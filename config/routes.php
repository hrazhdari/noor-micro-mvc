<?php
declare(strict_types=1);
use App\Controllers\HomeController;
use App\Controllers\DocsController;
use Noor\Core\Router;
use Noor\Security\Middleware;
return (static function (): Router {
    $router = new Router();
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/hello/{name}', [HomeController::class, 'greet']);
    $router->get('/docs', [DocsController::class, 'index']);
    $router->get('/docs/{slug}', [DocsController::class, 'page']);
    $router->get('/health', static fn(\Noor\Http\Request $r): \Noor\Http\Response => \Noor\Http\Response::json(['status' => 'ok']));
    $router->get('/form', [HomeController::class, 'form']);
    $router->post('/form', [HomeController::class, 'submit'], [Middleware::csrf()]);
    // Example: protect a specific route. Session must have started in public/index.php.
    // $router->get('/private', [HomeController::class, 'privatePage'], [Middleware::auth()]);
    return $router;
})();
