<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require dirname(__DIR__, 2) . '/views/' . $template . '.php';
        $content = ob_get_clean();

        ob_start();
        require dirname(__DIR__, 2) . '/views/layout.php';
        return (string) ob_get_clean();
    }
}
