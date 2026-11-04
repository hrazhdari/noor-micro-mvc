<?php
declare(strict_types=1);
// PHP built-in server router for local development only.
$path = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$file = __DIR__ . '/' . ltrim((string)$path, '/');
if ($path !== '/' && is_file($file)) { return false; }
require __DIR__ . '/index.php';
