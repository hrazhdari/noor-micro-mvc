<?php
declare(strict_types=1);
namespace Noor\Database;
use PDO;

final class Database
{
    public static function connect(array $settings): PDO
    {
        foreach (['dsn', 'username', 'password'] as $key) {
            if (!array_key_exists($key, $settings)) { throw new \InvalidArgumentException('Missing database setting: ' . $key); }
        }
        return new PDO((string) $settings['dsn'], (string) $settings['username'], (string) $settings['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    public static function transaction(PDO $db, callable $callback): mixed
    {
        $db->beginTransaction();
        try {
            $result = $callback($db);
            $db->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) { $db->rollBack(); }
            throw $e;
        }
    }
}
