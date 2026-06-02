<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Client\TaxInvoiceClient;
use ExcelleInsights\Sage\Repositories\TaxInvoiceRepository;
use ExcelleInsights\Sage\Repositories\TaxInvoiceItemsRepository;
use ExcelleInsights\Sage\Repositories\CustomerRepository;
use ExcelleInsights\Sage\Repositories\SalesRepRepository;

class TaxInvoiceSyncService
{
    public function __construct(
        private TaxInvoiceRepository     $repo,
        private TaxInvoiceItemsRepository $lineRepo,
        private TaxInvoiceClient         $client,
        private CustomerRepository       $customerRepo,
        private SalesRepRepository       $salesRepRepo
    ) {}

    public function create(array $data): object
    {
        // 1. resolve customer local_id → sage_id
        if (!empty($data['customer_local_id'])) {
            $customer = $this->customerRepo->findByLocalId(
                $data['customer_local_id']
            );

            if (!$customer || !$customer->sage_id) {
                throw new \RuntimeException(
                    "Customer local_id {$data['customer_local_id']} is not synced to Sage yet"
                );
            }
            $data['sage_customer_id'] = (int) $customer->sage_id;
        }

        // 2. resolve sales rep local_id → sage_id
        if (!empty($data['sales_rep_local_id'])) {
            $salesRep = $this->salesRepRepo->findByLocalId(
                $data['sales_rep_local_id']
            );

            if (!$salesRep || !$salesRep->sage_id) {
                throw new \RuntimeException(
                    "SalesRep local_id {$data['sales_rep_local_id']} is not synced to Sage yet"
                );
            }
            $data['sage_salesrep_id'] = (int) $salesRep->sage_id;
        }

        // 3. save invoice header locally
        $localId = $this->repo->create($data);

        // 4. save lines locally
        foreach ($data['lines'] as $line) {
            $this->lineRepo->createLine($localId, $line);
        }

        // 5. push to Sage
        try {
            $result = $this->client->create($data);

            // 6. attach sage_id
            $this->repo->markSynced($localId, $result->ID);

            return (object) [
                'status'   => 'synced',
                'local_id' => $localId,
                'sage_id'  => $result->ID,
                'data'     => $result,
            ];
        } catch (\Throwable $e) {
            $this->repo->markFailed($localId, $e->getMessage());

            return (object) [
                'status'   => 'failed',
                'local_id' => $localId,
                'error'    => $e->getMessage(),
            ];
        }
    }

    public function getByLocalId(int $localId): object
    {
        $local = $this->repo->findByLocalId($localId);

        if (!$local) {
            throw new \RuntimeException(
                "TaxInvoice with local ID {$localId} not found"
            );
        }

        if ($local->status === 'synced' && $local->sage_id) {
            return $this->client->getById((int) $local->sage_id);
        }

        return $local;
    }

    public function exportPdf(int $localId): string
    {
        $local = $this->repo->findByLocalId($localId);

        if (!$local || !$local->sage_id) {
            throw new \RuntimeException(
                "Invoice local_id {$localId} is not synced to Sage yet"
            );
        }

        return $this->client->exportPdf((int) $local->sage_id);
    }
}
