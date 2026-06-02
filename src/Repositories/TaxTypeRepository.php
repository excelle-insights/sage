<?php

namespace ExcelleInsights\Sage\Repositories;

use PDO;

class TaxTypeRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sage_tax_types (sage_id, name, percentage, active)
         VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['sage_id'],
            $data['name'],
            $data['percentage'],
            $data['active'] ?? true,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE sage_tax_types 
         SET name = ?, percentage = ?, active = ?
         WHERE id = ?"
        );

        $stmt->execute([
            $data['name'],
            $data['percentage'],
            $data['active'] ?? true,
            $id,
        ]);
    }
    public function findByName(string $name): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_tax_types WHERE name = ? LIMIT 1"
        );
        $stmt->execute([$name]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function findBySageId(int $sageId): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_tax_types WHERE sage_id = ? LIMIT 1"
        );
        $stmt->execute([$sageId]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function all(): array
    {
        return $this->pdo->query(
            "SELECT * FROM sage_tax_types WHERE active = 1"
        )->fetchAll(PDO::FETCH_OBJ);
    }
    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM sage_tax_types WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }
}
