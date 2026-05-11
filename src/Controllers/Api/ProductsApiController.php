<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Auth\Auth;
use App\Http\JsonResponse;
use App\Repositories\ProductRepositoryInterface;
use App\Services\PurchaseService;
use App\Support\JwtService;
use App\Support\Validator;
use RuntimeException;

final class ProductsApiController
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly PurchaseService $purchaseService,
        private readonly Auth $auth,
        private readonly JwtService $jwt
    ) {
    }

    public function index(): never
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        JsonResponse::send([
            'data' => $this->products->paginate($page, 20, (string) ($_GET['sort'] ?? 'name'), (string) ($_GET['direction'] ?? 'ASC')),
            'page' => $page,
        ]);
    }

    public function show(string $id): never
    {
        $product = $this->products->find((int) $id);
        JsonResponse::send($product ? ['data' => $product] : ['error' => 'Product not found.'], $product ? 200 : 404);
    }

    public function store(): never
    {
        $this->requireRole('Admin');
        $input = $this->jsonInput();
        $errors = Validator::product($input);
        if ($errors) {
            JsonResponse::send(['errors' => $errors], 422);
        }

        $id = $this->products->create($input);
        JsonResponse::send(['id' => $id], 201);
    }

    public function update(string $id): never
    {
        $this->requireRole('Admin');
        $input = $this->jsonInput();
        $errors = Validator::product($input);
        if ($errors) {
            JsonResponse::send(['errors' => $errors], 422);
        }

        $this->products->update((int) $id, $input);
        JsonResponse::send(['updated' => true]);
    }

    public function destroy(string $id): never
    {
        $this->requireRole('Admin');
        $this->products->delete((int) $id);
        JsonResponse::send(['deleted' => true]);
    }

    public function purchase(string $id): never
    {
        $user = $this->requireToken();
        $product = $this->products->find((int) $id);
        if (!$product) {
            JsonResponse::send(['error' => 'Product not found.'], 404);
        }

        $input = $this->jsonInput();
        $errors = Validator::purchase($input, (int) $product['quantity']);
        if ($errors) {
            JsonResponse::send(['errors' => $errors], 422);
        }

        try {
            $transactionId = $this->purchaseService->purchase((int) $id, (int) $user['id'], (int) $input['quantity']);
            JsonResponse::send(['transaction_id' => $transactionId], 201);
        } catch (RuntimeException $exception) {
            JsonResponse::send(['error' => $exception->getMessage()], 409);
        }
    }

    public function token(): never
    {
        $input = $this->jsonInput();
        if (!$this->auth->attempt((string) ($input['email'] ?? ''), (string) ($input['password'] ?? ''))) {
            JsonResponse::send(['error' => 'Invalid credentials.'], 401);
        }

        $user = $this->auth->user();
        $token = $this->jwt->encode([
            'sub' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600,
        ]);

        JsonResponse::send(['token' => $token]);
    }

    private function requireRole(string $role): array
    {
        $user = $this->requireToken();
        if (($user['role'] ?? null) !== $role) {
            JsonResponse::send(['error' => 'Forbidden.'], 403);
        }

        return $user;
    }

    private function requireToken(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($header, 'Bearer ')) {
            JsonResponse::send(['error' => 'Missing bearer token.'], 401);
        }

        $payload = $this->jwt->decode(substr($header, 7));
        if (!$payload) {
            JsonResponse::send(['error' => 'Invalid token.'], 401);
        }

        return [
            'id' => (int) $payload['sub'],
            'email' => (string) $payload['email'],
            'role' => (string) $payload['role'],
        ];
    }

    private function jsonInput(): array
    {
        $json = file_get_contents('php://input') ?: '{}';
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }
}
