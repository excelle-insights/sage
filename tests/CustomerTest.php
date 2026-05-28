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

    // public function testFetchCustomers(): void
    // {
    //     $url = $this->manager->getApiUrl('Customer/Get');

    //     $ch = curl_init($url);
    //     curl_setopt_array($ch, [
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    //     ]);

    //     $response = curl_exec($ch);
    //     $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     curl_close($ch);

    //     $this->assertEquals(200, $status);

    //     $data = json_decode($response, true);
    //     $this->assertNotNull($data);

    //     fwrite(STDOUT, "\nCustomers: " . json_encode($data, JSON_PRETTY_PRINT) . "\n");
    // }
}
