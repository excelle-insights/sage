<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Client\CustomerClient;
use ExcelleInsights\Sage\Repositories\CustomerRepository;
use ExcelleInsights\Sage\Repositories\CustomerCategoryRepository;
use ExcelleInsights\Sage\Repositories\SalesRepRepository;

class CustomerSyncService
{
    public function __construct(
        private CustomerRepository         $repo,
        private CustomerClient             $client,
        private CustomerCategoryRepository $categoryRepo,
        private SalesRepRepository         $salesRepRepo
    ) {}

    public function create(array $data): object
    {
        // 1. resolve category local_id → sage_id
        if (!empty($data['category_local_id'])) {
            $category = $this->categoryRepo->findByLocalId($data['category_local_id']);

            if (!$category || !$category->sage_id) {
                throw new \RuntimeException(
                    "Category local_id {$data['category_local_id']} is not synced to Sage yet"
                );
            }

            $data['sage_category_id']  = (int) $category->sage_id;
        }

        // 2. resolve sales rep local_id → sage_id
        if (!empty($data['sales_rep_local_id'])) {
            $salesRep = $this->salesRepRepo->findByLocalId($data['sales_rep_local_id']);

            if (!$salesRep || !$salesRep->sage_id) {
                throw new \RuntimeException(
                    "SalesRep local_id {$data['sales_rep_local_id']} is not synced to Sage yet"
                );
            }
            $data['sage_salesrep_id']  = (int) $salesRep->sage_id;
        }

        // 3. save locally first
        $localId = $this->repo->create($data);

        // 4. push to Sage
        try {
            $result = $this->client->create($data);

            // 5. attach sage_id
            $this->repo->markSynced($localId, $result->ID);

            return (object) [
                'status'   => 'synced',
                'local_id' => $localId,
                'sage_id'  => $result->ID,
                'data'     => $result,
            ];
        } catch (\Throwable $e) {
            fwrite(STDOUT, "\nCAUGHT ERROR: " . $e->getMessage() . "\n");
            fwrite(STDOUT, "FILE: " . $e->getFile() . " LINE: " . $e->getLine() . "\n");

            $this->repo->markFailed($localId, $e->getMessage());

            return (object) [
                'status'   => 'failed',
                'local_id' => $localId,
                'error'    => $e->getMessage(),
            ];
        }
    }

    public function getByLocalId(int $localId): object
    {
        $local = $this->repo->findByLocalId($localId);

        if (!$local) {
            throw new \RuntimeException("Customer with local ID {$localId} not found");
        }

        if ($local->status === 'synced' && $local->sage_id) {
            return $this->client->getById((int) $local->sage_id);
        }

        return $local;
    }
}
