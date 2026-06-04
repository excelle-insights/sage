<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class CustomerReceiptRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_customer_receipts
    (invoice_id, sage_customer_id, sage_invoice_id, bank_account_id, date, total, reference, description, comments, payment_method, reconciled, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"  );

        $stmt->execute([
            $data['invoice_id'],
            $data['sage_customer_id'] ?? null,
            $data['sage_invoice_id']  ?? null,
            $data['bank_account_id']  ?? null,
            $data['date'],
            $data['total'],
            $data['reference']        ?? null,
            $data['description']      ?? null,
            $data['comments']         ?? null,
            $data['payment_method']   ?? 'Cash',
            $data['reconciled']       ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByInvoiceId(int $invoiceId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customer_receipts WHERE invoice_id = ? AND status = 'synced' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$invoiceId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_customer_receipts WHERE sage_id = ?"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }


    public function markSynced(int $id, int $sageId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_customer_receipts SET sage_id = ?, status = 'synced', error = NULL WHERE id = ?"
        );
        $stmt->execute([$sageId, $id]);
    }

    public function markFailed(int $id, string $error): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_customer_receipts SET status = 'failed', error = ?, retry_count = retry_count + 1 WHERE id = ?"
        );
        $stmt->execute([$error, $id]);
    }
}
