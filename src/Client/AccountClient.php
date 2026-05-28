<?php

namespace ExcelleInsights\Sage\Client;

class AccountClient extends BaseClient
{
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'Account/Get');
    }

    public function getById(int $id): object
    {
        return $this->sendRequest('GET', "Account/Get/{$id}");
    }

    public function create(array $data): object
    {
        $payload = array_filter([
            'Name'        => $data['name'],
            'Description' => $data['description'] ?? null,
            'Active'      => $data['active']       ?? true,
            'Balance'     => $data['balance']      ?? 0.00,
            'Category'    => isset($data['category_id']) ? [
                'ID' => $data['category_id'],
            ] : null,
        ], fn($v) => $v !== null);

        return $this->sendRequest('POST', 'Account/Save', $payload);
    }
}
