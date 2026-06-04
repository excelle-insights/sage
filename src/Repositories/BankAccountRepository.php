<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class BankAccountRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_bank_accounts
        (local_id, account_name,bank_name, account_number, branch_name, branch_code, description, payment_method, opening_balance, active, is_default, status)
     VALUES (?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, 'pending')"
        );

        $stmt->execute([
            $data['local_id'],
            $data['account_name'],
            $data['bank_name']      ?? null,
            $data['account_number'] ?? null,
            $data['branch_name']    ?? null,
            $data['branch_code']    ?? null,
            $data['description']    ?? null,
            $data['payment_method'] ?? 'Cash',
            $data['opening_balance'] ?? 0.00,
            $data['active']          ? 1 : 0,    
            $data['is_default']      ? 1 : 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_bank_accounts WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findByLocalId(int $localId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_bank_accounts WHERE local_id = ? AND status = 'synced' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$localId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_bank_accounts WHERE sage_id = ?"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findDefault(): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_bank_accounts WHERE is_default = 1 AND status = 'synced' LIMIT 1"
        );
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function markSynced(int $localId, int $sageId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_bank_accounts SET sage_id = ?, status = 'synced', error = NULL WHERE id = ?"
        );
        $stmt->execute([$sageId, $localId]);
    }

    public function markFailed(int $localId, string $error): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_bank_accounts SET status = 'failed', error = ?, retry_count = retry_count + 1 WHERE id = ?"
        );
        $stmt->execute([$error, $localId]);
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM sage_bank_accounts WHERE status IN ('pending', 'failed') AND retry_count < 5"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
