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

$products = [
    [
        'name' => 'Water',
        'description' => 'Cold bottled water',
        'price' => 1.00,
        'quantity' => 25,
    ],
    [
        'name' => 'Soda',
        'description' => 'Carbonated soft drink',
        'price' => 1.50,
        'quantity' => 20,
    ],
    [
        'name' => 'Chips',
        'description' => 'Salted potato chips',
        'price' => 2.00,
        'quantity' => 15,
    ],
];

$findProduct = $pdo->prepare('SELECT id FROM products WHERE name = :name LIMIT 1');
$createProduct = $pdo->prepare(
    'INSERT INTO products (name, description, price, quantity)
     VALUES (:name, :description, :price, :quantity)'
);

foreach ($products as $product) {
    $findProduct->execute(['name' => $product['name']]);
    if ($findProduct->fetchColumn() !== false) {
        echo "Skipped existing product: {$product['name']}\n";
        continue;
    }

    $createProduct->execute([
        'name' => $product['name'],
        'description' => $product['description'],
        'price' => $product['price'],
        'quantity' => $product['quantity'],
    ]);

    echo "Seeded product {$product['name']} with id {$pdo->lastInsertId()}\n";
}
