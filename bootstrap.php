<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    foreach (['Noor\\' => __DIR__ . '/src/Noor/', 'App\\' => __DIR__ . '/app/'] as $prefix => $base) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        if (!preg_match('/^[A-Za-z0-9_\\\\]+$/D', $relative)) {
            return;
        }
        $file = $base . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
        return;
    }
});
