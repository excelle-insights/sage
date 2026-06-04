<?php

namespace ExcelleInsights\Sage\Client;

class BankAccountClient extends BaseClient
{
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'BankAccount/Get');
    }

    public function getById(int $id): object
    {
        return $this->sendRequest('GET', "BankAccount/Get/{$id}");
    }

    public function create(array $data): object
    {
        $payload = array_filter([
            'Name'                   => $data['account_name'],
            'BankName'               => $data['bank_name']       ?? null,
            'AccountNumber'          => $data['account_number']  ?? null,
            'BranchName'             => $data['branch_name']     ?? null,
            'BranchNumber'           => $data['branch_code']     ?? null,
            'Description'            => $data['description']     ?? null,
            'DefaultPaymentMethodId' => $data['payment_method']  ?? 'Cash',
            'Balance'                => $data['opening_balance'] ?? 0.00,
            'Active'                 => $data['active']          ?? true,
            'Default'                => $data['is_default']      ?? false,
        ], fn($v) => $v !== null);

        return $this->sendRequest('POST', 'BankAccount/Save', $payload);
    }
}
