<?php
declare(strict_types=1);
namespace Noor\Core;

use Noor\Http\Request;
use Noor\Http\Response;

/** No dependency on Composer. Literal routes take priority over parameter routes. */
final class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function use(callable $middleware): self { $this->middleware[] = $middleware; return $this; }
    public function get(string $path, callable|array $action, array $middleware = []): self { return $this->add('GET', $path, $action, $middleware); }
    public function post(string $path, callable|array $action, array $middleware = []): self { return $this->add('POST', $path, $action, $middleware); }
    public function put(string $path, callable|array $action, array $middleware = []): self { return $this->add('PUT', $path, $action, $middleware); }
    public function patch(string $path, callable|array $action, array $middleware = []): self { return $this->add('PATCH', $path, $action, $middleware); }
    public function delete(string $path, callable|array $action, array $middleware = []): self { return $this->add('DELETE', $path, $action, $middleware); }

    public function add(string $method, string $path, callable|array $action, array $middleware = []): self
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET','POST','PUT','PATCH','DELETE','HEAD','OPTIONS'], true)) {
            throw new \InvalidArgumentException('Unsupported HTTP method.');
        }
        if ($path === '' || $path[0] !== '/' || str_contains($path, '?') || str_contains($path, '#')) {
            throw new \InvalidArgumentException('Route must start with / and exclude query/fragment.');
        }
        foreach ($middleware as $mw) { if (!is_callable($mw)) { throw new \InvalidArgumentException('Middleware must be callable.'); } }
        $path = self::normalizePath($path);
        $segments = $path === '/' ? [] : explode('/', trim($path, '/'));
        $names = []; $parts = []; $dynamic = false;
        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/D', $segment, $m)) {
                if (in_array($m[1], $names, true)) { throw new \InvalidArgumentException('Duplicate route parameter.'); }
                $names[] = $m[1]; $parts[] = '([^/]+)'; $dynamic = true;
            } elseif (str_contains($segment, '{') || str_contains($segment, '}')) {
                throw new \InvalidArgumentException('Parameters must occupy a full path segment.');
            } else { $parts[] = preg_quote($segment, '~'); }
        }
        $regex = '~^/' . implode('/', $parts) . '$~Du';
        foreach ($this->routes as $existing) {
            if ($existing['method'] === $method && $existing['regex'] === $regex) {
                throw new \LogicException('Duplicate route for ' . $method . ' ' . $path);
            }
        }
        $this->routes[] = compact('method', 'path', 'regex', 'names', 'action', 'middleware', 'dynamic');
        return $this;
    }

    public static function normalizePath(string $path): string
    {
        if ($path === '') { return '/'; }
        return '/' . trim($path, '/');
    }

    public function dispatch(Request $request): Response
    {
        $method = strtoupper($request->method);
        $path = self::normalizePath($request->path);
        $matching = [];
        foreach ($this->routes as $route) {
            $matches = [];
            if (preg_match($route['regex'], $path, $matches) !== 1) { continue; }
            // Reject decoded slash/backslash and invalid percent encoding in dynamic segments.
            $params = [];
            $valid = true;
            foreach ($route['names'] as $i => $name) {
                $value = $matches[$i + 1];
                if (preg_match('/%(?![0-9a-fA-F]{2})/', $value)) { $valid = false; break; }
                $value = rawurldecode($value);
                if (str_contains($value, '/') || str_contains($value, "\\") || !preg_match('//u', $value)) { $valid = false; break; }
                $params[] = $value;
            }
            if ($valid) { $matching[] = ['route' => $route, 'params' => $params]; }
        }
        if ($matching === []) { return Response::text('404 Not Found', 404); }
        // An exact path beats a dynamic path, regardless of registration order.
        usort($matching, static fn(array $a, array $b): int => (int)$a['route']['dynamic'] <=> (int)$b['route']['dynamic']);
        $allowed = [];
        foreach ($matching as $entry) { $allowed[] = $entry['route']['method']; }
        if (in_array('GET', $allowed, true)) { $allowed[] = 'HEAD'; }
        $allowed = array_values(array_unique($allowed));
        if ($method === 'OPTIONS' && !in_array('OPTIONS', array_column(array_column($matching, 'route'), 'method'), true)) {
            return Response::text('', 204)->header('Allow', implode(', ', $allowed));
        }
        foreach ($matching as ['route' => $route, 'params' => $params]) {
            if ($route['method'] !== $method && !($method === 'HEAD' && $route['method'] === 'GET')) { continue; }
            $action = $route['action'];
            $runner = static function (Request $req) use ($action, $params): Response {
                if (is_array($action) && count($action) === 2 && is_string($action[0])) {
                    $action = [new $action[0](), $action[1]];
                }
                $result = $action($req, ...$params);
                if (!$result instanceof Response) { throw new \LogicException('Route actions must return a Response.'); }
                return $result;
            };
            foreach (array_reverse(array_merge($this->middleware, $route['middleware'])) as $mw) {
                $next = $runner;
                $runner = static fn(Request $req): Response => $mw($req, $next);
            }
            return $runner($request);
        }
        return Response::text('405 Method Not Allowed', 405)->header('Allow', implode(', ', $allowed));
    }
}
