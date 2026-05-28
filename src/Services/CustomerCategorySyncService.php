<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Client\CustomerCategoryClient;
use ExcelleInsights\Sage\Repositories\CustomerCategoryRepository;

class CustomerCategorySyncService
{
    public function __construct(
        private CustomerCategoryRepository $repo,
        private CustomerCategoryClient     $client
    ) {}

    public function create(array $data): object
    {
        // 1. save locally first
        $localId = $this->repo->create($data);

        // 2. push to Sage
        try {
            $result = $this->client->create($data);

            // 3. attach sage_id
            $this->repo->markSynced($localId, $result->ID);

            return (object) [
                'status'   => 'synced',
                'local_id' => $localId,
                'sage_id'  => $result->ID,
                'data'     => $result,
            ];
        } catch (\Throwable $e) {
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
            throw new \RuntimeException("Category with local ID {$localId} not found");
        }

        if ($local->status === 'synced' && $local->sage_id) {
            return $this->client->getById((int) $local->sage_id);
        }

        return $local;
    }
}
