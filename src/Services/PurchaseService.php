<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProductRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use RuntimeException;

final class PurchaseService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly TransactionRepositoryInterface $transactions
    ) {
    }

    public function purchase(int $productId, int $userId, int $quantity): int
    {
        $product = $this->products->find($productId);
        if (!$product) {
            throw new RuntimeException('Product not found.');
        }

        if ((int) $product['quantity'] < $quantity) {
            throw new RuntimeException('Not enough stock available.');
        }

        if (!$this->products->decrementQuantity($productId, $quantity)) {
            throw new RuntimeException('Unable to update stock.');
        }

        $unitPrice = (float) $product['price'];

        return $this->transactions->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $unitPrice * $quantity,
        ]);
    }
}
