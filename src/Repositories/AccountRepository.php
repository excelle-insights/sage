<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class AccountRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_accounts
                (local_id, name, description, category_id, active, balance, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );

        $stmt->execute([
            $data['local_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['category_id'] ?? null,
            $data['active']      ?? true,
            $data['balance']     ?? 0.00,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_accounts WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findByLocalId(int $localId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_accounts WHERE local_id = ? AND status = 'synced' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$localId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_accounts WHERE sage_id = ?"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function markSynced(int $localId, int $sageId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_accounts SET sage_id = ?, status = 'synced', error = NULL WHERE id = ?"
        );
        $stmt->execute([$sageId, $localId]);
    }

    public function markFailed(int $localId, string $error): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_accounts SET status = 'failed', error = ?, retry_count = retry_count + 1 WHERE id = ?"
        );
        $stmt->execute([$error, $localId]);
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM sage_accounts WHERE status IN ('pending', 'failed') AND retry_count < 5"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
