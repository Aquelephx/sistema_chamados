<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Core\Auth;

class Router
{
    private array $routes = [];

    public function get(string $path, array $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $action, $middlewares);
    }

    public function post(string $path, array $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $action, $middlewares);
    }

    private function addRoute(string $method, string $path, array $action, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request): mixed
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $request->uri(), $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // 1. Executa os middlewares antes do controller
                foreach ($route['middlewares'] as $middleware) {
                    if ($middleware === 'auth') {
                        (new AuthMiddleware())->handle();
                    }
                }

                // 2. Executa o controller
                [$controllerClass, $method] = $route['action'];
                $controller = new $controllerClass();
                
                return $controller->$method($request, ...array_values($params));
            }
        }

        // PÁGINA 404 AMIGÁVEL
        http_response_code(404);
        
        // Verifica se o usuário está logado para escolher o layout correto
        if (Auth::check()) {
            return View::render('errors.404', [], 'layouts.main');
        }
        
        return View::render('errors.404', [], 'layouts.auth');
    }
}