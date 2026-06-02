<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class TaxInvoiceRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_tax_invoice
                (local_id, sage_customer_id, sage_salesrep_id, date, due_date, customer_reference, inclusive, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
        );

        $stmt->execute([
            $data['local_id'],
            $data['sage_customer_id']  ?? null,
            $data['sage_salesrep_id']  ?? null,
            $data['date'],
            $data['due_date']           ?? null,
            $data['customer_reference'] ?? null,
            $data['inclusive']          ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_tax_invoice WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findByLocalId(int $localId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_tax_invoice WHERE local_id = ? AND status = 'synced' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$localId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_tax_invoice WHERE sage_id = ?"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function markSynced(int $localId, int $sageId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_tax_invoice SET sage_id = ?, status = 'synced', error = NULL WHERE id = ?"
        );
        $stmt->execute([$sageId, $localId]);
    }

    public function markFailed(int $localId, string $error): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_tax_invoice SET status = 'failed', error = ?, retry_count = retry_count + 1 WHERE id = ?"
        );
        $stmt->execute([$error, $localId]);
    }
}
