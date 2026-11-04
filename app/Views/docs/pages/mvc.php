<h2>ساخت Controller و View</h2><p>Controller درخواست را می‌گیرد، داده آماده می‌کند و خروجی مناسب را برمی‌گرداند. View فقط مسئول تولید HTML است؛ قواعد کاری مانند کنترل مانده انبار در View قرار نمی‌گیرند.</p><h3>Controller نمونه</h3><pre><code>&lt;?php
namespace App\Controllers;

use Noor\Core\Controller;
use Noor\Http\Request;
use Noor\Http\Response;

final class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('products/index', [
            'title' => 'فهرست کالاها',
            'products' => [['name' => 'Pipe']]
        ]);
    }
}</code></pre><h3>View نمونه</h3><p>فایل <code>app/Views/products/index.php</code> را ایجاد کن:</p><pre><code>&lt;h1&gt;&lt;?= \Noor\Core\View::escape($title) ?&gt;&lt;/h1&gt;
&lt;?php foreach ($products as $product): ?&gt;
  &lt;p&gt;&lt;?= \Noor\Core\View::escape($product['name']) ?&gt;&lt;/p&gt;
&lt;?php endforeach; ?&gt;</code></pre><h3>ثبت Controller در Router</h3><pre><code>$router->get('/products', [\App\Controllers\ProductController::class, 'index']);</code></pre><p>در این نسخه Model کلاس اجباری فریمورک ندارد. می‌توانی کلاس مدل معمولی بسازی که PDO را دریافت کند و Queryها را اجرا کند. برای عملیات چندمرحله‌ای، یک Service در پوشه برنامه تعریف کن.</p>