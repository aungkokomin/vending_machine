<?php

declare(strict_types=1);

namespace App\Support;

final class RedirectResponse
{
    public function __construct(public readonly string $url)
    {
    }

    public function send(): never
    {
        header('Location: ' . $this->url);
        exit;
    }
}
