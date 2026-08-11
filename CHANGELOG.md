# Changelog

All notable changes to this project are documented in this file. This project
follows [Semantic Versioning](https://semver.org/).

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