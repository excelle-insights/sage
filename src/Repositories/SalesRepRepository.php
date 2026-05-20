<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class SalesRepRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Insert a new sales rep locally
     * Returns the local primary key
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_sales_reps 
            (local_id, first_name, last_name, email, mobile, active, status)
         VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );

        $stmt->execute([
            $data['local_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email']  ?? null,
            $data['mobile'] ?? null,
            $data['active'] ?? true,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Fetch by local primary key
     */
    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_sales_reps WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }


    /**
     * Fetch by Sage ID
     */
    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_sales_reps WHERE sage_id = ?"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * After successful Sage sync — attach sage_id
     */
    public function markSynced(int $localId, int $sageId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_sales_reps SET sage_id = ?, status = 'synced', error = NULL WHERE id = ?"
        );
        $stmt->execute([$sageId, $localId]);
    }

    /**
     * After failed Sage sync — log error
     */
    public function markFailed(int $localId, string $error): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_sales_reps SET status = 'failed', error = ?, retry_count = retry_count + 1 WHERE id = ?"
        );
        $stmt->execute([$error, $localId]);
    }

    /**
     * Get all pending/failed records for retry
     */
    public function getPending(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM sage_sales_reps 
            WHERE status IN ('pending', 'failed') AND retry_count < 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
