<?php

declare(strict_types=1);

namespace App\Auth;

class Guard
{
    public function __construct(private readonly Auth $auth)
    {
    }

    public function requireLogin(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }
    }

    public function requireAdmin(): void
    {
        $this->requireLogin();
        if (!$this->auth->isAdmin()) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }
}
