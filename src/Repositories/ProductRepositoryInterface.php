<?php

declare(strict_types=1);

namespace App\Repositories;

interface ProductRepositoryInterface
{
    public function paginate(int $page, int $perPage, string $sort, string $direction): array;

    public function count(): int;

    public function find(int $id): ?array;

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function decrementQuantity(int $id, int $quantity): bool;
}
