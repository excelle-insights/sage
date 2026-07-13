<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Client\CustomerClient;
use ExcelleInsights\Sage\Repositories\CustomerRepository;

class CustomerSyncService
{
    public function __construct(
        private CustomerRepository $customers,
        private CustomerClient $qbo
    ) {}

    public function create(array $data): object
    {
        // Create locally
        $localId = $this->customers->create($data);

        try {
            // Create in 
            $response = $this->qbo->create($data);

            if (!isset($response->Customer)) {
                throw new \RuntimeException('Invalid  response');
            }

            // Link local ↔ 
            $this->customers->markSynced(
                $localId,
                $response->Customer->Id,
                $response->Customer->SyncToken,
                "synced"
            );

            return (object)[
                'status'   => 'synced',
                'local_id' => $localId,
                'qbo_id'   => $response->Customer->Id,
                'data'     => $response->Customer
            ];

        } catch (\Throwable $e) {
            error_log("QBO Customer sync failed: " . $e->getMessage());
            // Leave unsynced, retry later

            $this->customers->markFailed(
                $localId,
                $e->getMessage()
            );

            return (object)[
                'status'   => 'pending',
                'local_id' => $localId,
                'error'    => $e->getMessage()
            ];
        }
    }
}
