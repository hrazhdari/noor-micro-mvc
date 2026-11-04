# Noor Micro MVC PHP Framework

**Noor** is a small dependency-free PHP 8.2+ micro MVC framework intended for learning, prototypes, and applications whose owners want readable, conventional PHP. **No Composer is needed to run it.** This is an independently maintained project; it is not based on Flight or Laravel.

> **Release: 1.0.0-stable (initial stable API).** Tested features are described below. This is **not a security-audited or production-certified framework**. Before customer deployment, validate your target PHP/Apache/MySQL hosting, authentication integration, permissions, file uploads, backups, and application security.

## Website and documentation

The new responsive Persian RTL landing page is served at `/`. Browsable documentation is at `/docs`, with 13 sections including Quick Start, MVC architecture, routing, HTTP, security, API, tests and deployment. A lion-and-sun logo PNG lives at `public/assets/img/noor-mark.png`; layout is in `app/Views/home.php` and `public/assets/css/site.css`. The docs pages are in `app/Views/docs/pages/` and are accessed via `DocsController`.

This is the first stable API release. It has automated tests, but has **not** undergone an independent security audit or real MySQL integration testing in the build environment. Production deployment still requires application-level controls and testing on the target server.

## Quick start (Windows + XAMPP)

1. Extract the archive into `C:\xampp\htdocs\noor`.
2. Start Apache in XAMPP.
3. Open `http://localhost/noor/public/` (this is the test-only subdirectory URL).
4. Test `http://localhost/noor/public/hello/Ali`, `/health`, and `/form`.
5. Optional: copy `config/app.example.php` to `config/app.php` and adjust the timezone (keep debug=false for any public host).
6. Run tests from the `noor` folder: `php tests/run.php` (PATH must include PHP); or `C:\xampp\php\php.exe tests\run.php`.

## Alternative local server

From the project root: `php -S 127.0.0.1:8000 -t public public/router.php`; open `http://127.0.0.1:8000/`. Not intended for production. If this port does not work, use XAMPP.

## Concepts and responsibilities

| Layer | Location | Responsibility |
|---|---|---|
| Entry | `public/index.php` | Boot application, start session, dispatch and send response |
| Autoload | `bootstrap.php` | Map `Noor\` and `App\` namespaces to files |
| HTTP | `src/Noor/Http/` | Normalize requests and generate safe responses |
| Router | `src/Noor/Core/Router.php` | Route matching, named parameters, middleware and 404/405 |
| App | `src/Noor/Core/Application.php` | Convert uncaught exceptions to logged 500 responses |
| View | `src/Noor/Core/View.php` | PHP templates under an approved directory |
| Validation | `src/Noor/Core/Validation.php` | Simple input checks (`required`, `string`, `integer`, `email`, `min`, `max`) |
| Security | `src/Noor/Security/` | Session, password helpers, auth session primitives, CSRF |
| Database | `src/Noor/Database/Database.php` | PDO connection and transaction wrapper |
| Application code | `app/` | Your controllers and HTML templates |
| App settings | `config/` | Routes and opt-in local settings |
| Tests | `tests/run.php` | Self-contained CLI test runner |

## Basic routes

Edit `config/routes.php`:

```php
use Noor\Core\Router;
use Noor\Http\Request;
use Noor\Http\Response;

$router = new Router();
$router->get('/products', static fn(Request $req): Response => Response::text('Products'));
$router->get('/products/{id}', static fn(Request $req, string $id): Response => Response::text($id));
$router->post('/products', static fn(Request $req): Response => Response::json(['data' => $req->all()]));
return $router;
```

A `Route` handler **must** return `Noor\Http\Response`; it always receives `Request` as the first parameter, then dynamic route arguments in order.

## Middleware, authentication and CSRF

See the working `/form` route in `config/routes.php`, `app/Controllers/HomeController.php` and `src/Noor/Security/Middleware.php`. CSRF is applied **to the form POST route**, not globally. Add it to **each state-changing application route** (or register it globally after designing your exemptions). Session-based authentication primitives are **not** a complete login system: add database users, identity checks, login throttling, password resets, role/permission rules and audit logging to your application.

## Database

Use the PDO wrapper with a private `config/database.php` (copy `config/database.example.php`), then call `Database::connect($settings)` and `Database::transaction($pdo, $callback)`. See [docs/DATABASE.md](docs/DATABASE.md). **MySQL integration was not tested in the build environment because its PHP has no PDO drivers.**

## Deployment safety

- For production, configure the web server document root as **`public/`**, while `src/`, `app/`, `config/`, `storage/` stay outside web access. Do not use the XAMPP `.../noor/public/` style for a public production host without properly blocking the parent tree.
- Do not commit `config/app.php`, `config/database.php`, secrets or customer data. Keep `debug=false` in production.
- Use HTTPS and a host that supports PHP 8.2+. Confirm `pdo_mysql` and its version.
- Verify Apache `mod_rewrite` and `.htaccess` support; set filesystem permissions to prevent public access to private data. Root `.htaccess` is a supplementary local guard only.
- Validate all user inputs; escape data at output according to its HTML/JS/URL context. Use prepared SQL statements, authorization and transactions.
- This framework does not include an ORM, migrations, upload handler, rate limiter, account recovery, API tokens, queue or production security audit.

## Tests

Run `php tests/run.php`. Tested cases cover routes, dynamic decoding, static precedence, GET/POST/HEAD/OPTIONS/405, global and route middleware, Response header and redirect safety, view confinement and escaping, CSRF checks, password hashes, auth primitives, exception 500 responses, path base handling, validation and example app. See `TEST-REPORT.md` for actual results and limits.

## License

MIT. See `LICENSE`. Please credit the project if you publish a fork.
