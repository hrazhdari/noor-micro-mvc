<?php
declare(strict_types=1);
namespace Noor\Http;

final class Response
{
    private array $headers = [];
    public function __construct(public string $body = '', public int $status = 200)
    {
        if ($status < 100 || $status > 599) { throw new \InvalidArgumentException('Invalid HTTP status.'); }
    }
    public function header(string $name, string $value): self
    {
        if (!preg_match('/^[A-Za-z0-9!#$%&\x27*+.^_`|~-]+$/D', $name) || preg_match('/[\r\n\x00]/', $value)) {
            throw new \InvalidArgumentException('Invalid HTTP header.');
        }
        $this->headers[$name] = $value;
        return $this;
    }
    public function headers(): array { return $this->headers; }
    public static function html(string $html, int $status = 200): self { return (new self($html, $status))->header('Content-Type','text/html; charset=UTF-8'); }
    public static function text(string $text, int $status = 200): self { return (new self($text, $status))->header('Content-Type','text/plain; charset=UTF-8'); }
    public static function json(array $data, int $status = 200): self
    {
        return (new self(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), $status))->header('Content-Type','application/json; charset=UTF-8');
    }
    public static function redirect(string $path, int $status = 303): self
    {
        if (!str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/[\r\n\x00]/', $path) || !in_array($status, [301,302,303,307,308], true)) {
            throw new \InvalidArgumentException('Redirect requires a safe local path and a redirect status.');
        }
        return (new self('', $status))->header('Location', $path);
    }
    public function send(bool $head = false): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) { header($name . ': ' . $value); }
        if (!$head && !in_array($this->status, [204,304], true)) { echo $this->body; }
    }
}
