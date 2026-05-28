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

    public function testCreateCustomer(): void
    {
        $result = $this->manager->createCustomer([
            'local_id'           => 2,
            'name'               => 'Akinyi Customer',
            'email'               => 'Akinyi@gmail.com',
            'mobile'               => '0700453259',
            'active'             => true,
            'category_local_id'  => 1,  // must already be synced
            'sales_rep_local_id' => 2,  // must already be synced
        ]);

        fwrite(STDOUT, "\nCustomer: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");


        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }

    /**
     * Sales Rep 
     */

    public function testCreateSalesRep(): void
    {
        $result = $this->manager->createSalesRep([
            'local_id' => 2,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'active'     => true,
            'email'      => 'jane.doe@example.com',
            'mobile'     => '0712345678',
        ]);

        fwrite(STDOUT, "\nSalesRep: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }

    /**
     * Customer Category
     */

    // public function testCreateCustomerCategory(): void
    // {
    //     $result = $this->manager->createCustomerCategory([
    //         'local_id'    => 1,
    //         'description' => 'Equity',
    //     ]);

    //     fwrite(STDOUT, "\nCategory: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

    //     $this->assertEquals('synced', $result->status);
    //     $this->assertNotNull($result->sage_id);
    // }

    /**
     * Invoice Account
     */


    public function testCreateAccount(): void
    {
        $result = $this->manager->createAccount([
            'local_id'    => 1,
            'name'        => 'Disbursements',
            'description' => 'Disbursements and related costs',
            'category_id' => 2,   // Sales
            'active'      => true,
            'balance'     => 0.00,
        ]);

        fwrite(STDOUT, "\nAccount: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");
        fwrite(STDOUT, "\nURL: " . $this->manager->getApiUrl('Account/Save') . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }

    public function testGetAccount(): void
    {
        $result = $this->manager->getAccount(1);

        fwrite(STDOUT, "\nAccount: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertNotNull($result);
    }
}
