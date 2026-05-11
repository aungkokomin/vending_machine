<?php

declare(strict_types=1);

namespace App\Auth;

use App\Repositories\UserRepositoryInterface;

class Auth
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        return true;
    }

    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function id(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function isAdmin(): bool
    {
        return ($_SESSION['user']['role'] ?? null) === 'Admin';
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
    }
}
