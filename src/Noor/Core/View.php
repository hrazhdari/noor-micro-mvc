<?php
declare(strict_types=1);
namespace Noor\Core;
use Noor\Http\Response;
final class View
{
    public function __construct(private readonly string $directory) {}
    public static function escape(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public function render(string $name, array $data = []): Response
    {
        if (!preg_match('~^[a-zA-Z0-9_/-]+$~D', $name) || str_contains($name, '..')) { throw new \InvalidArgumentException('Invalid view name.'); }
        $root = realpath($this->directory);
        $path = realpath($this->directory . '/' . $name . '.php');
        if ($root === false || $path === false || !str_starts_with($path, $root . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException('View not found.');
        }
        ob_start();
        try {
            (static function (string $__file, array $__values): void {
                extract($__values, EXTR_SKIP);
                require $__file;
            })($path, $data);
            $html = (string) ob_get_clean();
        } catch (\Throwable $e) { ob_end_clean(); throw $e; }
        return Response::html($html);
    }
}
