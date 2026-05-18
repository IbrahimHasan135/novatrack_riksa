<?php

namespace Core;

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $url): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = str_replace('/', '\/', $pattern);

            if (preg_match('/^' . $pattern . '$/', $url, $matches)) {
                array_shift($matches);
                call_user_func_array($handler, $matches);
                return;
            }

            if ($route === $url) {
                call_user_func($handler);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Halaman tidak ditemukan';
    }
}
