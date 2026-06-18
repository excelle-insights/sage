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

    // Customers
    public function testGetAllCustomers(): void
    {
        $result = $this->manager->getCustomers();
        fwrite(STDOUT, "\nAll Customers: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");
        $this->assertNotNull($result);
    }

    public function testGetCustomerById(): void
    {
        $result = $this->manager->getCustomer('861999');
        fwrite(STDOUT, "\nCustomer: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");
        $this->assertNotNull($result);
    }

    public function testCreateCustomer(): void
    {
        $result = $this->manager->createCustomer([
            'local_id'           => 1,
            'name'               => 'John Doe',
            'email'               => 'jdoe@gmail.com',
            'pin'                 => 'A123456789B',
            'mobile'               => '0700453259',
            'active'             => true,
            'category_local_id'  => 1,  // must already be synced
            'sales_rep_local_id' => 1,  // must already be synced
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
            'local_id' => 1,
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

    public function testCreateCustomerCategory(): void
    {
        $result = $this->manager->createCustomerCategory([
            'local_id'    => 1,
            'description' => 'Equity',
        ]);

        fwrite(STDOUT, "\nCategory: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }

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

    public function testCreateTaxType(): void
    {
        $result = $this->manager->createTaxType([
            'name'       => 'KRA VAT',
            'percentage' => 0.16,
            'active'     => true,
        ]);

        fwrite(STDOUT, "\nTaxType: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }

    public function testUpdateTaxType(): void
    {
        // WMS passes primary key — not sage_id
        $result = $this->manager->updateTaxType(1, [
            'name'       => 'KRA VAT',
            'percentage' => 0.17,
            'active'     => true,
        ]);

        fwrite(STDOUT, "\nUpdated: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
    }

    public function testCreateTaxInvoice(): void
    {
        $result = $this->manager->createTaxInvoice([
            'local_id'           => 2,
            'customer_local_id'  => 1,       // resolves to sage_customer_id
            'sales_rep_local_id' => 1,       // resolves to sage_salesrep_id
            'date'               => '2026-05-28',
            'due_date'           => '2026-06-28',
            'customer_reference' => '84-02-26',
            'lines'              => [
                [
                    'line_type'    => 1,         // Account
                    'selection_id' => 643509,         // replace from sage_invoice_accounts
                    'description'  => 'Disbursements',
                    'tax_type_id'  => 149296,         // replace with sage tax type id
                    'quantity'     => 1,
                    'unit_price'   => 150000.00,
                    'discount'     => 0,
                ]
            ],
        ]);

        fwrite(STDOUT, "\nTaxInvoice: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }

    public function testExportTaxInvoicePdf(): void
    {
        $pdf = $this->manager->exportTaxInvoicePdf(1);

        $this->assertNotEmpty($pdf);

        // save to file so you can open and verify
        file_put_contents('/tmp/tax_invoice.pdf', $pdf);
        fwrite(STDOUT, "\nPDF saved to /tmp/tax_invoice.pdf\n");
    }

    public function testCreateBankAccount(): void
    {
        $result = $this->manager->createBankAccount([
            'local_id'        => 1,
            'account_name'    => 'EQUITY',
            'bank_name'       => 'EQUITY Bank',
            'account_number'  => '1234567890',
            'branch_name'     => 'Nairobi',
            'branch_code'     => '01',
            'description'     => 'EQUITY Bank Account',
            'payment_method'  => 'Cash',
            'opening_balance' => 0.00,
            'active'          => true,
            'is_default'      => false,
        ]);


        fwrite(STDOUT, "\nBankAccount: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }

    public function testGetBankAccount(): void
    {
        $result = $this->manager->getBankAccount(1);

        fwrite(STDOUT, "\nBankAccount: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertNotNull($result);
    }


    public function testCreateCustomerReceipt(): void
    {
        $result = $this->manager->createCustomerReceipt([
            'customer_local_id'  => 1,           // resolves to sage_customer_id
            'invoice_local_id'   => 6,           // resolves to sage_invoice_id + invoice_id FK
            'bank_account_id'    => 2406,      // Sage bank account ID (from testCreateBankAccount result->sage_id)
            'date'               => '2026-06-02',
            'total'              => 150000.00,   // matches invoice unit_price
            'reference'          => '84-02-26',
            'description'        => 'Payment for INV-001',
            'payment_method'     => 'Cash',
            'reconciled'         => false,
        ]);

        fwrite(STDOUT, "\nReceipt: " . json_encode($result, JSON_PRETTY_PRINT) . "\n");

        $this->assertEquals('synced', $result->status);
        $this->assertNotNull($result->sage_id);
    }
    // public function testSyncTaxTypes(): void
    // {
    //     $this->manager->syncTaxTypes();

    //     fwrite(STDOUT, "\nTax types synced from Sage\n");

    //     $this->assertTrue(true);
    // }
}
