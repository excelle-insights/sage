<?php

namespace ExcelleInsights\Sage\Services;

use ExcelleInsights\Sage\Client\TaxTypeClient;
use ExcelleInsights\Sage\Repositories\TaxTypeRepository;

class TaxTypeSyncService
{
    public function __construct(
        private TaxTypeRepository $repo,
        private TaxTypeClient     $client
    ) {}

    /**
     * Create a new tax type in Sage and cache locally
     */
    public function create(array $data): object
    {
        try {
            // 1. push to Sage first — sage_id is what we need
            $result = $this->client->create($data);

            // 2. cache locally using sage_id
            $this->repo->create([
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
        } catch (\Throwable $e) {
            return (object) [
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ];
        }
    }
    public function update(int $id, array $data): object
    {
        // 1. find local record by primary key
        $local = $this->repo->findById($id);

        if (!$local) {
            throw new \RuntimeException("TaxType with id {$id} not found");
        }

        // 2. push to Sage using resolved sage_id
        try {
            $result = $this->client->update((int) $local->sage_id, $data);

            // 3. update local record by primary key
            $this->repo->update($id, [
                'name'       => $result->Name,
                'percentage' => $result->Percentage,
                'active'     => $result->Active ?? true,
            ]);

            return (object) [
                'status'  => 'synced',
                'sage_id' => $result->ID,
                'data'    => $result,
            ];
        } catch (\Throwable $e) {
            return (object) [
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Pull all tax types from Sage and cache locally
     * Run once during setup
     */
    // public function sync(): void
    // {
    //     $result = $this->client->getAll();

    //     foreach ($result->Results ?? $result as $taxType) {
    //         $this->repo->create([
    //             'sage_id'    => $taxType->ID,
    //             'name'       => $taxType->Name,
    //             'percentage' => $taxType->Percentage,
    //             'active'     => $taxType->Active ?? true,
    //         ]);
    //     }
    // }

    /**
     * Find by name — used when building invoice lines
     */
    public function findByName(string $name): ?object
    {
        return $this->repo->findByName($name);
    }

    /**
     * Find by Sage ID
     */
    public function findBySageId(int $sageId): ?object
    {
        return $this->repo->findBySageId($sageId);
    }
}
