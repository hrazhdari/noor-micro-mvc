<?php
declare(strict_types=1);
namespace Noor\Core;

final class Config
{
    public static function load(string $file): array
    {
        if (!is_file($file)) { throw new \RuntimeException('Configuration file not found.'); }
        $data = require $file;
        if (!is_array($data)) { throw new \RuntimeException('Configuration must return an array.'); }
        return $data;
    }
}
