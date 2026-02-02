<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class PaymentItemRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Create one or multiple payment items
     */
    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO _payment_items
            (qbo_payment_id, _invoice_id, amount, created_at)
            VALUES
            (:qbo_payment_id, :qbo_invoice_id, :amount, NOW())
        ");

        // Accept either a single item or multiple items
        $items = isset($data[0]) ? $data : [$data];

        foreach ($items as $item) {
            $stmt->execute([
                ':qbo_payment_id' => $item['qbo_payment_id'],
                ':qbo_invoice_id' => $item['qbo_invoice_id'],
                ':amount' => $item['amount'],
            ]);
        }
    }

    public function getByPaymentId(int $paymentId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM _payment_items WHERE _payment_id = :qbo_payment_id
        ");
        $stmt->execute([':qbo_payment_id' => $paymentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
