# KayaCMS

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

- PHP >= 8.2 with extensions: `mbstring`, `json`, and either `pdo_sqlite` or
  `pdo_mysql`; `gd` (thumbnails), `curl` (webhooks).
- Composer.

## Installation

```bash
composer install
cp .env.example .env          # set CI_ENVIRONMENT, app.baseURL, app.jwtSecret
php spark migrate --all
php seed.php                  # creates default settings, users, themes, menus
php spark serve               # or point your web server at public/
```

Access the admin panel at `/admin` (default seed admin: `admin@kayacms.local`
/ `admin123` — **change it immediately**).

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
vendor/bin/phpunit
```

Feature and unit tests cover the admin panel, API, theme engine, media queue,
web-cron, GDPR, custom fields, and more (126 tests).

## License

MIT — see [LICENSE](LICENSE).