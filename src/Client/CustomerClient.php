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
}
