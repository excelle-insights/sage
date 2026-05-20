<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class CustomerCategoryRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_customer_categories
                (local_id, description, status)
             VALUES (?, ?, 'pending')"
        );

        $stmt->execute([
            $data['local_id'],
            $data['description'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customer_categories WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findByLocalId(int $localId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customer_categories WHERE local_id = ?"
        );
        $stmt->execute([$localId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customer_categories WHERE sage_id = ?"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function markSynced(int $localId, int $sageId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_customer_categories SET sage_id = ?, status = 'synced', error = NULL WHERE id = ?"
        );
        $stmt->execute([$sageId, $localId]);
    }

    public function markFailed(int $localId, string $error): void
    {
        // $stmt = $this->pdo->prepare(
        //     "UPDATE sage_customer_categories SET status = 'failed', error = ?, retry_count = retry_count + 1 WHERE id = ?"
        // );
        $stmt = $this->pdo->prepare(
            "UPDATE sage_customer_categories SET status = 'failed', error = ? WHERE id = ?"
        );
        $stmt->execute([$error, $localId]);
    }

    // public function getPending(): array
    // {
    //     $stmt = $this->pdo->query(
    //         "SELECT * FROM sage_customer_categories WHERE status IN ('pending', 'failed') AND retry_count < 5"
    //     );
    //     return $stmt->fetchAll(PDO::FETCH_OBJ);
    // }
}
