<?php

declare(strict_types=1);

namespace Tests;

use App\Auth\Auth;
use App\Auth\Guard;
use App\Controllers\ProductsController;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\PurchaseService;
use App\Support\RedirectResponse;
use PHPUnit\Framework\TestCase;

final class ProductsControllerTest extends TestCase
{
    public function testIndexPaginatesProducts(): void
    {
        $products = new InMemoryProductRepository([
            ['id' => 1, 'name' => 'Water', 'description' => 'Cold', 'price' => '1.50', 'quantity' => 10],
        ]);
        $controller = $this->controller($products);

        $_GET = ['page' => '1', 'sort' => 'name', 'direction' => 'ASC'];

        $html = $controller->index();

        self::assertStringContainsString('Water', $html);
        self::assertStringContainsString('Products', $html);
    }

    public function testStoreReturnsValidationErrorsForInvalidProduct(): void
    {
        $products = new InMemoryProductRepository();
        $controller = $this->controller($products, admin: true);

        $_POST = ['name' => '', 'price' => '-1', 'quantity' => '-2'];

        $response = $controller->store();

        self::assertIsString($response);
        self::assertStringContainsString('Product name is required.', $response);
        self::assertSame(0, $products->createCount);
    }

    public function testStoreCreatesValidProductForAdmin(): void
    {
        $products = new InMemoryProductRepository();
        $controller = $this->controller($products, admin: true);

        $_POST = ['name' => 'Juice', 'description' => 'Orange', 'price' => '2.25', 'quantity' => '5'];

        $response = $controller->store();

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/products', $response->url);
        self::assertSame(1, $products->createCount);
    }

    public function testPurchaseRejectsQuantityAboveAvailableStock(): void
    {
        $products = new InMemoryProductRepository([
            ['id' => 1, 'name' => 'Water', 'description' => 'Cold', 'price' => '1.50', 'quantity' => 1],
        ]);
        $transactions = new InMemoryTransactionRepository();
        $controller = $this->controller($products, transactions: $transactions);

        $_POST = ['quantity' => '2'];

        $response = $controller->purchase('1');

        self::assertIsString($response);
        self::assertStringContainsString('cannot exceed available stock', $response);
        self::assertSame(0, $transactions->createCount);
    }

    public function testPurchaseUpdatesQuantityAndCreatesTransaction(): void
    {
        $products = new InMemoryProductRepository([
            ['id' => 1, 'name' => 'Water', 'description' => 'Cold', 'price' => '1.50', 'quantity' => 4],
        ]);
        $transactions = new InMemoryTransactionRepository();
        $controller = $this->controller($products, transactions: $transactions);

        $_POST = ['quantity' => '3'];

        $response = $controller->purchase('1');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(1, $products->items[1]['quantity']);
        self::assertSame(1, $transactions->createCount);
        self::assertSame(4.5, $transactions->lastTransaction['total_amount']);
    }

    public function testDestroySoftDeletesProduct(): void
    {
        $products = new InMemoryProductRepository([
            ['id' => 1, 'name' => 'Water', 'description' => 'Cold', 'price' => '1.50', 'quantity' => 4],
        ]);
        $controller = $this->controller($products, admin: true);

        $response = $controller->destroy('1');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/products', $response->url);
        self::assertArrayHasKey(1, $products->items);
        self::assertNotNull($products->items[1]['deleted_at']);
        self::assertNull($products->find(1));
    }

    public function testDeletedProductsAreExcludedFromIndex(): void
    {
        $products = new InMemoryProductRepository([
            ['id' => 1, 'name' => 'Water', 'description' => 'Cold', 'price' => '1.50', 'quantity' => 4, 'deleted_at' => '2026-05-12 10:00:00'],
            ['id' => 2, 'name' => 'Soda', 'description' => 'Cold', 'price' => '1.75', 'quantity' => 2],
        ]);
        $controller = $this->controller($products);

        $_GET = ['page' => '1', 'sort' => 'name', 'direction' => 'ASC'];

        $html = $controller->index();

        self::assertStringNotContainsString('Water', $html);
        self::assertStringContainsString('Soda', $html);
    }

    private function controller(
        InMemoryProductRepository $products,
        ?InMemoryTransactionRepository $transactions = null,
        bool $admin = false
    ): ProductsController {
        $transactions ??= new InMemoryTransactionRepository();
        $auth = new TestAuth($admin);
        $guard = new TestGuard($auth, $admin);
        $purchaseService = new PurchaseService($products, $transactions);

        return new ProductsController($products, $purchaseService, $auth, $guard);
    }
}

final class TestAuth extends Auth
{
    public function __construct(private readonly bool $admin = false)
    {
    }

    public function user(): ?array
    {
        return ['id' => 7, 'name' => 'Test User', 'email' => 'test@example.com', 'role' => $this->admin ? 'Admin' : 'User'];
    }

    public function id(): ?int
    {
        return 7;
    }

    public function check(): bool
    {
        return true;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }
}

final class TestGuard extends Guard
{
    public function __construct(private readonly Auth $auth, private readonly bool $admin = false)
    {
    }

    public function requireLogin(): void
    {
    }

    public function requireAdmin(): void
    {
        if (!$this->admin) {
            throw new \RuntimeException('Admin role required.');
        }
    }
}

final class InMemoryProductRepository implements ProductRepositoryInterface
{
    /** @var array<int, array> */
    public array $items = [];
    public int $createCount = 0;

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->items[(int) $item['id']] = $item;
        }
    }

    public function paginate(int $page, int $perPage, string $sort, string $direction): array
    {
        return array_values(array_filter(
            $this->items,
            fn (array $item): bool => !isset($item['deleted_at']) || $item['deleted_at'] === null
        ));
    }

    public function count(): int
    {
        return count($this->paginate(1, 999, 'name', 'ASC'));
    }

    public function find(int $id): ?array
    {
        $item = $this->items[$id] ?? null;

        return isset($item['deleted_at']) && $item['deleted_at'] !== null ? null : $item;
    }

    public function create(array $data): int
    {
        $this->createCount++;
        $id = count($this->items) + 1;
        $this->items[$id] = array_merge(['id' => $id], $data);

        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $this->items[$id] = array_merge($this->items[$id] ?? ['id' => $id], $data);

        return true;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->items[$id])) {
            return false;
        }

        $this->items[$id]['deleted_at'] = date('Y-m-d H:i:s');

        return true;
    }

    public function decrementQuantity(int $id, int $quantity): bool
    {
        if (!isset($this->items[$id]) || $this->items[$id]['quantity'] < $quantity) {
            return false;
        }

        $this->items[$id]['quantity'] -= $quantity;

        return true;
    }
}

final class InMemoryTransactionRepository implements TransactionRepositoryInterface
{
    public int $createCount = 0;
    public array $lastTransaction = [];

    public function create(array $data): int
    {
        $this->createCount++;
        $this->lastTransaction = $data;

        return 99;
    }

    public function find(int $id): ?array
    {
        return $id === 99 ? $this->lastTransaction : null;
    }

    public function forUser(int $userId): array
    {
        return $this->lastTransaction ? [$this->lastTransaction] : [];
    }
}

final class NullUserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?array
    {
        return null;
    }

    public function findByEmail(string $email): ?array
    {
        return null;
    }

    public function create(array $data): int
    {
        return 0;
    }

    public function update(int $id, array $data): bool
    {
        return false;
    }

    public function delete(int $id): bool
    {
        return false;
    }
}
