<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Repositories\InvoiceRepository;
use ExcelleInsights\Sage\Repositories\CustomerRepository;
use ExcelleInsights\Sage\Client\InvoiceClient;

class InvoiceSyncService
{
    public function __construct(
        private InvoiceRepository $invoiceRepo,
        private CustomerRepository $customerRepo,
        private InvoiceClient $invoiceClient
    ) {}

    /**
     * Create invoice locally and attempt sync with Sage Online
     */
    public function create(array $data): object
    {
        // Insert invoice locally first
        $localId = $this->invoiceRepo->create($data);

        // Load customer (local source of truth)
        $customer = $this->customerRepo->find(
            (int) $data['qbo_customer_id']
        );

        // If customer not yet synced → stop here
        if (!$customer || !$customer->qbo_id) {
            return (object) [
                'status'   => 'queued',
                'local_id' => $localId,
                'reason'   => 'Customer not yet synced to ',
            ];
        }

        // Inject  customer ID for API payload
        $data['customer_qbo_id'] = $customer->qbo_id;

        try {
            // Attempt to create invoice in 
            $qboInvoice = $this->invoiceClient->create($data);

            // Ensure required fields exist
            $qboId = $qboInvoice->Invoice->Id ?? null;
            $syncToken = $qboInvoice->Invoice->SyncToken ?? null;
            $total = $qboInvoice->Invoice->TotalAmt ?? 0;

            if($qboId) {
                // Mark invoice as synced locally
                $this->invoiceRepo->markSynced(
                    $localId,
                    $qboId,
                    $syncToken,
                    $total
                );
            }

            return (object)[
                'status'   => 'synced',
                'local_id' => $localId,
                'qbo_id'   => $qboId,
            ];
        } catch (\Throwable $e) {
            // 4️⃣ If  sync fails, mark as failed for retry later
            error_log("QBO Invoice sync failed: " . $e->getMessage() . ":" . $e->getTraceAsString());
            $this->invoiceRepo->markFailed($localId, $e->getMessage());

            return (object)[
                'status'   => 'failed',
                'local_id' => $localId,
                'error'    => $e->getMessage(),
            ];
        }
    }
}
