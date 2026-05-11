<?php

declare(strict_types=1); // Enable strict typing for better type safety

// Web routes
$router->get('/', [$productsController, 'index']);
$router->get('/products', [$productsController, 'index']);
$router->get('/products/create', [$productsController, 'create']);
$router->post('/products', [$productsController, 'store']);
$router->get('/products/{id}/edit', [$productsController, 'edit']);
$router->post('/products/{id}/update', [$productsController, 'update']);
$router->post('/products/{id}/delete', [$productsController, 'destroy']);
$router->registerAttributes($productsController);

$router->get('/login', [$authController, 'loginForm']);
$router->post('/login', [$authController, 'login']);
$router->post('/logout', [$authController, 'logout']);

// API routes
$router->post('/api/token', [$apiController, 'token']);
$router->get('/api/products', [$apiController, 'index']);
$router->get('/api/products/{id}', [$apiController, 'show']);
$router->post('/api/products', [$apiController, 'store']);
$router->post('/api/products/{id}', [$apiController, 'update']);
$router->delete('/api/products/{id}', [$apiController, 'destroy']);
$router->post('/api/products/{id}/purchase', [$apiController, 'purchase']);