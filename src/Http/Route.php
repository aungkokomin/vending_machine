<?php

declare(strict_types=1);

namespace App\Http;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Route
{
    public function __construct(
        public readonly string $method,
        public readonly string $path
    ) {
    }
}
