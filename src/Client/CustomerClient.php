<?php

namespace ExcelleInsights\Sage\Client;

class CustomerClient extends BaseClient
{
    /**
     * Get all customers
     * GET /Customer/Get?APIKey=xxx&companyid=xxx
     */
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'Customer/Get');
    }
    

    /**
     * Get a single customer by ID
     * GET /Customer/Get/{id}?APIKey=xxx&companyid=xxx
     */
    public function getById(string $id): object
    {
        return $this->sendRequest('GET', "Customer/Get/{$id}");
    }

    /**
     * Create a new customer
     */
    public function create(array $data): object
    {
        $payload = array_filter([
            'Name'                  => $data['name'],
            'Active'                => $data['active'] ?? true,
            'Category'              => isset($data['sage_category_id'])
                ? ['ID' => $data['sage_category_id']]
                : null,
            'SalesRepresentativeId' => $data['sage_salesrep_id'] ?? null,
            'Email'                 => $data['email']             ?? null,
            'Mobile'                => $data['mobile']            ?? null,
            'Telephone'             => $data['telephone']         ?? null,
        ], fn($v) => $v !== null);

        return $this->sendRequest('POST', 'Customer/Save', $payload);
    }
}



