<h2>معماری Noor</h2><p>در Noor همه درخواست‌ها از یک نقطه مشترک وارد می‌شوند و بعد به Router می‌رسند. به این الگو Front Controller می‌گوییم. مزیتش این است که Session، مدیریت خطا، بارگذاری کلاس‌ها و تنظیمات مشترک در یک نقطه کنترل می‌شوند.</p><pre><code>Browser
   |
   v
public/index.php
   |  bootstrap.php (autoload)
   v
Request -> Router -> Middleware -> Controller
                                  |
                                  v
                            Model / Service
                                  |
                                  v
                              PDO / DB
                                  |
                                  v
                              View -> Response</code></pre><h3>مسئولیت فایل‌ها</h3><table><tr><th>فایل یا پوشه</th><th>مسئولیت</th></tr><tr><td><code>bootstrap.php</code></td><td>بارگذاری خودکار کلاس‌های Noor و App</td></tr><tr><td><code>public/index.php</code></td><td>ساخت Request و Application و ارسال Response</td></tr><tr><td><code>config/routes.php</code></td><td>تعریف آدرس‌ها و Handlerها</td></tr><tr><td><code>src/Noor/</code></td><td>هسته مستقل از برنامه</td></tr><tr><td><code>app/Controllers/</code></td><td>کد اختصاصی وب‌اپلیکیشن</td></tr><tr><td><code>app/Views/</code></td><td>قالب‌های PHP</td></tr><tr><td><code>storage/</code></td><td>فایل‌های خصوصی زمان اجرا</td></tr></table><h3>چرا هسته و برنامه جدا هستند؟</h3><p>چون توسعه‌دهنده باید بتواند Noor را برای پروژه‌های دیگر استفاده کند و تغییرات اختصاصی هر پروژه باعث وابستگی هسته نشوند. کلاس‌های عمومی داخل namespace <code>Noor\</code> و کلاس‌های برنامه در <code>App\</code> قرار می‌گیرند.</p><h3>چرا Autoload اختصاصی داریم؟</h3><p>برای این نسخه نمی‌خواهیم Composer شرط اجرای برنامه باشد. تابع استاندارد <code>spl_autoload_register()</code> نام کامل کلاس را به مسیر فایل ترجمه می‌کند. محدودیت آن این است که مدیریت بسته‌های خارجی را بر عهده نمی‌گیرد؛ اگر برنامه به پکیج‌های زیاد نیاز پیدا کند، استفاده اختیاری از Composer قابل بررسی است.</p>