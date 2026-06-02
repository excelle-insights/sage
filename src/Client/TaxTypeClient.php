<?php

namespace ExcelleInsights\Sage\Client;

class TaxTypeClient extends BaseClient
{
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'TaxType/Get');
    }

    public function create(array $data): object
    {
        $payload = [
            'Name'       => $data['name'],
            'Percentage' => $data['percentage'],
            'Active'     => $data['active'] ?? true,
        ];

        return $this->sendRequest('POST', 'TaxType/Save', $payload);
    }
    public function update(int $sageId, array $data): object
    {
        $payload = [
            'ID'         => $sageId,
            'Name'       => $data['name'],
            'Percentage' => $data['percentage'],
            'Active'     => $data['active'] ?? true,
        ];

        return $this->sendRequest('POST', 'TaxType/Save', $payload);
    }

}
