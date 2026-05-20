<?php
namespace App\Core;

class Router
{
    private array  $routes   = [];
    private string $basePath = '';

    public function load(): void
    {
        $this->routes = require CONFIG_PATH . '/routes.php';

        $cfg           = require CONFIG_PATH . '/app.php';
        $appUrl        = rtrim($cfg['url'] ?? '', '/');
        $parsed        = parse_url($appUrl, PHP_URL_PATH) ?? '';
        $this->basePath = rtrim($parsed, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?? '/', '/') ?: '/';

        // Strip subdirectory base path  e.g. /tamilnews/public
        // APP_URL = http://localhost/tamilnews  → basePath = /tamilnews
        // Actual requests come as /tamilnews/public/admin/...
        // So strip both /tamilnews/public and /tamilnews
        $strips = [
            $this->basePath . '/public',
            $this->basePath,
        ];
        foreach ($strips as $strip) {
            if ($strip && str_starts_with($path, $strip)) {
                $path = substr($path, strlen($strip));
                break;
            }
        }

        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if (strtoupper($routeMethod) !== strtoupper($method)) continue;

            $pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([^/]+)', $routePath);
            $pattern = '@^' . $pattern . '$@';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $this->callHandler($handler, $matches);
                return;
            }
        }

        $this->notFound();
    }

    private function callHandler(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler);

        // Build FQCN: "admin\DashboardController" → "App\Controllers\Admin\DashboardController"
        // "frontend\HomeController"               → "App\Controllers\Frontend\HomeController"
        // "AuthController"                        → "App\Controllers\AuthController"
        $parts = explode('\\', $class);
        $parts = array_map('ucfirst', $parts);
        $fqcn  = 'App\\Controllers\\' . implode('\\', $parts);

        if (!class_exists($fqcn)) {
            error_log("Router: class not found [{$fqcn}] for handler [{$handler}]");
            $this->notFound();
            return;
        }

        $controller = new $fqcn();

        if (method_exists($controller, 'middleware')) {
            $controller->middleware();
        }

        if (!method_exists($controller, $method)) {
            error_log("Router: method [{$method}] not found in [{$fqcn}]");
            $this->notFound();
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        $f = VIEW_PATH . '/errors/404.php';
        file_exists($f) ? require $f : print('<h1>404 — Not Found</h1>');
    }
}
