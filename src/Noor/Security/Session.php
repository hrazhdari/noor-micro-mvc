<?php
declare(strict_types=1);
namespace Noor\Security;
final class Session
{
    public static function start(bool $secure = false, string $path = '/'): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) { return; }
        if (headers_sent()) { throw new \RuntimeException('Cannot start a session after output.'); }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_set_cookie_params(['lifetime'=>0,'path'=>$path,'secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
        if (!session_start()) { throw new \RuntimeException('Session start failed.'); }
    }
}
