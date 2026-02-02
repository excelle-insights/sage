<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class CustomerRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Insert local customer (before  sync)
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO _customers (
                local_id,
                _company_id,
                name,
                display_name,
                email,
                phone,
                company_name,
                country,
                city,
                postal_code,
                line,
                active,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $data['local_id'],
            $data['qbo_company_id'],
            $data['name'],
            $data['display_name'] ?? $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['company_name'] ?? null,
            $data['country'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['line'] ?? null,
            $data['active'] ?? true,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Attach  identifiers after successful sync
     */
    public function markSynced(
        int $id,
        string $qboId,
        string $syncToken
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE _customers
            SET _id = ?, sync_token = ?, status = ?, last_attempt_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$qboId, $syncToken, 'synced', $id]);
    }
    public function markFailed(int $id, string $error): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE _customers
            SET status = 'failed',
                retry_count = retry_count + 1,
                last_attempt_at = NOW(),
                error_message = :error
            WHERE id = :id
        ");
        $stmt->execute([':error' => $error, ':id' => $id]);
    }

    /**
     * Update local customer (local source of truth)
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE _customers
            SET
                name = ?,
                display_name = ?,
                email = ?,
                phone = ?,
                company_name = ?,
                country = ?,
                city = ?,
                postal_code = ?,
                line = ?,
                active = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $data['name'],
            $data['display_name'] ?? $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['company_name'] ?? null,
            $data['country'] ?? null,
            $data['city'] ?? null,
            $data['postal_code'] ?? null,
            $data['line'] ?? null,
            $data['active'] ?? true,
            $id
        ]);
    }

    /**
     * Find by local ID
     */
    public function find(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM _customers WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * Find by  ID (used by webhooks)
     */
    public function findByQboId(string $qboId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM _customers WHERE _id = ?"
        );
        $stmt->execute([$qboId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * Customers pending initial sync
     */
    public function getPending(int $maxRetries = 5): array
    {
        $stmt = $this->pdo->prepare("
        SELECT *
        FROM _customers
        WHERE status IN ('pending','failed')
          AND retry_count < :maxRetries
        ORDER BY last_attempt_at ASC
    ");
        $stmt->execute([':maxRetries' => $maxRetries]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
