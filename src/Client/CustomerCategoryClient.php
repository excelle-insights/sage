<?php

namespace ExcelleInsights\Sage\Client;

class CustomerCategoryClient extends BaseClient
{
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'CustomerCategory/Get');
    }

    public function getById(int $id): object
    {
        return $this->sendRequest('GET', "CustomerCategory/Get/{$id}");
    }

    public function create(array $data): object
    {
        $payload = [
            'Description' => $data['description'],
        ];

        return $this->sendRequest('POST', 'CustomerCategory/Save', $payload);
    }
}
