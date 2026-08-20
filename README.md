# KayaCMS

[![CI](https://github.com/kayacuneyd/kayacms/actions/workflows/ci.yml/badge.svg)](https://github.com/kayacuneyd/kayacms/actions/workflows/ci.yml)
[![Latest release](https://img.shields.io/github/v/release/kayacuneyd/kayacms)](https://github.com/kayacuneyd/kayacms/releases/latest)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A modular, headless(-ish) CMS built on **CodeIgniter 4**. Ships with a theme
engine, RBAC, media processing queue, REST API, webhooks, i18n-ready content,
GDPR export and a full admin panel — deployable to shared hosting or a VPS.

## Highlights

- **Modular vertical slices** under `modules/`: Content, Media, User, Menu,
  Setting, Theme, Taxonomy, Contact, Maintenance.
- **Theme engine**: `app/Views/themes/{theme}/` with a `config.php` schema for
  per-theme admin settings (`$theme_config`), content-type view overrides
  (`single-{type}.php`), global header/footer script injection.
- **Security**: JWT + session auth, RBAC, rate limiting, 2FA (TOTP), login
  brute-force protection, security audit log, one-switch production hardening
  (`app.securityHardening` → CSRF + InvalidChars + Honeypot + SecureHeaders).
- **API**: REST endpoints with personal access tokens, OpenAPI spec,
  rate limiting, webhooks.
- **Media queue**: async thumbnail/resize jobs via CLI or web-cron.
- **Web-cron**: token-protected HTTP scheduler for shared hosting
  (`GET cron/run/{token}`) — no shell cron required.
- **Other**: GDPR export, magic-link passwordless login, backups & maintenance
  mode, CKEditor 5 (local package), multilingual content.

## Requirements

- PHP >= 8.2 with extensions: `mbstring`, `json`, and either `sqlite3` or
  `mysqli`; `gd` (thumbnails), `curl` (webhooks).
- Composer.

## Installation

### Option A — Composer (recommended)

```bash
composer create-project kayacms/kayacms myapp
cd myapp
php spark app:install
```

`composer create-project` copies `.env` from `.env.example` and generates an
encryption key automatically. `app:install` then checks your database
settings, runs migrations, seeds default data (roles, settings, themes, a
contact form), and walks you through creating the first admin account
(pass `--no-interaction` for a non-interactive run in scripts/CI, with
`--admin-email`, `--admin-username`, `--admin-password` options).

```bash
php spark serve   # or point your web server at public/
```

### Option B — Download a release

No Composer needed. Grab the latest packaged zip from
[Releases](https://github.com/kayacuneyd/kayacms/releases/latest), unzip it,
then:

```bash
cd kayacms-x.y.z
cp .env.example .env
php spark app:install
php spark serve
```

### Option C — Manual (clone + composer install)

```bash
git clone https://github.com/kayacuneyd/kayacms.git
cd kayacms
composer install
cp .env.example .env          # set CI_ENVIRONMENT, app.baseURL, jwt.secret
php spark app:install         # or run migrate --all / db:seed by hand
php spark serve
```

In every case, access the admin panel at `/admin` with the account you
created during `app:install`.

## Deploying

See [`deploy/DEPLOY.md`](deploy/DEPLOY.md): rsync checklist, `.env` setup,
migrations/seed, permissions script (`deploy/fix-permissions.sh`), Nginx vhost
sample (`deploy/nginx-vhost.conf`), web-cron URL, security checklist.

## Developing themes

1. Create `app/Views/themes/{slug}/` (see the bundled `default` and `minimal`
   themes as examples).
2. Optional `config.php` schema → editable in Admin → Themes → Configure,
   exposed to views as `$theme_config`.
3. Views: `index`, `single`, `single-{content_type}`, `category`, `tag`,
   `search`, `virtual`, `partials/*`.
4. Activate the theme in Admin → Themes.

## REST API

- Base: `https://your-site/api`
- Auth: JWT (`POST /api/auth/login`) or personal access token (`API-Key` header).
- Interactive spec: `GET /api/openapi`.

See `docs/` (or the API route file) for endpoint details.

## Hooks & events

KayaCMS exposes a lightweight hook layer (`App\Libraries\Hooks`) plus webhooks
for content/user/comment lifecycle: see Admin → Hooks & Events.

## Testing

```bash
composer test   # or: vendor/bin/phpunit
composer lint
```

Feature and unit tests cover the admin panel, API, theme engine, media queue,
web-cron, GDPR, custom fields, and more (147 tests). Every push and pull
request runs the same suite via [GitHub Actions](.github/workflows/ci.yml).

## Releasing

```bash
composer run release -- patch   # or: minor / major
git push && git push --tags
```

Bumps `app/Config/Version.php`, drafts a `CHANGELOG.md` entry from the
commits since the last tag, and creates a `chore(release): vX.Y.Z` commit +
git tag (after a confirmation prompt). Pushing the tag triggers
[`release.yml`](.github/workflows/release.yml), which builds a `--no-dev`
zip and publishes it to [GitHub Releases](https://github.com/kayacuneyd/kayacms/releases).

## License

MIT — see [LICENSE](LICENSE).