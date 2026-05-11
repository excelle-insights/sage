<?php

namespace ExcelleInsights\Sage\Facade;

use PDO;
// use ExcelleInsights\Sage\Auth\Authentication;
use ExcelleInsights\Sage\Auth\StaticAuth;
use ExcelleInsights\Sage\Client\CustomerClient;
use ExcelleInsights\Sage\Client\InvoiceClient;
use ExcelleInsights\Sage\Client\PaymentClient;
use ExcelleInsights\Sage\Contracts\HttpClientInterface;
use ExcelleInsights\Sage\Repositories\TokenRepository;
use ExcelleInsights\Sage\Repositories\CustomerRepository;
use ExcelleInsights\Sage\Repositories\InvoiceRepository;
use ExcelleInsights\Sage\Repositories\PaymentRepository;
use ExcelleInsights\Sage\Repositories\PaymentItemRepository;
use ExcelleInsights\Sage\Services\CustomerSyncService;
use ExcelleInsights\Sage\Services\InvoiceSyncService;
use ExcelleInsights\Sage\Services\PaymentSyncService;
use ExcelleInsights\Sage\Support\EnvLoader;

/**
 * Facade for Sage integration
 * Keeps DX simple while wiring everything internally
 */
class SageManager
{
    private StaticAuth $auth;
    private PDO $pdo;
    private string $baseUrl;
    private string $companyId;
    private HttpClientInterface $http;

    public function __construct(
        ?HttpClientInterface $http = null,
        ?PDO $pdo = null,
        ?string $companyId = null,
        ?string $envRoot = null
    ) {
        EnvLoader::load($envRoot);

        $this->baseUrl   = $_ENV['SAGE_BASE_URL'] ?? '';
        $this->companyId = $companyId ?? $_ENV['SAGE_REALM_ID'] ?? '';

        if (!$pdo) {
            $dsn  = $_ENV['DB_DSN'] ?? null;
            $user = $_ENV['DB_USER'] ?? null;
            $pass = $_ENV['DB_PASSWORD'] ?? null;

            if (!$dsn) {
                throw new \RuntimeException(
                    'DB_DSN is not set. Ensure your project .env exists.'
                );
            }

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        }

        $this->pdo = $pdo;

        if ($http === null) {
            $http = new \ExcelleInsights\Sage\Support\DefaultHttpClient($this->pdo);
        }

        $this->http = $http;

        $this->auth = new StaticAuth();

    }

    public function getApiUrl(string $endpoint): string
    {
        return $this->auth->getApiUrl($endpoint);
    }
    public function getAuthHeader(): string
    {
        return $this->auth->getAuthHeader();
    }

    /**
     * -------------------------
     * Customers
     * -------------------------
     */

    public function getCustomers(): object
    {
        $client = new CustomerClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );

        return $client->getAll();
    }

    public function getCustomer(string $id): object
    {
        $client = new CustomerClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );

        return $client->getById($id);
        
    }
    // public function createCustomer(array $data): object
    // {
    //     $repo = new CustomerRepository($this->pdo);

    //     $client = new CustomerClient(
    //         $this->baseUrl,
    //         $this->companyId,
    //         $this->auth,
    //         $this->http
    //     );

    //     $service = new CustomerSyncService($repo, $client);

    //     return $service->create($data);
    // }

    /**
     * -------------------------
     * Invoices
     * -------------------------
     */
    // public function createInvoice(array $data): object
    // {
    //     if (empty($data['qbo_company_id'])) {
    //         throw new \InvalidArgumentException('qbo_company_id is required');
    //     }

    //     if (empty($data['items']) || !is_array($data['items'])) {
    //         throw new \InvalidArgumentException('Invoice items are required');
    //     }

    //     $invoiceRepo  = new InvoiceRepository($this->pdo);
    //     $customerRepo = new CustomerRepository($this->pdo);

    //     $client = new InvoiceClient(
    //         $this->baseUrl,
    //         $this->companyId,
    //         $this->auth,
    //         $this->http
    //     );

    //     $service = new InvoiceSyncService(
    //         $invoiceRepo,
    //         $customerRepo,
    //         $client
    //     );

    //     return $service->create($data);
    // }

    /**
     * -------------------------
     * Payments
     * -------------------------
     */
    // public function createPayment(array $data): object
    // {
    //     if (empty($data['qbo_company_id'])) {
    //         throw new \InvalidArgumentException('qbo_company_id is required');
    //     }

    //     if (empty($data['qbo_customer_id'])) {
    //         throw new \InvalidArgumentException('qbo_customer_id is required');
    //     }

    //     // if (empty($data['items']) || !is_array($data['items'])) {
    //     //     throw new \InvalidArgumentException('Payment items are required');
    //     // }

    //     $paymentRepo  = new PaymentRepository($this->pdo);
    //     $paymentItemRepo  = new PaymentItemRepository($this->pdo);
    //     $customerRepo = new CustomerRepository($this->pdo);
    //     $invoiceRepo = new InvoiceRepository($this->pdo);

    //     $client = new PaymentClient(
    //         $this->baseUrl,
    //         $this->companyId,
    //         $this->auth,
    //         $this->http
    //     );

    //     $service = new PaymentSyncService(
    //         $paymentRepo,
    //         $paymentItemRepo,
    //         $customerRepo,
    //         $invoiceRepo,
    //         $client
    //     );

    //     return $service->create($data);
    // }
}
