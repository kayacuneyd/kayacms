#!/usr/bin/env bash
# KayaCMS — deploy sonrası yazılabilir dizinlerin izinlerini ayarlar.
#
# Kullanım: bash deploy/fix-permissions.sh /var/www/kayacms [WEB_USER]
set -euo pipefail

APP_DIR="${1:?Kullanım: bash deploy/fix-permissions.sh /var/www/kayacms [WEB_USER]}"
WEB_USER="${2:-www-data}"

if [[ ! -d "$APP_DIR" ]]; then
  echo "HATA: $APP_DIR dizini yok." >&2
  exit 1
fi

# writable/: sqlite db, cache, session, logs, uploads, backups.
chown -R "$WEB_USER" "$APP_DIR/writable"
chmod -R 775 "$APP_DIR/writable"

# public/assets/uploads: medya yükleme.
if [[ -d "$APP_DIR/public/assets/uploads" ]]; then
  chown -R "$WEB_USER" "$APP_DIR/public/assets/uploads"
  chmod -R 775 "$APP_DIR/public/assets/uploads"
fi

echo "OK: writable/ ve public/assets/uploads izinleri ayarlandı ($WEB_USER)."