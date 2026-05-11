<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO transactions (user_id, product_id, quantity, unit_price, total_amount)
             VALUES (:user_id, :product_id, :quantity, :unit_price, :total_amount)'
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'total_amount' => $data['total_amount'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM transactions WHERE id = :id');
        $statement->execute(['id' => $id]);
        $transaction = $statement->fetch();

        return $transaction ?: null;
    }

    public function forUser(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT t.*, p.name AS product_name
             FROM transactions t
             JOIN products p ON p.id = t.product_id
             WHERE t.user_id = :user_id
             ORDER BY t.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }
}
