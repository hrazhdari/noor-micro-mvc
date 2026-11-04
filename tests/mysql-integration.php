<?php
declare(strict_types=1);
// Run only against a disposable DEVELOPMENT MySQL schema with a dedicated user.
require dirname(__DIR__) . '/bootstrap.php';

use Noor\Database\Database;

$config = dirname(__DIR__) . '/config/database.php';
if (!is_file($config)) {
    fwrite(STDERR, "SKIP: copy config/database.example.php to config/database.php and edit DEVELOPMENT credentials.\n");
    exit(2);
}
if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    fwrite(STDERR, "SKIP: pdo_mysql is not enabled in this PHP runtime.\n");
    exit(2);
}
try {
    $db = Database::connect(require $config);
    $db->exec('CREATE TEMPORARY TABLE noor_integration_test (id INT PRIMARY KEY, value VARCHAR(20)) ENGINE=InnoDB');
    Database::transaction($db, static function (PDO $pdo): void {
        $stmt = $pdo->prepare('INSERT INTO noor_integration_test (id, value) VALUES (:id, :value)');
        $stmt->execute(['id' => 1, 'value' => 'committed']);
    });
    $count = (int) $db->query('SELECT COUNT(*) FROM noor_integration_test')->fetchColumn();
    if ($count !== 1) { throw new RuntimeException('Commit test failed.'); }
    try {
        Database::transaction($db, static function (PDO $pdo): void {
            $stmt = $pdo->prepare('INSERT INTO noor_integration_test (id, value) VALUES (:id, :value)');
            $stmt->execute(['id' => 2, 'value' => 'rolled back']);
            throw new RuntimeException('Intentional rollback');
        });
        throw new RuntimeException('Rollback did not throw.');
    } catch (RuntimeException $e) {
        if ($e->getMessage() !== 'Intentional rollback') { throw $e; }
    }
    $count = (int) $db->query('SELECT COUNT(*) FROM noor_integration_test')->fetchColumn();
    if ($count !== 1) { throw new RuntimeException('Rollback test failed.'); }
    echo "PASS MySQL connection, prepared insert, commit and rollback\n";
} catch (Throwable $e) {
    fwrite(STDERR, "FAIL MySQL integration: {$e->getMessage()}\n");
    exit(1);
}
