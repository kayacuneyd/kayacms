# KayaCMS Deploy Checklist

Shared hosting / VPS dağıtımında sırayla uygulanır.

## 0. Ön Koşullar

- PHP >= 8.1 (önerilen 8.2+); gerekli eklentiler: `pdo_sqlite` veya `pdo_mysql`, `gd`, `mbstring`, `json`, `curl`
- Web sunucusu: Apache (`.htaccess` hazır) veya Nginx (`deploy/nginx-vhost.conf`)
- Composer (opsiyonel — `vendor/` paket olarak da kopyalanabilir)

## 1. Dosyaları Kopyala

```bash
rsync -av --exclude='.git' --exclude='.env' --exclude='writable/db/*.sqlite3' --exclude='writable/backups/*' ./ user@host:/var/www/kayacms/
```

## 2. Ortam Dosyası

```bash
cp .env.example .env
# Düzenleyin:
#   CI_ENVIRONMENT = production
#   app.baseURL     = https://kayacms.example.com
#   app.jwtSecret   = <openssl rand -base64 48>
#   app.securityHardening = true   # production'da varsayılan açık; netleştirin
```

## 3. Veritabanı & Seed

```bash
php spark migrate --all
php seed.php                 # SQLite dev seed (admin/admin123)
```

MySQL/PostgreSQL için `php spark db:seed` seeder'larını kullanın.

## 4. İzinler

```bash
bash deploy/fix-permissions.sh /var/www/kayacms
```

## 5. Web Sunucusu

- **Apache:** `public/` web root; `.htaccess` hazır.
- **Nginx:** `deploy/nginx-vhost.conf` örneğini uyarlayın (alan adı, SSL, php-fpm socket).

## 6. Web Cron

Dış zamanlayıcıya (cron / UptimeRobot / EasyCron) şu URL'yi ekleyin:

```
https://kayacms.example.com/cron/run/<cron-token>
```

- Token üretme (Admin → Backup & Maintenance → Web Cron, veya CLI):
  ```bash
  php spark webcron:token --generate
  ```
- Varsayılan görevler: `media:queue,backup:create` (Admin'den değiştirilebilir).
- **Token boşken endpoint 403 döner (kapalı).** Sızarsa `webcron:token --generate` ile yenileyin.

## 7. Güvenlik Kontrolleri

- [ ] `CI_ENVIRONMENT=production`
- [ ] `app.baseURL` https ile başlıyor
- [ ] `app.jwtSecret` üretildi
- [ ] `app.securityHardening=true` (CSRF + InvalidChars + Honeypot + SecureHeaders)
- [ ] HTTPS zorunlu (nginx 301 veya `app.forceGlobalSecureRequests=true`)
- [ ] Admin şifresi seed sonrası değiştirildi
- [ ] `writable/` web root dışında (public/ dışında kaldığı için güvende)

## 8. Bonus

- OPCache: `opcache.enable=1`, `opcache.validate_timestamps=0`; deploy sonrası `kill -USR2 $(pgrep php-fpm)`
- Page cache: Admin → Settings → `cache_enabled=true`, `cache_ttl=3600`