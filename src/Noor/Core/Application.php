<?php
declare(strict_types=1);
namespace Noor\Core;
use Noor\Http\Request;
use Noor\Http\Response;
final class Application
{
    public function __construct(private readonly Router $router, private readonly bool $debug = false, private readonly ?string $logFile = null) {}
    public function handle(Request $request): Response
    {
        try { return $this->router->dispatch($request); }
        catch (\Throwable $e) {
            $line = '[' . date(DATE_ATOM) . '] ' . (string)$e . PHP_EOL;
            if ($this->logFile !== null) { error_log($line, 3, $this->logFile); }
            else { error_log($line); }
            return Response::text($this->debug ? '500 ' . $e->getMessage() : '500 Internal Server Error', 500);
        }
    }
}
