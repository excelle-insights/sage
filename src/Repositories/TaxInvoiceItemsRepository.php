<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class TaxInvoiceItemsRepository
{
    public function __construct(private PDO $pdo) {}

    public function createLine(int $invoiceId, array $line): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_tax_invoice_items
                (invoice_id, line_type, selection_id, description, tax_type_id, quantity, unit_price, discount)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $invoiceId,
            $line['line_type']    ?? 1,
            $line['selection_id'] ?? null,
            $line['description']  ?? null,
            $line['tax_type_id']  ?? null,
            $line['quantity']     ?? 1,
            $line['unit_price'],
            $line['discount']     ?? 0.00,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByInvoiceId(int $invoiceId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_tax_invoice_items WHERE invoice_id = ?"
        );
        $stmt->execute([$invoiceId]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
