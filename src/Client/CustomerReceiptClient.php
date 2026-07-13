<?php

namespace ExcelleInsights\Sage\Client;

class CustomerReceiptClient extends BaseClient
{
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'CustomerReceipt/Get');
    }

    public function getById(int $id): object
    {
        return $this->sendRequest('GET', "CustomerReceipt/Get/{$id}");
    }

    public function create(array $data): object
    {
        $payload = array_filter([
            'CustomerId'    => $data['sage_customer_id'],
            'Date'          => $data['date'],
            'Total'         => $data['total'],
            'Reference'     => $data['reference']      ?? null,
            'Description'   => $data['description']    ?? null,
            'Comments'      => $data['comments']       ?? null,
            'BankAccountId' => $data['bank_account_id'],
            'PaymentMethod' => $data['payment_method'] ?? 'Cash',
            'Reconciled'    => $data['reconciled']     ?? false,
            'Allocations'   => isset($data['sage_invoice_id']) ? [
                [
                    'ID'     => $data['sage_invoice_id'],
                    'Amount' => $data['total'], // removed from DB, still needed by Sage API
                ]
            ] : null,
        ], fn($v) => $v !== null);

        return $this->sendRequest(
            'POST',
            'CustomerReceipt/Save?useSystemDocumentNumber=true',
            $payload
        );
    }
}
