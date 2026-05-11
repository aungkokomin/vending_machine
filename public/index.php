<?php

declare(strict_types=1);

use App\Auth\Auth;
use App\Auth\Guard;
use App\Controllers\Api\ProductsApiController;
use App\Controllers\AuthController;
use App\Controllers\ProductsController;
use App\Database\Connection;
use App\Http\Router;
use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\PurchaseService;
use App\Support\JwtService;
use App\Support\RedirectResponse;

require dirname(__DIR__) . '/src/bootstrap.php';

$config = require dirname(__DIR__) . '/config/config.php';
$pdo = (new Connection($config))->pdo();

$products = new ProductRepository($pdo);
$users = new UserRepository($pdo);
$transactions = new TransactionRepository($pdo);
$auth = new Auth($users);
$guard = new Guard($auth);
$purchaseService = new PurchaseService($products, $transactions);

$productsController = new ProductsController($products, $purchaseService, $auth, $guard);
$authController = new AuthController($auth);
$apiController = new ProductsApiController($products, $purchaseService, $auth, new JwtService($config['jwt_secret']));

$router = new Router();
require dirname(__DIR__) . '/src/routes.php';

$response = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
if ($response instanceof RedirectResponse) {
    $response->send();
}

echo $response;
