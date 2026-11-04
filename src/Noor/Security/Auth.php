<?php
declare(strict_types=1);
namespace Noor\Security;
final class Auth
{
    public static function login(int $userId): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { throw new \RuntimeException('Session required.'); }
        session_regenerate_id(true);
        $_SESSION['noor_user_id'] = $userId;
    }
    public static function id(): ?int { return isset($_SESSION['noor_user_id']) ? (int) $_SESSION['noor_user_id'] : null; }
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { throw new \RuntimeException('Session required.'); }
        unset($_SESSION['noor_user_id']);
        session_regenerate_id(true);
    }
    public static function hashPassword(string $password): string { return password_hash($password, PASSWORD_DEFAULT); }
    public static function verifyPassword(string $password, string $hash): bool { return password_verify($password, $hash); }
}
