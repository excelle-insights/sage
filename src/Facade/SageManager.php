<?php

namespace ExcelleInsights\Sage\Facade;

use PDO;
// use ExcelleInsights\Sage\Auth\Authentication;
use ExcelleInsights\Sage\Auth\StaticAuth;
use ExcelleInsights\Sage\Contracts\HttpClientInterface;
use ExcelleInsights\Sage\Support\EnvLoader;

use ExcelleInsights\Sage\Client\CustomerClient;
use ExcelleInsights\Sage\Repositories\CustomerRepository;

use ExcelleInsights\Sage\Client\SalesRepClient;
use ExcelleInsights\Sage\Repositories\SalesRepRepository;
use ExcelleInsights\Sage\Services\SalesRepSyncService;

use ExcelleInsights\Sage\Client\CustomerCategoryClient;
use ExcelleInsights\Sage\Repositories\CustomerCategoryRepository;
use ExcelleInsights\Sage\Services\CustomerCategorySyncService;

use ExcelleInsights\Sage\Client\AccountClient;
use ExcelleInsights\Sage\Repositories\AccountRepository;
use ExcelleInsights\Sage\Services\AccountSyncService;

use ExcelleInsights\Sage\Client\TaxTypeClient;
use ExcelleInsights\Sage\Repositories\TaxTypeRepository;
use ExcelleInsights\Sage\Services\TaxTypeSyncService;

use ExcelleInsights\Sage\Client\TaxInvoiceClient;
use ExcelleInsights\Sage\Repositories\TaxInvoiceRepository;
use ExcelleInsights\Sage\Repositories\TaxInvoiceItemsRepository;
use ExcelleInsights\Sage\Services\TaxInvoiceSyncService;


use ExcelleInsights\Sage\Client\InvoiceClient;
use ExcelleInsights\Sage\Client\PaymentClient;
use ExcelleInsights\Sage\Repositories\TokenRepository;
use ExcelleInsights\Sage\Repositories\InvoiceRepository;
use ExcelleInsights\Sage\Repositories\PaymentRepository;
use ExcelleInsights\Sage\Repositories\PaymentItemRepository;
use ExcelleInsights\Sage\Services\CustomerSyncService;
use ExcelleInsights\Sage\Services\InvoiceSyncService;
use ExcelleInsights\Sage\Services\PaymentSyncService;

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

    // public function getCustomer(int $localId): object
    // {
    //     $repo    = new CustomerRepository($this->pdo);
    //     $client  = new CustomerClient(
    //         $this->baseUrl,
    //         $this->companyId,
    //         $this->auth,
    //         $this->http
    //     );
    //     $service = new CustomerSyncService(
    //         $repo,
    //         $client,
    //         new CustomerCategoryRepository($this->pdo),
    //         new SalesRepRepository($this->pdo)
    //     );

    //     return $service->getByLocalId($localId);
    // }

    public function createCustomer(array $data): object
    {
        $repo         = new CustomerRepository($this->pdo);
        $categoryRepo = new CustomerCategoryRepository($this->pdo);
        $salesRepRepo = new SalesRepRepository($this->pdo);

        $client = new CustomerClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );

        $service = new CustomerSyncService(
            $repo,
            $client,
            $categoryRepo,
            $salesRepRepo
        );

        return $service->create($data);
    }

    /**
     * -------------------------
     * Sales Representative
     * -------------------------
     */

    public function createSalesRep(array $data): object
    {
        $repo    = new SalesRepRepository($this->pdo);
        $client  = new SalesRepClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );
        $service = new SalesRepSyncService($repo, $client);

        return $service->create($data);
    }

    public function getSalesRep(int $localId): object
    {
        $repo    = new SalesRepRepository($this->pdo);
        $client  = new SalesRepClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );
        $service = new SalesRepSyncService($repo, $client);

        return $service->getByLocalId($localId);
    }
    /**
     * -------------------------
     * Customer Category
     * -------------------------
     */

    public function createCustomerCategory(array $data): object
    {
        $repo    = new CustomerCategoryRepository($this->pdo);
        $client  = new CustomerCategoryClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );
        $service = new CustomerCategorySyncService($repo, $client);

        return $service->create($data);
    }

    public function getCustomerCategory(int $localId): object
    {
        $repo    = new CustomerCategoryRepository($this->pdo);
        $client  = new CustomerCategoryClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );
        $service = new CustomerCategorySyncService($repo, $client);

        return $service->getByLocalId($localId);
    }


    /**
     * -------------------------
     * Invoices
     * -------------------------
     */

    public function createAccount(array $data): object
    {
        $repo    = new AccountRepository($this->pdo);
        $client  = new AccountClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );
        $service = new AccountSyncService($repo, $client);

        return $service->create($data);
    }

    public function getAccount(int $localId): object
    {
        $repo    = new AccountRepository($this->pdo);
        $client  = new AccountClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );
        $service = new AccountSyncService($repo, $client);

        return $service->getByLocalId($localId);
    }

    /**
     * Pull all tax types from Sage and cache locally
     * Run this once during setup
     */
    // public function syncTaxTypes(): void
    // {
    //     $repo   = new TaxTypeRepository($this->pdo);
    //     $client = new TaxTypeClient(
    //         $this->baseUrl,
    //         $this->companyId,
    //         $this->auth,
    //         $this->http
    //     );

    //     $result = $client->getAll();

    //     foreach ($result->Results ?? $result as $taxType) {
    //         $repo->create([
    //             'sage_id'    => $taxType->ID,
    //             'name'       => $taxType->Name,
    //             'percentage' => $taxType->Percentage,
    //             'active'     => $taxType->Active ?? true,
    //         ]);
    //     }
    // }

    public function createTaxType(array $data): object
    {
        $repo   = new TaxTypeRepository($this->pdo);
        $client = new TaxTypeClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );

        $result = $client->create($data);

        $repo->create([
            'sage_id'    => $result->ID,
            'name'       => $result->Name,
            'percentage' => $result->Percentage,
            'active'     => $result->Active ?? true,
        ]);

        return (object) [
            'status'  => 'synced',
            'sage_id' => $result->ID,
            'data'    => $result,
        ];
    }
    public function updateTaxType(int $id, array $data): object
    {
        $repo    = new TaxTypeRepository($this->pdo);
        $client  = new TaxTypeClient(
            $this->baseUrl,
            $this->companyId,
            $this->auth,
            $this->http
        );
        $service = new TaxTypeSyncService($repo, $client);

        return $service->update($id, $data);
    }


    public function createTaxInvoice(array $data): object
    {
        $service = new TaxInvoiceSyncService(
            new TaxInvoiceRepository($this->pdo),
            new TaxInvoiceItemsRepository($this->pdo),
            new TaxInvoiceClient(
                $this->baseUrl,
                $this->companyId,
                $this->auth,
                $this->http
            ),
            new CustomerRepository($this->pdo),
            new SalesRepRepository($this->pdo)
        );

        return $service->create($data);
    }

    public function getTaxInvoice(int $localId): object
    {
        $service = new TaxInvoiceSyncService(
            new TaxInvoiceRepository($this->pdo),
            new TaxInvoiceItemsRepository($this->pdo),
            new TaxInvoiceClient(
                $this->baseUrl,
                $this->companyId,
                $this->auth,
                $this->http
            ),
            new CustomerRepository($this->pdo),
            new SalesRepRepository($this->pdo)
        );

        return $service->getByLocalId($localId);
    }

    public function exportTaxInvoicePdf(int $localId): string
    {
        $service = new TaxInvoiceSyncService(
            new TaxInvoiceRepository($this->pdo),
            new TaxInvoiceItemsRepository($this->pdo),
            new TaxInvoiceClient(
                $this->baseUrl,
                $this->companyId,
                $this->auth,
                $this->http
            ),
            new CustomerRepository($this->pdo),
            new SalesRepRepository($this->pdo)
        );

        return $service->exportPdf($localId);
    }
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
