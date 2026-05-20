<?php

namespace ExcelleInsights\Sage\Tests;

use PHPUnit\Framework\TestCase;
use ExcelleInsights\Sage\Facade\SageManager;
use ExcelleInsights\Sage\Support\EnvLoader;

class CustomerTest extends TestCase
{
    private SageManager $manager;

    protected function setUp(): void
    {
        EnvLoader::load(dirname(__DIR__));
        $this->manager = new SageManager();
    }

    // Connection
    /*

    public function testFetchCompany(): void
    {
        $url    = $this->manager->getApiUrl('Company/Get');
        $header = $this->manager->getAuthHeader();

        // fwrite(STDOUT, "\nURL: $url\n");
        // fwrite(STDOUT, "Auth header: $header\n");

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: ' . $header,
            ],
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        fwrite(STDOUT, "Status: $status\n");
        fwrite(STDOUT, "Response: $response\n");

        $this->assertEquals(200, $status);
    }
     */

    // Customers
    // public function testGetAllCustomers(): void
    // {
    //     $result = $this->manager->getCustomers();
    //     fwrite(STDOUT, "\nAll Customers: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");
    //     $this->assertNotNull($result);
    // }

    // public function testGetCustomerById(): void
    // {
    //     $result = $this->manager->getCustomer('861999');
    //     fwrite(STDOUT, "\nCustomer: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");
    //     $this->assertNotNull($result);
    // }

    /**
     * Sales Rep 
     */

    public function testCreateSalesRep(): void
    {
        $result = $this->manager->createSalesRep([
            'local_id' => 1,
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'active'     => true,
            'email'      => 'john.doe@example.com',
            'mobile'     => '0712345678',
        ]);

        fwrite(STDOUT, "\nSalesRep: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }
}
