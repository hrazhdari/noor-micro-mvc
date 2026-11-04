<h2>Middleware چه مسئله‌ای را حل می‌کند؟</h2><p>در برخی درخواست‌ها باید قبل از Controller یک کنترل انجام شود؛ مثلاً ورود کاربر یا اعتبارسنجی CSRF. به‌جای تکرار این کنترل در همه Controllerها، Middleware آن را در جریان درخواست قرار می‌دهد.</p><h3>Middleware اختصاصی مسیر</h3><pre><code>use Noor\Security\Middleware;

$router->post('/form',
    [\App\Controllers\HomeController::class, 'submit'],
    [Middleware::csrf()]
);</code></pre><h3>Middleware عمومی</h3><pre><code>$router->use(static function (\Noor\Http\Request $req, callable $next): \Noor\Http\Response {
    $response = $next($req);
    return $response->header('X-App', 'Noor');
});</code></pre><p>Middleware می‌تواند درخواست را به مرحله بعد منتقل کند یا با یک Response مستقل جلوی آن را بگیرد. ترتیب اجرا به‌صورت تو در تو است؛ اولین Middleware قبل از بقیه وارد شده و پس از آن‌ها خارج می‌شود.</p><div class="warn">Middleware مربوط به Auth فقط وضعیت Session را می‌سنجد. ایجاد، بازیابی، قفل حساب، Rate Limiting و Permissionهای پروژه بر عهده برنامه کاربردی است.</div>