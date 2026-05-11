<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)'
        );
        $statement->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'User',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'name = :name',
            'email = :email',
            'role = :role',
        ];
        $params = [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'User',
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $statement = $this->pdo->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');

        return $statement->execute($params);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM users WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
