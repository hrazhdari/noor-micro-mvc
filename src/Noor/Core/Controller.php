<?php
declare(strict_types=1);
namespace Noor\Core;
use Noor\Http\Response;

abstract class Controller
{
    protected function view(string $name, array $data = []): Response
    {
        return (new View(dirname(__DIR__, 3) . '/app/Views'))->render($name, $data);
    }
    protected function redirect(string $path): Response { return Response::redirect($path); }
}
