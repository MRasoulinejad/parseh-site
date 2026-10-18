<?php
declare(strict_types=1);

$GLOBALS['__routes'] = [];

function route(string $method, string $pattern, callable $handler): void
{
    $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', rtrim($pattern, '/') ?: '/') . '$#u';
    $GLOBALS['__routes'][] = [strtoupper($method), $regex, $handler];
}

function get(string $p, callable $h): void { route('GET', $p, $h); }
function post(string $p, callable $h): void { route('POST', $p, $h); }

function dispatch(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $path = rtrim($path, '/') ?: '/';

    if ($method === 'POST') {
        verify_csrf();
    }

    $allowed = false;
    foreach ($GLOBALS['__routes'] as [$m, $regex, $handler]) {
        if (!preg_match($regex, $path, $matches)) {
            continue;
        }
        if ($m !== $method) {
            $allowed = true;
            continue;
        }
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        $out = $handler(...array_values($params));
        if (is_string($out)) {
            echo $out;
        }
        return;
    }
    abort($allowed ? 405 : 404);
}
