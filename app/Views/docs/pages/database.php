<h2>دیتابیس، PDO و Transaction</h2><p>Noor از PDO استفاده می‌کند و ORM اختصاصی تحمیل نمی‌کند. این انتخاب امکان نوشتن Query روشن و کنترل‌شده را فراهم می‌کند، اما توسعه‌دهنده مسئول طراحی SQL و Indexهاست.</p><h3>تنظیم اتصال</h3><p>از <code>config/database.example.php</code> یک کپی به نام <code>config/database.php</code> بساز. رمز اتصال را در Git منتشر نکن.</p><pre><code>$settings = require dirname(__DIR__) . '/config/database.php';
$pdo = \Noor\Database\Database::connect($settings);</code></pre><h3>Query پارامتری</h3><pre><code>$statement = $pdo->prepare(
    'SELECT id, title FROM articles WHERE id = :id'
);
$statement->execute(['id' => $id]);
$article = $statement->fetch();</code></pre><h3>Transaction</h3><pre><code>\Noor\Database\Database::transaction($pdo, static function ($pdo): void {
    $pdo->prepare('INSERT INTO logs (message) VALUES (?)')
        ->execute(['Saved']);
});</code></pre><p>اگر در داخل Transaction خطایی رخ دهد، Wrapper تلاش می‌کند Rollback کند و خطا را دوباره به برنامه برگرداند. قواعد کاری هر برنامه، مانند کنترل ثبت سفارش یا تغییر وضعیت یک فرایند، باید در Service همان برنامه نوشته و با تراکنش مناسب تست شوند.</p><div class="warn">تست یکپارچه PDO/MySQL هنوز باید روی هاست هدف یا XAMPP انجام شود. جزئیات دیتابیس در فایل <code>docs/DATABASE.md</code> نیز وجود دارد.</div>