<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
$config = require dirname(__DIR__) . '/config/config.php';

use App\Database\Connection;
use App\Repositories\UserRepository;

$pdo = (new Connection($config))->pdo();
$userRepository = new UserRepository($pdo);

$users = [
    [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'Admin',
    ],
    [
        'name' => 'User',
        'email' => 'user@example.com',
        'password' => 'password',
        'role' => 'User',
    ],
];

foreach ($users as $user) {
    $existing = $userRepository->findByEmail($user['email']);
    if ($existing !== null) {
        echo "Skipped existing user: {$user['email']}\n";
        continue;
    }

    $id = $userRepository->create($user);
    echo "Seeded user {$user['email']} with id {$id}\n";
}
