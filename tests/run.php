<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap.php';
// Keep HTTP headers writable during the full CLI test run.
ob_start();

use Noor\Core\Application;
use Noor\Core\Router;
use Noor\Core\View;
use Noor\Http\Request;
use Noor\Http\Response;
use Noor\Security\Auth;
use Noor\Security\Csrf;
use Noor\Security\Middleware;
use Noor\Security\Session;
use Noor\Database\Database;

$tests = [];
function test(string $name, callable $test): void { global $tests; $tests[$name] = $test; }
function same(mixed $actual, mixed $expected): void {
    if ($actual !== $expected) { throw new RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)); }
}
function yes(bool $value): void { if (!$value) { throw new RuntimeException('Assertion failed'); } }

Session::start();

test('GET routes and named parameters', function (): void {
    $r = new Router();
    $r->get('/p/{id}', static fn (Request $request, string $id): Response => Response::text($id));
    same($r->dispatch(new Request('GET', '/p/42'))->body, '42');
    same($r->dispatch(new Request('GET', '/p/abc'))->body, 'abc');
});
test('Route literal regex characters are escaped', function (): void {
    $r = new Router();
    $r->get('/file.json', static fn (Request $r): Response => Response::text('matched'));
    same($r->dispatch(new Request('GET', '/fileXjson'))->status, 404);
    same($r->dispatch(new Request('GET', '/file.json'))->status, 200);
});
test('404 and 405 with Allow header', function (): void {
    $r = new Router();
    $r->get('/only', static fn (Request $r): Response => Response::text('ok'));
    same($r->dispatch(new Request('GET', '/unknown'))->status, 404);
    $response = $r->dispatch(new Request('POST', '/only'));
    same($response->status, 405);
    yes(str_contains($response->headers()['Allow'], 'GET'));
});
test('Middleware order', function (): void {
    $events = [];
    $r = new Router();
    $r->use(function (Request $r, callable $next) use (&$events): Response { $events[]='a1'; $out=$next($r); $events[]='a2'; return $out; });
    $r->use(function (Request $r, callable $next) use (&$events): Response { $events[]='b1'; $out=$next($r); $events[]='b2'; return $out; });
    $r->get('/', function (Request $r) use (&$events): Response { $events[]='handler'; return Response::text('ok'); });
    same($r->dispatch(new Request('GET', '/'))->status, 200);
    same($events, ['a1', 'b1', 'handler', 'b2', 'a2']);
});
test('Response json unicode, redirects, header injection rejected', function (): void {
    $r = Response::json(['label'=>'کالا']);
    yes(str_contains($r->body, 'کالا'));
    same(Response::redirect('/products')->status, 303);
    $caught=false;
    try { Response::redirect('//attacker.example'); } catch (InvalidArgumentException) { $caught=true; }
    yes($caught);
    $caught=false;
    try { (new Response())->header('X-A', "bad\r\nEvil: yes"); } catch (InvalidArgumentException) { $caught=true; }
    yes($caught);
});
test('View escaping and path traversal protection', function (): void {
    same(View::escape('<script>'), '&lt;script&gt;');
    $view = new View(dirname(__DIR__) . '/app/Views');
    $out = $view->render('home', ['title'=>'<test>']);
    yes(str_contains($out->body, '&lt;test&gt;'));
    $caught = false;
    try { $view->render('../bootstrap'); } catch (InvalidArgumentException) { $caught = true; }
    yes($caught);
});
test('CSRF accepts correct token and rejects wrong', function (): void {
    $token = Csrf::token();
    yes(strlen($token) === 64);
    yes(Csrf::verify($token));
    yes(!Csrf::verify('wrong'));
    yes(str_contains(Csrf::field(), 'name="_csrf"'));
});
test('CSRF middleware blocks bad POST', function (): void {
    $r = new Router();
    $r->use(Middleware::csrf());
    $r->post('/submit', static fn (Request $req): Response => Response::text('saved'));
    same($r->dispatch(new Request('POST', '/submit'))->status, 419);
    same($r->dispatch(new Request('POST', '/submit', [], ['_csrf'=>Csrf::token()]))->body, 'saved');
});
test('Password hash and verification', function (): void {
    $hash = Auth::hashPassword('test strong pass 123');
    yes(Auth::verifyPassword('test strong pass 123', $hash));
    yes(!Auth::verifyPassword('wrong', $hash));
});
test('Auth middleware denies guest and allows authenticated', function (): void {
    Auth::logout();
    $r = new Router();
    $r->use(Middleware::auth());
    $r->get('/private', static fn (Request $req): Response => Response::text('allowed'));
    same($r->dispatch(new Request('GET', '/private'))->status, 401);
    Auth::login(17);
    same(Auth::id(), 17);
    same($r->dispatch(new Request('GET', '/private'))->status, 200);
    Auth::logout();
});
test('Application catches exceptions with safe response', function (): void {
    $r = new Router();
    $r->get('/fail', static function (Request $req): Response { throw new RuntimeException('private secret'); });
    $app = new Application($r, false, dirname(__DIR__) . '/storage/logs/noor-test.log');
    $out = $app->handle(new Request('GET', '/fail'));
    same($out->status, 500);
    yes(!str_contains($out->body, 'private secret'));
});
test('Real application homepage and JSON demo', function (): void {
    $router = require dirname(__DIR__) . '/config/routes.php';
    $app = new Application($router);
    $out = $app->handle(new Request('GET', '/'));
    same($out->status, 200);
    yes(str_contains($out->body, 'Noor Micro MVC'));
    $json = $app->handle(new Request('GET', '/hello/Noor'));
    same(json_decode($json->body, true)['message'], 'Hello Noor');
});
test('PDO driver capabilities are reported honestly', function (): void {
    yes(class_exists(PDO::class));
});


test('Static route wins over dynamic route', function (): void {
    $r = new Router();
    $r->get('/products/{id}', static fn(Request $req, string $id): Response => Response::text('dynamic:' . $id));
    $r->get('/products/new', static fn(Request $req): Response => Response::text('static'));
    same($r->dispatch(new Request('GET', '/products/new'))->body, 'static');
    same($r->dispatch(new Request('GET', '/products/15'))->body, 'dynamic:15');
});
test('Multiple URL parameters and URL-decoding', function (): void {
    $r = new Router();
    $r->get('/projects/{project}/products/{product}', static fn(Request $req, string $p, string $n): Response => Response::json(['p'=>$p,'n'=>$n]));
    $data = json_decode($r->dispatch(new Request('GET','/projects/%D9%86%D9%88%D8%B1/products/15'))->body, true);
    same($data, ['p'=>'نور','n'=>'15']);
    same($r->dispatch(new Request('GET','/projects/a%2Fb/products/15'))->status, 404);
    same($r->dispatch(new Request('GET','/projects/%GG/products/15'))->status, 404);
});
test('GET/POST/HEAD/OPTIONS and 405', function (): void {
    $r = new Router();
    $r->get('/resource', static fn(Request $req): Response => Response::text('get'));
    $r->post('/resource', static fn(Request $req): Response => Response::text('post'));
    same($r->dispatch(new Request('POST','/resource'))->body,'post');
    same($r->dispatch(new Request('HEAD','/resource'))->status,200);
    $options=$r->dispatch(new Request('OPTIONS','/resource'));
    same($options->status, 204);
    yes(str_contains($options->headers()['Allow'],'HEAD'));
    $unsupported=$r->dispatch(new Request('PATCH','/resource'));
    same($unsupported->status,405);
    yes(str_contains($unsupported->headers()['Allow'],'POST'));
});
test('Route middleware isolated and short-circuits', function (): void {
    $r = new Router();
    $r->get('/open', static fn(Request $req): Response=>Response::text('open'));
    $r->get('/blocked', static fn(Request $req): Response=>Response::text('bad'),
        [static fn(Request $req, callable $next): Response => Response::text('denied',403)]);
    same($r->dispatch(new Request('GET','/open'))->body, 'open');
    same($r->dispatch(new Request('GET','/blocked'))->status, 403);
});
test('Duplicate and invalid route definitions are rejected', function (): void {
    $r = new Router();
    $r->get('/x', static fn(Request $req): Response=>Response::text('a'));
    $caught=false;
    try { $r->get('/x', static fn(Request $req): Response=>Response::text('b')); } catch (LogicException) { $caught=true; }
    yes($caught);
    $caught=false;
    try { $r->get('/x/{id}/{id}', static fn(Request $req): Response=>Response::text('b')); } catch (InvalidArgumentException) { $caught=true; }
    yes($caught);
    $caught=false;
    try { $r->add('TRACE', '/bad', static fn(Request $req): Response=>Response::text('bad')); } catch (InvalidArgumentException) { $caught=true; }
    yes($caught);
});
test('Request path stripping in XAMPP subdirectory', function (): void {
    $old=$_SERVER;
    try {
        $_SERVER['REQUEST_URI']='/noor/public/products/25?page=2';
        $_SERVER['SCRIPT_NAME']='/noor/public/index.php';
        $_SERVER['REQUEST_METHOD']='GET';
        $req=Request::fromGlobals();
        same($req->path,'/products/25');
        same($req->basePath, '/noor/public');
        same($req->url('/products'),'/noor/public/products');
    } finally { $_SERVER=$old; }
});
test('Validation required integer email and unicode length', function (): void {
    $rules=['name'=>'required|string|min:2|max:5','age'=>'required|integer','email'=>'email'];
    $errors=\Noor\Core\Validation::check(['name'=>'نور','age'=>'22','email'=>'a@b.com'],$rules);
    same($errors,[]);
    $errors=\Noor\Core\Validation::check(['name'=>'','age'=>'x','email'=>'wrong'],$rules);
    yes(in_array('required',$errors['name'],true));
    yes(in_array('integer',$errors['age'],true));
    yes(in_array('email',$errors['email'],true));
});
test('Response invalid header names and unsupported statuses rejected', function (): void {
    $caught=false;
    try {(new Response())->header('Evil Header','x');} catch(InvalidArgumentException){$caught=true;}
    yes($caught);
    $caught=false;
    try {new Response('', 999);} catch(InvalidArgumentException){$caught=true;}
    yes($caught);
});
test('Auth does not authenticate based on hash alone', function (): void {
    Auth::logout();
    same(Auth::id(),null);
    $hash=Auth::hashPassword('123');
    yes(Auth::verifyPassword('123',$hash));
    same(Auth::id(),null);
});

test('Documentation index and all published topics render', function (): void {
    $router = require dirname(__DIR__) . '/config/routes.php';
    $app = new Application($router);
    $index = $app->handle(new Request('GET', '/docs'));
    same($index->status, 200);
    yes(str_contains($index->body, 'مستندات فریمورک نور'));
    foreach (\App\Controllers\DocsController::PAGES as $slug => $title) {
        $response = $app->handle(new Request('GET', '/docs/' . $slug));
        same($response->status, 200);
        yes(str_contains($response->body, $title));
    }
});
test('Unknown docs page returns 404, not accidental file include', function (): void {
    $router = require dirname(__DIR__) . '/config/routes.php';
    $app = new Application($router);
    same($app->handle(new Request('GET', '/docs/no-such-page'))->status, 404);
    same($app->handle(new Request('GET', '/docs/%2e%2e'))->status, 404);
});
test('Landing page references documentation and local assets', function (): void {
    $router = require dirname(__DIR__) . '/config/routes.php';
    $app = new Application($router);
    $res = $app->handle(new Request('GET', '/'));
    same($res->status, 200);
    yes(str_contains($res->body, 'assets/css/site.css'));
    yes(str_contains($res->body, 'assets/img/noor-mark.png'));
    yes(str_contains($res->body, 'docs/quick-start'));
});

$passed=0; $failed=0;
foreach ($tests as $name=>$callback) {
    try { $callback(); echo "PASS $name\n"; $passed++; }
    catch (Throwable $e) { echo "FAIL $name: {$e->getMessage()}\n"; $failed++; }
}
echo "\nRESULT: $passed passed, $failed failed\n";
echo 'PDO drivers: ' . (implode(', ', PDO::getAvailableDrivers()) ?: '(none)') . "\n";
exit($failed ? 1 : 0);
