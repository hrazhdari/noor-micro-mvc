<h2>Router و مسیرهای پویا</h2><p>Router در Noor مسیر درخواست را با Routeهای تعریف‌شده مقایسه می‌کند. اگر هم Route ثابت و هم Route پارامتری داشته باشی، اول مسیر ثابت بررسی می‌شود تا نتیجه قابل پیش‌بینی بماند. همچنین هر Route فقط با متد HTTP خودش اجرا می‌شود.</p><pre><code>use Noor\Core\Router;
use Noor\Http\Request;
use Noor\Http\Response;

$router = new Router();

$router->get('/products', static fn(Request $r): Response =>
    Response::text('Product list')
);

$router->get('/products/{id}', static fn(Request $r, string $id): Response =>
    Response::json(['id' => $id])
);

$router->post('/products', static fn(Request $r): Response =>
    Response::json(['received' => $r->all()], 201)
);</code></pre><h3>چرا مسیر پارامتری داریم؟</h3><p>به‌جای تعریف Route برای هر کالا، الگوی <code>/products/{id}</code> مقدار شناسه را از URL استخراج می‌کند. مقدار دریافت‌شده همچنان داده ورودی کاربر است و قبل از اجرای Query باید بررسی شود.</p><h3>کدهای پاسخ</h3><table><tr><th>کد</th><th>رفتار</th></tr><tr><td>404</td><td>هیچ الگویی برای مسیر وجود ندارد</td></tr><tr><td>405</td><td>مسیر هست ولی متد مجاز نیست؛ هدر Allow فرستاده می‌شود</td></tr><tr><td>204</td><td>پاسخ خودکار OPTIONS برای مسیر ثبت‌شده</td></tr></table><p>متدهای <code>get</code>، <code>post</code>، <code>put</code>، <code>patch</code> و <code>delete</code> موجودند. Routeهای HEAD می‌توانند از GET استفاده کنند.</p><div class="note">اگر دو مسیر پویا الگوی یکسانی داشته باشند، باید از تعریف مبهم جلوگیری کنی. در کاربردهای واقعی، مسیرهای واضح و غیرمتداخل انتخاب کن.</div>