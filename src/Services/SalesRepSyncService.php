<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Client\SalesRepClient;
use ExcelleInsights\Sage\Repositories\SalesRepRepository;

class SalesRepSyncService
{
    public function __construct(
        private SalesRepRepository $repo,
        private SalesRepClient     $client
    ) {}

    public function create(array $data): object
    {
        // 1. save locally first
        $localId = $this->repo->create($data);

        // 2. push to Sage
        try {
            $result = $this->client->create($data);

            // 3. attach sage_id to local record
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

    /**
     * Fetch from local DB by primary key
     * Uses sage_id to fetch from Sage if needed
     */
    public function getByLocalId(int $localId): object
    {
        $local = $this->repo->findByLocalId($localId);

        if (!$local) {
            throw new \RuntimeException("SalesRep with local ID {$localId} not found");
        }

        // if synced, fetch fresh from Sage using sage_id
        // if ($local['status'] === 'synced' && $local['sage_id']) {
        //     return $this->client->getById((int) $local['sage_id']);
        // }
        // if ($local->status === 'synced' && $local->sage_id) {
        //     return $this->client->getById((int) $local->sage_id);
        // }
        // not yet synced — return local data
        return (object) $local;
    }
}
