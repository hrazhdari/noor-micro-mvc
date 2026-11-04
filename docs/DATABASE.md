# Database integration

Copy `config/database.example.php` to `config/database.php`. Edit credentials on the local machine, not in version control. Ensure the PHP CLI and Apache PHP both enable `pdo_mysql`. Do not expose this file through the web server.

```php
use Noor\Database\Database;

$settings = require dirname(__DIR__) . '/config/database.php';
$db = Database::connect($settings);
$stmt = $db->prepare('SELECT * FROM products WHERE ProductId = :id');
$stmt->execute(['id' => 25]);
$product = $stmt->fetch();

Database::transaction($db, function (PDO $pdo): void {
    $stmt = $pdo->prepare('UPDATE stock SET qty = qty - :amount WHERE id = :id AND qty >= :amount');
    $stmt->execute(['amount' => 2, 'id' => 10]);
    if ($stmt->rowCount() !== 1) { throw new RuntimeException('Insufficient stock'); }
});
```

The stock table is illustrative only. Proper inventory implementations require carefully designed schema, row-level locking, unique constraints, deadlock retries, and tests with concurrent transactions. `Database::transaction()` handles only one PDO connection and should not be nested using this wrapper.

**Testing:** the build container had PDO core but no PDO drivers. The connection and rollback could not be integration-tested there; run `php -m` and test with an isolated development MySQL database on your workstation.
