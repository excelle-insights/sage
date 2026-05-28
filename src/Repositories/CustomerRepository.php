<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class CustomerRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_customers
                (local_id, name, active,sage_category_id,sage_salesrep_id, email,pin,mobile, telephone, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
        );

        $stmt->execute([
            $data['local_id'],
            $data['name'],
            $data['active']             ?? true,
            $data['sage_category_id']  ?? null,
            $data['sage_salesrep_id'] ?? null,
            $data['email']              ?? null,
            $data['pin']              ?? null,
            $data['mobile']             ?? null,
            $data['telephone']          ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customers WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findByLocalId(int $localId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customers WHERE local_id = ?"
        );
        $stmt->execute([$localId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customers WHERE sage_id = ?"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function markSynced(int $localId, int $sageId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_customers SET sage_id = ?, status = 'synced', error = NULL WHERE id = ?"
        );
        $stmt->execute([$sageId, $localId]);
    }

    public function markFailed(int $localId, string $error): void
    {
        $stmt = $this->pdo->prepare(
            // "UPDATE sage_customers SET status = 'failed', error = ?, retry_count = retry_count + 1 WHERE id = ?"
            "UPDATE sage_customers SET status = 'failed', error = ? WHERE id = ?"

        );
        $stmt->execute([$error, $localId]);
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM sage_customers WHERE status IN ('pending', 'failed') AND retry_count < 5"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
