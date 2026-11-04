# Noor website customization

The website itself is implemented as a regular Noor MVC application:

- `app/Views/home.php`: landing content and PHP markup.
- `app/Views/partials/site-header.php`, `site-footer.php`: shared navigation and footer.
- `public/assets/css/site.css`: responsive RTL design, colors, layout and typography.
- `public/assets/js/site.js`: mobile menu and documentation-title search.
- `public/assets/img/noor-mark.png`: lion-and-sun logo asset used by the landing page.
- `app/Controllers/DocsController.php`: whitelist for documentation topics and View dispatch.
- `app/Views/docs/layout.php`: docs page frame.
- `app/Views/docs/pages/*.php`: individual documents.
- `config/routes.php`: `/docs` and `/docs/{slug}` routes.

## Why the docs use a page whitelist

Only known `slug` values can select templates. This prevents arbitrary paths from selecting local PHP files and ensures unknown topics return a 404 response.

## Editing documentation

1. Add your page to `app/Views/docs/pages/{slug}.php`.
2. Add `slug => title` to `DocsController::PAGES`.
3. Run `php tests/run.php` and browse `/docs/{slug}`.
4. Add/update tests and README when documenting a new API.

## Publishing requirements

For a production website, configure the domain's Document Root to `public/`, serve HTTPS, consider a self-hosted local font if external Google Fonts are unavailable, and validate headers, access controls and target environment. Never claim this beta is production certified.
