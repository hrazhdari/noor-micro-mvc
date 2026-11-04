<?php
declare(strict_types=1);
namespace Noor\Security;
final class Csrf
{
    public static function token(): string
    {
        self::requireSession();
        if (!isset($_SESSION['noor_csrf'])) { $_SESSION['noor_csrf'] = bin2hex(random_bytes(32)); }
        return $_SESSION['noor_csrf'];
    }
    public static function verify(mixed $candidate): bool
    {
        self::requireSession();
        return is_string($candidate) && isset($_SESSION['noor_csrf']) && hash_equals($_SESSION['noor_csrf'], $candidate);
    }
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . \Noor\Core\View::escape(self::token()) . '">';
    }
    private static function requireSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { throw new \RuntimeException('Session must be started before CSRF.'); }
    }
}
