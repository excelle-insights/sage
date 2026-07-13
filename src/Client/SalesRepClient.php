<?php

namespace ExcelleInsights\Sage\Client;

class SalesRepClient extends BaseClient
{
    /**
     * Get all sales reps
     */
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'SalesRepresentative/Get');
    }

    /**
     * Get single sales rep by Sage ID
     */
    public function getById(int $id): object
    {
        return $this->sendRequest('GET', "SalesRepresentative/Get/{$id}");
    }

    /**
     * Create a new sales rep
     */
    public function create(array $data): object
    {
        $payload = array_filter([
            'FirstName' => $data['first_name'],
            'LastName'  => $data['last_name'],
            'Active'    => $data['active']    ?? true,
            'Email'     => $data['email']     ?? null,
            'Mobile'    => $data['mobile']    ?? null,
            'Telephone' => $data['telephone'] ?? null,
        ], fn($v) => $v !== null);

        return $this->sendRequest('POST', 'SalesRepresentative/Save', $payload);
    }
}
