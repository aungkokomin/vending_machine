<?php

declare(strict_types=1);

namespace App\Repositories;

interface TransactionRepositoryInterface
{
    public function create(array $data): int;

    public function find(int $id): ?array;

    public function forUser(int $userId): array;
}
