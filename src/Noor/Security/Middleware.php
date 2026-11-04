<?php
declare(strict_types=1);
namespace Noor\Security;
use Noor\Http\Request;
use Noor\Http\Response;
final class Middleware
{
    public static function csrf(): callable
    {
        return static function (Request $request, callable $next): Response {
            if (in_array($request->method, ['POST','PUT','PATCH','DELETE'], true) &&
                !Csrf::verify($request->input('_csrf') ?? $request->header('x-csrf-token'))) {
                return Response::text('419 Invalid CSRF token', 419);
            }
            return $next($request);
        };
    }
    public static function auth(): callable
    {
        return static fn(Request $request, callable $next): Response => Auth::id() === null
            ? Response::text('401 Unauthorized', 401) : $next($request);
    }
}
