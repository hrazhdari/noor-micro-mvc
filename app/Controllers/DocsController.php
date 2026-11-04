<?php
declare(strict_types=1);
namespace App\Controllers;
use Noor\Core\Controller;
use Noor\Http\Request;
use Noor\Http\Response;

final class DocsController extends Controller
{
    public const PAGES = [
        'overview' => 'معرفی و نقشه مستندات',
        'quick-start' => 'نصب و شروع سریع',
        'architecture' => 'معماری و ساختار پوشه‌ها',
        'routing' => 'Router و پارامترهای URL',
        'http' => 'Request و Response',
        'mvc' => 'ساخت Controller و View',
        'middleware' => 'Middleware و جریان اجرا',
        'database' => 'اتصال PDO و تراکنش‌ها',
        'security' => 'امنیت و اعتبارسنجی',
        'api' => 'مرجع کلاس‌ها و API',
        'testing' => 'تست، عیب‌یابی و محدودیت‌ها',
        'deployment' => 'استقرار در محیط عملیاتی',
        'contributing' => 'تغییر کد و مشارکت در GitHub',
    ];

    public function index(Request $request): Response
    {
        return $this->page($request, 'overview');
    }

    public function page(Request $request, string $slug): Response
    {
        if (!array_key_exists($slug, self::PAGES)) {
            return Response::text('404 Not Found', 404);
        }
        return $this->view('docs/layout', [
            'title' => self::PAGES[$slug],
            'slug' => $slug,
            'pages' => self::PAGES,
            'baseUrl' => $request->url(),
        ]);
    }
}
