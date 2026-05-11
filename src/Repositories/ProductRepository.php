<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository implements ProductRepositoryInterface
{
    private const SORTABLE = ['id', 'name', 'price', 'quantity', 'created_at'];

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function paginate(int $page, int $perPage, string $sort, string $direction): array
    {
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'name';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $offset = max(0, ($page - 1) * $perPage);

        $statement = $this->pdo->prepare(
            "SELECT * FROM products WHERE deleted_at IS NULL ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset"
        );
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM products WHERE deleted_at IS NULL')->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE id = :id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();

        return $product ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products (name, description, price, quantity) VALUES (:name, :description, :price, :quantity)'
        );
        $statement->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'quantity' => $data['quantity'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET name = :name, description = :description, price = :price, quantity = :quantity WHERE id = :id AND deleted_at IS NULL'
        );

        return $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'quantity' => $data['quantity'],
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('UPDATE products SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL');

        return $statement->execute(['id' => $id]);
    }

    public function decrementQuantity(int $id, int $quantity): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET quantity = quantity - :quantity WHERE id = :id AND quantity >= :input_quantity AND deleted_at IS NULL'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $statement->bindValue(':input_quantity', $quantity, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() === 1;
    }
}
