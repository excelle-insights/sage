<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class InvoiceRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Create a local invoice (before  sync)
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO _invoices (
                local_id,
                _company_id,
                _customer_id,
                invoice_number,
                status,
                txn_date,
                due_date,
                currency,
                created_at,
                updated_at
            ) VALUES (
                :local_id,
                :company_id,
                :qbo_customer_id,
                :invoice_number,
                :status,
                :txn_date,
                :due_date,
                :currency,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            ':local_id'       => $data['local_id'],
            ':company_id'       => $data['qbo_company_id'],
            ':qbo_customer_id'  => $data['qbo_customer_id'],
            ':invoice_number'   => $data['invoice_number'] ?? null,
            ':status'           => $data['status'] ?? 'pending',
            ':txn_date'         => $data['txn_date'] ?? date('Y-m-d'),
            ':due_date'         => $data['due_date'] ?? null,
            ':currency'         => $data['currency'] ?? 'KES',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Mark invoice as synced after successful  creation
     */
    public function markSynced(
        int $id,
        string $qboId,
        string $syncToken,
        float $total
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE _invoices
            SET
                _id      = :qbo_id,
                sync_token  = :sync_token,
                total       = :total,
                status      = 'synced',
                last_attempt_at  = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':qbo_id'     => $qboId,
            ':sync_token' => $syncToken,
            ':total'      => $total,
            ':id'         => $id,
        ]);
    }

    /**
     * Mark invoice as failed (retry later)
     */
    public function markFailed(int $id, string $reason): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE _invoices
            SET
                status = 'failed',
                retry_count = retry_count + 1, 
                error_message = :reason,
                last_attempt_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':reason' => $reason,
            ':id'     => $id,
        ]);
    }

    /**
     * Find by local ID
     */
    public function find(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM _invoices WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * Find by  ID (webhooks)
     */
    public function findByQboId(string $qboId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM _invoices WHERE _id = ?"
        );
        $stmt->execute([$qboId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * Invoices pending sync
     */
    public function getPending(int $maxRetries = 5): array
    {
        $stmt = $this->pdo->prepare("
        SELECT *
        FROM _invoices
        WHERE status IN ('pending','failed')
          AND retry_count < :maxRetries
        ORDER BY last_attempt_at ASC
    ");
        $stmt->execute([':maxRetries' => $maxRetries]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update local invoice
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE _invoices
            SET
                invoice_number = :invoice_number,
                txn_date       = :txn_date,
                due_date       = :due_date,
                currency       = :currency,
                updated_at     = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':invoice_number' => $data['invoice_number'] ?? null,
            ':txn_date'       => $data['txn_date'],
            ':due_date'       => $data['due_date'] ?? null,
            ':currency'       => $data['currency'] ?? 'KES',
            ':id'             => $id,
        ]);
    }
}
