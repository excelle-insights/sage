<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Repositories\PaymentRepository;
use ExcelleInsights\Sage\Repositories\PaymentItemRepository;
use ExcelleInsights\Sage\Repositories\CustomerRepository;
use ExcelleInsights\Sage\Repositories\InvoiceRepository;
use ExcelleInsights\Sage\Client\PaymentClient;

class PaymentSyncService
{
    public function __construct(
        private PaymentRepository $paymentRepo,
        private PaymentItemRepository $paymentItemRepo,
        private CustomerRepository $customerRepo,
        private InvoiceRepository $invoiceRepo,
        private PaymentClient $paymentClient
    ) {}

    /**
     * Create payment locally and attempt sync with Sage Online
     */
    public function create(array $data): object
    {
        /**
         * 1️⃣ Create payment locally
         */
        $localPaymentId = $this->paymentRepo->create($data);

        /**
         * 2️⃣ Persist line items locally
         */
        foreach ($data['items'] ?? [] as $item) {
            $item['qbo_payment_id'] = $localPaymentId;
            $this->paymentItemRepo->create($item);
        }

        /**
         * 3️⃣ Load customer (local source of truth)
         */
        $customer = $this->customerRepo->find(
            (int) $data['qbo_customer_id']
        );

        if (!$customer || !$customer->qbo_id) {
            return (object) [
                'status'   => 'queued',
                'local_id' => $localPaymentId,
                'reason'   => 'Customer not yet synced to ',
            ];
        }

        /**
         * 4️⃣ Ensure all linked invoices are synced
         */
        $lineItems = $this->paymentItemRepo->getByPaymentId($localPaymentId);

        foreach ($lineItems as $item) {
            $invoice = $this->invoiceRepo->find((int) $item['qbo_invoice_id']);

            if (!$invoice || !$invoice->qbo_id) {
                return (object) [
                    'status'   => 'queued',
                    'local_id' => $localPaymentId,
                    'reason'   => 'Linked invoice not yet synced to ',
                ];
            }
        }

        /**
         * 5️⃣ Build  payload
         */
        $payload = [
            'customer_qbo_id' => $customer->qbo_id,
            'amount'          => $data['total_amount'],
            'txn_date'        => $data['txn_date'] ?? null,
            'transaction_ref' => $data['payment_ref'] ?? null,
            'bank_account'    => $data['deposit_account_id'] ?? null,
            'private_note'    => $data['private_note'] ?? null,
            'items'      => array_map(
                fn ($item) => [
                    'amount'     => $item['amount'],
                    'qbo_invoice_id' => $this->invoiceRepo
                        ->find((int) $item['qbo_invoice_id'])
                        ->qbo_id,
                ],
                $lineItems
            ),
        ];

        /**
         * 6️⃣ Attempt  sync
         */
        try {
            $qboPayment = $this->paymentClient->create($payload);

            $qboId = $qboPayment->Payment->Id ?? null;

            if ($qboId) {
                $this->paymentRepo->markSynced(
                    $localPaymentId,
                    $qboId,
                    $qboPayment->Payment->SyncToken ?? null
                );
            }

            return (object) [
                'status'   => 'synced',
                'local_id' => $localPaymentId,
                'qbo_id'   => $qboId,
            ];

        } catch (\Throwable $e) {
            error_log(
                "QBO Payment sync failed: " .
                $e->getMessage() .
                ":" .
                $e->getTraceAsString()
            );

            $this->paymentRepo->markFailed(
                $localPaymentId,
                $e->getMessage()
            );

            return (object) [
                'status'   => 'failed',
                'local_id' => $localPaymentId,
                'error'    => $e->getMessage(),
            ];
        }
    }
}
