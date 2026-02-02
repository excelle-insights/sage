<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class InvoiceItemRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Insert a local invoice line item
     */
    public function create(int $invoiceId, array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO _invoice_items (
                _invoice_id,
                item_id,
                item_name,
                description,
                quantity,
                unit_price,
                amount,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $invoiceId,
            $data['item_id'] ?? '',
            $data['item_name'] ?? '',
            $data['description'] ?? '',
            $data['quantity'] ?? 1,
            $data['unit_price'] ?? 0,
            ($data['quantity'] ?? 1) * ($data['unit_price'] ?? 0),
        ]);
    }

    /**
     * Get invoice items formatted for Sage API
     */
    public function forInvoice(int $invoiceId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT description, quantity, unit_price, amount
            FROM _invoice_items
            WHERE _invoice_id = ?
        ");
        $stmt->execute([$invoiceId]);

        $items = [];

        foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $row) {
            $items[] = [
                'DetailType' => 'SalesItemLineDetail',
                'Amount' => (float) $row->amount,
                'Description' => $row->description,
                'SalesItemLineDetail' => [
                    "ItemRef" => [
                        "value" => isset($row->item_id) ? $row->item_id : "",
                        "name" => isset($row->item_name) ? $row->item_name : ""
                    ],
                    'Qty' => (float) $row->quantity,
                    'UnitPrice' => (float) $row->unit_price,
                ],
            ];
        }

        return $items;
    }
}
