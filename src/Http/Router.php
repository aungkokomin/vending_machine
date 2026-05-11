<?php

declare(strict_types=1);

namespace App\Http;

use ReflectionClass;

final class Router
{
    /** @var array<string, array<int, array{path:string, regex:string, handler:array}>> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    public function add(string $method, string $path, array $handler): void
    {
        $this->routes[strtoupper($method)][] = [
            'path' => $path,
            'regex' => $this->compilePath($path),
            'handler' => $handler,
        ];
    }

    public function registerAttributes(object $controller): void
    {
        $reflection = new ReflectionClass($controller);
        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes(Route::class) as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();
                $this->add($route->method, $route->path, [$controller, $method->getName()]);
            }
        }
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $method = strtoupper($method);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return call_user_func_array($route['handler'], $params);
            }
        }

        http_response_code(404);
        return 'Not Found';
    }

    private function compilePath(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[0-9]+)', $path);

        return '#^' . $pattern . '$#';
    }
}
