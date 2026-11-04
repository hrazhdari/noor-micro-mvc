<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap.php';
use Noor\Core\Application;
use Noor\Core\Config;
use Noor\Http\Request;
use Noor\Security\Session;

$root = dirname(__DIR__);
$configFile = is_file($root . '/config/app.php') ? $root . '/config/app.php' : $root . '/config/app.example.php';
$config = Config::load($configFile);
date_default_timezone_set($config['timezone'] ?? 'UTC');
$request = Request::fromGlobals();
$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
Session::start($secure, $request->basePath === '' ? '/' : $request->basePath . '/');
$router = require $root . '/config/routes.php';
$app = new Application($router, (bool) ($config['debug'] ?? false), $root . '/storage/logs/noor.log');
$response = $app->handle($request);
$response->header('X-Content-Type-Options', 'nosniff')
         ->header('X-Frame-Options', 'SAMEORIGIN')
         ->header('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->send($request->method === 'HEAD');
