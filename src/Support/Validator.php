<?php

declare(strict_types=1);

namespace App\Support;

final class Validator
{
    public static function product(array $input): array
    {
        $errors = [];
        $name = trim((string) ($input['name'] ?? ''));
        $price = $input['price'] ?? null;
        $quantity = $input['quantity'] ?? null;

        if ($name === '') {
            $errors['name'] = 'Product name is required.';
        }

        if ($price === null || $price === '' || !is_numeric($price) || (float) $price <= 0) {
            $errors['price'] = 'Price must be a positive number.';
        }

        if ($quantity === null || $quantity === '' || filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 0) {
            $errors['quantity'] = 'Quantity must be a non-negative whole number.';
        }

        return $errors;
    }

    public static function purchase(array $input, int $availableQuantity): array
    {
        $errors = [];
        $quantity = $input['quantity'] ?? null;

        if ($quantity === null || filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity <= 0) {
            $errors['quantity'] = 'Purchase quantity must be at least 1.';
        } elseif ((int) $quantity > $availableQuantity) {
            $errors['quantity'] = 'Purchase quantity cannot exceed available stock.';
        }

        return $errors;
    }
}
