<?php

declare(strict_types=1);

namespace App\Support;

final class JwtService
{
    public function __construct(private readonly string $secret)
    {
    }

    public function encode(array $payload): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];
        $signature = hash_hmac('sha256', implode('.', $segments), $this->secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public function decode(string $token): ?array
    {
        $segments = explode('.', $token);
        if (count($segments) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $segments;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $this->secret, true));
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $data = json_decode($this->base64UrlDecode($payload), true);
        if (!is_array($data) || (($data['exp'] ?? PHP_INT_MAX) < time())) {
            return null;
        }

        return $data;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
