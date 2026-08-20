# Changelog

All notable changes to this project are documented in this file. This project
follows [Semantic Versioning](https://semver.org/).

## [1.1.0] - 2026-08-21

### Added

- **Newsletter module**: subscribers, lists, campaigns, send queue, CSV
  import/export, admin UI, CLI audit/purge/queue commands.
- **Member accounts**: magic-link passwordless sign-in for site readers
  (separate from admin auth), profile + avatar upload, content bookmarks.
- **Content analytics**: first-party pageview + read-time tracking
  (`content_metrics`, `daily_content_metrics`, `content_events`), a
  `sendBeacon`-based collector script.
- **RSS idea pool**: per-source polling with GUID dedup, an AI-assisted
  draft-suggestion workflow (configurable prompt template, no bundled
  source list), admin inbox/sources screens.
- **Media**: pluggable storage backend (local disk or Cloudflare
  R2/S3-compatible, via hand-rolled SigV4 signing), generated image
  derivatives (card/hero/og sizes), a redesigned drag-drop upload modal
  (also fixes an XSS gap in the old picker's folder-label rendering and
  adds CSRF protection to uploads).
- **Content**: `source_system`/`source_id`/`source_url`/`canonical_url`/
  `seo_data` fields (syndication + SEO metadata), `podcast` content type,
  a `wp:import` command for WordPress WXR exports.
- **Menus**: per-locale menu items, drag-drop reorder in the admin UI.
- **Settings**: schema-driven admin form (grouped, typed fields;
  sensitive fields no longer get blanked out by an empty submit).
- **Operations**: an error-logging pipeline (PHP log handler + JS error
  beacon + admin viewer), System Health and SEO Audit admin dashboards,
  `seo:audit`/`seo:fix`/`media:audit`/`media:fix`/`encoding:audit` CLI
  commands.
- **Themes**: `landing` (dark single-page marketing) and `corporate`
  themes; theme config repeater fields.
- Honeypot + rate-limit spam protection on contact and comment forms.

### Fixed

- Admin auth redirects broke when the app was installed under a
  non-root base URL; login/magic-link/TOTP flows now also send
  explicit HTTP 303 on post-login redirects.
- `Mailer::isConfigured()` reported "configured" even with a host set
  but no username/password; added `sendmail` protocol support.
- `/admin` and `/contact` 404s caused by route-registration ordering.

## [1.0.0] - 2026-08-11

Initial open-source release. KayaCMS is a modular headless-CMS on CodeIgniter 4.

### Highlights

- **Content**: multilingual content, revisions, related content, featured &
  collections, custom sections/fields (`custom_data` + content-type schemas),
  virtual pages, markdown rendering.
- **Theme engine**: `config.php` schema → admin settings, content-type view
  overrides, global header/footer scripts, example `default` + `minimal` themes.
- **Admin panel**: dashboard widgets, RBAC, audit log, media queue console,
  backups, maintenance mode, GDPR export, hooks/events overview.
- **Auth**: JWT + session, 2FA (TOTP), magic-link passwordless login, login
  brute-force protection, personal access tokens.
- **API**: REST endpoints with OpenAPI spec, rate limiting, webhooks.
- **Operations**: SQLite/MySQL support, `web-cron` (token-protected HTTP
  scheduler), deploy package (nginx vhost, permissions script, checklist),
  one-switch production security hardening (`app.securityHardening`).
- **Testing**: 126 feature/unit tests, all green.

### Notes

- Requires PHP >= 8.2.
- Seed admin: `admin@kayacms.local` / `admin123` (change on first login).

## [0.0.0] - 2026-08-07

Pre-release scaffolding. Replaced by v1.0.0.