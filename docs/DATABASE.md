# Database integration

Copy `config/database.example.php` to `config/database.php`. Edit credentials on the local machine, not in version control. Ensure the PHP CLI and Apache PHP both enable `pdo_mysql`. Do not expose this file through the web server.

```php
use Noor\Database\Database;

$settings = require dirname(__DIR__) . '/config/database.php';
$db = Database::connect($settings);

// Example schema: articles(id, title) and activity_logs(message).
$stmt = $db->prepare('SELECT id, title FROM articles WHERE id = :id');
$stmt->execute(['id' => 25]);
$article = $stmt->fetch();

Database::transaction($db, function (PDO $pdo): void {
    $stmt = $pdo->prepare('INSERT INTO activity_logs (message) VALUES (:message)');
    $stmt->execute(['message' => 'Article viewed']);
});
```

The `articles` and `activity_logs` tables are illustrative and are not created by Noor. Build your own schema and validate it before running these examples. For operations that update several rows, consider constraints, transaction isolation, locking, deadlock retries, and concurrent tests. `Database::transaction()` wraps a single PDO connection and must not be nested with this helper.

**Testing:** the build container had PDO core but no PDO drivers. The connection and rollback could not be integration-tested there; run `php -m` and test with an isolated development MySQL database on your workstation.
