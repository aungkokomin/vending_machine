<?php

declare(strict_types=1);

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function find(int $id): ?array;

    public function findByEmail(string $email): ?array;

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
