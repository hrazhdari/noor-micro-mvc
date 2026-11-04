<?php
declare(strict_types=1);
namespace Noor\Http;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $headers = [],
        public readonly array $files = [],
        public readonly string $basePath = ''
    ) {}

    public static function fromGlobals(): self
    {
        $rawUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $uriPath = parse_url($rawUri, PHP_URL_PATH);
        $path = is_string($uriPath) && $uriPath !== '' ? $uriPath : '/';
        // Works in a subdirectory (e.g. /noor/public) or at domain root.
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/.');
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_') && is_string($value)) {
                $headers[strtolower(str_replace('_', '-', substr($key, 5)))] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) { $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE']; }
        $body = $_POST;
        $type = strtolower(trim(explode(';', $headers['content-type'] ?? '')[0]));
        if ($type === 'application/json') {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw === false ? '' : $raw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($json) || array_is_list($json)) {
                $body = [];
            } else { $body = $json; }
        }
        return new self(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')), $path, $_GET, $body, $headers, $_FILES, $base);
    }
    public function query(string $key, mixed $default = null): mixed { return $this->query[$key] ?? $default; }
    public function input(string $key, mixed $default = null): mixed { return $this->body[$key] ?? $default; }
    public function header(string $key, ?string $default = null): ?string { return $this->headers[strtolower($key)] ?? $default; }
    public function all(): array { return $this->body; }
    public function url(string $path = '/'): string { return $this->basePath . '/' . ltrim($path, '/'); }
}
