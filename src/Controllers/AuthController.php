<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Support\RedirectResponse;
use App\Support\View;

final class AuthController
{
    public function __construct(private readonly Auth $auth)
    {
    }

    public function loginForm(): string
    {
        return View::render('auth/login', [
            'error' => null,
            'authUser' => $this->auth->user(),
        ]);
    }

    public function login(): string|RedirectResponse
    {
        if ($this->auth->attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            return new RedirectResponse('/products');
        }

        return View::render('auth/login', [
            'error' => 'Invalid email or password.',
            'authUser' => $this->auth->user(),
        ]);
    }

    public function logout(): RedirectResponse
    {
        $this->auth->logout();

        return new RedirectResponse('/login');
    }
}
