<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\Guard;
use App\Http\Route;
use App\Repositories\ProductRepositoryInterface;
use App\Services\PurchaseService;
use App\Support\RedirectResponse;
use App\Support\Validator;
use App\Support\View;
use RuntimeException;

final class ProductsController
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly PurchaseService $purchaseService,
        private readonly Auth $auth,
        private readonly Guard $guard
    ) {
    }

    public function index(): string
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 8;
        $sort = (string) ($_GET['sort'] ?? 'name');
        $direction = (string) ($_GET['direction'] ?? 'ASC');
        $total = $this->products->count();

        return View::render('products/index', [
            'products' => $this->products->paginate($page, $perPage, $sort, $direction),
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'sort' => $sort,
            'direction' => strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC',
            'authUser' => $this->auth->user(),
        ]);
    }

    public function create(): string
    {
        $this->guard->requireAdmin();

        return View::render('products/create', [
            'product' => ['name' => '', 'description' => '', 'price' => '', 'quantity' => ''],
            'errors' => [],
            'authUser' => $this->auth->user(),
        ]);
    }

    public function store(): string|RedirectResponse
    {
        $this->guard->requireAdmin();
        $errors = Validator::product($_POST);

        if ($errors) {
            return View::render('products/create', [
                'product' => $_POST,
                'errors' => $errors,
                'authUser' => $this->auth->user(),
            ]);
        }

        $this->products->create($this->productData($_POST));

        return new RedirectResponse('/products');
    }

    public function edit(string $id): string
    {
        $this->guard->requireAdmin();
        $product = $this->products->find((int) $id);

        if (!$product) {
            http_response_code(404);
            return 'Product not found.';
        }

        return View::render('products/edit', [
            'product' => $product,
            'errors' => [],
            'authUser' => $this->auth->user(),
        ]);
    }

    public function update(string $id): string|RedirectResponse
    {
        $this->guard->requireAdmin();
        $errors = Validator::product($_POST);

        if ($errors) {
            return View::render('products/edit', [
                'product' => array_merge($_POST, ['id' => (int) $id]),
                'errors' => $errors,
                'authUser' => $this->auth->user(),
            ]);
        }

        $this->products->update((int) $id, $this->productData($_POST));

        return new RedirectResponse('/products');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->guard->requireAdmin();
        $this->products->delete((int) $id);

        return new RedirectResponse('/products');
    }

    #[Route('GET', '/products/{id}/purchase')]
    public function purchaseForm(string $id): string
    {
        $this->guard->requireLogin();
        $product = $this->products->find((int) $id);

        if (!$product) {
            http_response_code(404);
            return 'Product not found.';
        }

        return View::render('products/purchase', [
            'product' => $product,
            'errors' => [],
            'authUser' => $this->auth->user(),
        ]);
    }

    #[Route('POST', '/products/{id}/purchase')]
    public function purchase(string $id): string|RedirectResponse
    {
        $this->guard->requireLogin();
        $product = $this->products->find((int) $id);

        if (!$product) {
            http_response_code(404);
            return 'Product not found.';
        }

        $errors = Validator::purchase($_POST, (int) $product['quantity']);
        if ($errors) {
            return View::render('products/purchase', [
                'product' => $product,
                'errors' => $errors,
                'authUser' => $this->auth->user(),
            ]);
        }

        try {
            $this->purchaseService->purchase((int) $id, (int) $this->auth->id(), (int) $_POST['quantity']);
        } catch (RuntimeException $exception) {
            return View::render('products/purchase', [
                'product' => $product,
                'errors' => ['quantity' => $exception->getMessage()],
                'authUser' => $this->auth->user(),
            ]);
        }

        return new RedirectResponse('/products?purchase=success');
    }

    private function productData(array $input): array
    {
        return [
            'name' => trim((string) $input['name']),
            'description' => trim((string) ($input['description'] ?? '')),
            'price' => number_format((float) $input['price'], 2, '.', ''),
            'quantity' => (int) $input['quantity'],
        ];
    }
}
