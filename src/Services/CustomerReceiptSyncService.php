<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Client\CustomerReceiptClient;
use ExcelleInsights\Sage\Repositories\CustomerReceiptRepository;
use ExcelleInsights\Sage\Repositories\CustomerRepository;
use ExcelleInsights\Sage\Repositories\TaxInvoiceRepository;
use ExcelleInsights\Sage\Client\TaxInvoiceClient;
use ExcelleInsights\Sage\Client\AllocationClient;
use ExcelleInsights\Sage\Repositories\BankAccountRepository;



class CustomerReceiptSyncService
{
    public function __construct(
        private CustomerReceiptRepository $repo,
        private CustomerReceiptClient     $client,
        private CustomerRepository        $customerRepo,
        private TaxInvoiceRepository      $invoiceRepo,
        private TaxInvoiceClient          $invoiceClient,
        private AllocationClient          $allocationClient,
        private BankAccountRepository     $bankAccountRepo

    ) {}

    public function create(array $data): object
    {
        // 1. resolve customer local_id → sage_id (unchanged)
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

        // 2. resolve invoice local_id → sage_id AND local invoice_id FK
        if (!empty($data['invoice_local_id'])) {
            $invoice = $this->invoiceRepo->findById(
                $data['invoice_local_id']
            );
            if (!$invoice || !$invoice->sage_id) {
                throw new \RuntimeException(
                    "Invoice local_id {$data['invoice_local_id']} is not synced to Sage yet"
                );
            }
            $data['sage_invoice_id'] = (int) $invoice->sage_id;
            $data['invoice_id']      = (int) $invoice->id;        // ← NEW
        }

        // 3. resolve bank account local_id → sage_id
        if (!empty($data['bank_account_local_id'])) {
            $bankAccount = $this->bankAccountRepo->findByLocalId(
                $data['bank_account_local_id']
            );
            if (!$bankAccount || !$bankAccount->sage_id) {
                throw new \RuntimeException(
                    "BankAccount local_id {$data['bank_account_local_id']} is not synced to Sage yet"
                );
            }
            $data['bank_account_id'] = (int) $bankAccount->sage_id;
        }
        // 3. save locally first
        $receiptId = $this->repo->create($data);                   // was $localId

        // 4. push to Sage
        try {
            $result = $this->client->create($data);
            $this->repo->markSynced($receiptId, $result->ID);     // was $localId

            // 6. allocate receipt to invoice in Sage

            if (!empty($data['sage_invoice_id'])) {
                $this->allocationClient->allocate([
                    'receipt_sage_id' => $result->ID,
                    'invoice_sage_id' => $data['sage_invoice_id'],
                    'total'           => $data['total'],
                    'discount'        => $data['discount'] ?? 0,
                ]);
            }

            // 7. verify invoice paid status from Sage
            if (!empty($data['invoice_id']) && !empty($data['sage_invoice_id'])) {
                $sageInvoice = $this->invoiceClient->getById(
                    (int) $data['sage_invoice_id']
                );
                $sageStatus = $sageInvoice->Status ?? 'unknown';

                // fwrite(STDOUT, "\nSage invoice status: {$sageStatus}\n");

                $this->invoiceRepo->markPaidStatus(
                    (int) $data['invoice_id'],
                    strtolower($sageStatus) === 'paid' ? 'paid' : 'unpaid'
                );
            }

            return (object) [
                'status'   => 'synced',
                'local_id' => $receiptId,     // let caller know local receipt PK
                'sage_id'  => $result->ID,
                'data'     => $result,
            ];
        } catch (\Throwable $e) {
            $this->repo->markFailed($receiptId, $e->getMessage());

            return (object) [
                'status'   => 'failed',
                'local_id' => $receiptId,
                'error'    => $e->getMessage(),
            ];
        }
    }

    public function getByInvoiceId(int $invoiceId): object
    {
        $local = $this->repo->findByInvoiceId($invoiceId);

        if (!$local) {
            throw new \RuntimeException(
                "Receipt for invoice ID {$invoiceId} not found"
            );
        }

        // if ($local->status === 'synced' && $local->sage_id) {
        //     return $this->client->getById((int) $local->sage_id);
        // }

        return $local;
    }

    public function getByLocalId(int $localId): object
    {
        $local = $this->repo->findByLocalId($localId);

        if (!$local) {
            throw new \RuntimeException(
                "Receipt with local ID {$localId} not found"
            );
        }

        if ($local->status === 'synced' && $local->sage_id) {
            return $this->invoiceClient->getById((int) $local->sage_id);
        }

        return $local;
    }
}
