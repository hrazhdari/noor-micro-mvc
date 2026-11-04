# Changelog

## 1.0.0-stable
- Replaced the previous flame logo with the approved lion-and-sun visual identity.
- Refined the landing page copy to be clearer and more product-focused.
- Simplified several Persian documentation sections for easier reading.
- Marked the current API as the first stable release; production environment testing and independent security review remain separate steps.
- Kept all framework tests passing after the branding and documentation update.

0.3.0-beta — 2026-11-05
- Redesigned the public landing page with a responsive Persian RTL layout, custom editable SVG Noor logo, and self-hosted CSS/JavaScript assets.
- Added a browsable documentation website covering 13 topics and real code examples.
- Added documentation routes, a dedicated DocsController, navigation search, README guides, and documentation regression tests.
- Kept the framework API backward compatible with 0.2.0-beta.
- This is still a beta: production deployment requires independent security review and target-host MySQL and integration tests.

## 0.2.0-beta — 2026-11-04
- Kept Noor as an independent Composer-free project.
- Reworked Router for static-first matching, named parameters, safe decoding, duplicate checks, HEAD and OPTIONS, and per-route middleware.
- Added subdirectory-aware Request URLs for XAMPP.
- Added a lightweight validator, clearer configuration and form/CSRF example.
- Expanded CLI regression tests and documentation.
- Database integration and production security audits remain open.
