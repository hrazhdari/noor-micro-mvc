# Noor 1.0.0-stable — Test report

**Executed:** 2026-11-04. **Runtime:** PHP 8.4.24 CLI, Linux container. This is not the user's Windows/XAMPP server.

## Automated suite

Command: `php tests/run.php`

**Result: 25 PASS / 0 FAIL.** Areas: route matching and named parameters, regex escaping, duplicate routes, static route priority, decoded parameters, unsafe encoded separators, GET/POST/HEAD/OPTIONS, 404/405 Allow, global and route middleware, response status/headers/redirects/JSON, safe View paths, escaping, CSRF, password hashing, session auth primitives, generic 500 response, XAMPP subdirectory normalization, validator and example controller.

## Actual HTTP tests

Ran local PHP server using `php -S 127.0.0.1:18293 -t public public/router.php` and sent real HTTP requests:

| Request | Observed | Result |
|---|---|---|
| `GET /` | 200 HTML | PASS |
| `GET /hello/Ali` | 200 JSON | PASS |
| `GET /hello/نور` (URL-encoded) | 200 JSON in UTF-8 | PASS |
| `GET /health` | 200 JSON | PASS |
| `GET /missing` | 404 | PASS |
| `GET /form` | 200 HTML with CSRF token | PASS |
| `POST /form` without CSRF | 419 | PASS |
| `POST /form` with valid CSRF and session cookie | 200 HTML | PASS |

## Limitations and outstanding checks

1. **MySQL was not available.** PHP CLI had `PDO` but `PDO::getAvailableDrivers()` returned `[]`. No claim is made that a real MySQL connection or transaction was tested here. To test on XAMPP with a disposable schema, run `php tests/mysql-integration.php` after editing `config/database.php` and enabling `pdo_mysql`.
2. **Apache/XAMPP `.htaccess` rewriting was not tested in this Linux environment.** Verify clean URLs and root access denial on the target Apache configuration.
3. **No independent security audit, load test or concurrency test** has been run. Session storage permissions, CSRF on all state-changing app routes, login rate limiting, role permissions, secure uploads and account recovery are application concerns not implemented in the current framework core.
4. Tests are a foundation for regression testing; they do not guarantee all defects have been found.

## Release judgment

Suitable as an **initial stable framework API for further development**. The stable version label does not mean independent security certification or verified readiness for customer data. Before using for paid customers, add integration tests and perform manual review of the actual application and deployment configuration.

## Final package verification (2026-11-05)

- `php tests/run.php`: **25 PASS / 0 FAIL**, PHP 8.4.24 CLI.
- HTTP responses: `/`, `/docs`, `/docs/overview`, `/health` returned 200 from the local PHP development server.
- Local MySQL driver availability: none; **MySQL integration is not verified**.
- Apache/XAMPP, security audit, and load tests: not run in the build environment.
