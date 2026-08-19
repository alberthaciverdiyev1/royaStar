#!/usr/bin/env bash
# ============================================================
#  RoyaStar Production Deploy Script
#  Node:   nvm v20.20.1 (sistem v12 ile Vite build OLMAZ!)
#  Usage:  sudo bash /root/roya.sh [--skip-backup]
# ============================================================
set -euo pipefail

APP_DIR="/var/www/royaStar"
BACKUP_DIR="/root/backups/royastar"
NVM_DIR="${NVM_DIR:-/root/.nvm}"
WEB_SITE="royastar.foxsoft.agency"
ADMIN_SITE="royadmin.foxsoft.agency"

SKIP_BACKUP=0
if [[ "${1:-}" == "--skip-backup" ]]; then SKIP_BACKUP=1; fi

log()  { echo -e "\033[1;34m[roya.sh]\033[0m $*"; }
ok()   { echo -e "\033[1;32m  ✓\033[0m $*"; }
fail() { echo -e "\033[1;31m  ✗ $*\033[0m" >&2; }

# ------------------------------------------------------------
# 1. Ön kontrol
# ------------------------------------------------------------
cd "$APP_DIR"
if [[ ! -d .git ]]; then fail "Proje dizini bulunamadı: $APP_DIR"; exit 1; fi
if [[ ! -f .env ]]; then fail ".env bulunamadı"; exit 1; fi
if ! command -v git >/dev/null 2>&1; then fail "git yok"; exit 1; fi

log "RoyaStar deploy başlıyor ($APP_DIR)"

# Node'u kur (nvm v20)
export NVM_DIR
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
  # shellcheck source=/dev/null
  . "$NVM_DIR/nvm.sh" >/dev/null 2>&1
  nvm use 20.20.1 >/dev/null 2>&1 || nvm install 20 >/dev/null 2>&1 || true
fi
NODE_OK=""
command -v node >/dev/null 2>&1 && node -v 2>/dev/null | grep -q "^v20" && NODE_OK=1
if [[ -z "$NODE_OK" ]]; then
  fail "Node 20 bulunamadı. Önce: nvm install 20 && nvm use 20"
  exit 1
fi
ok "Node $(node -v) kullanılıyor"

# ------------------------------------------------------------
# 2. Git pull
# ------------------------------------------------------------
log "Git pull..."
git fetch origin 2>&1 | sed 's/^/  /'
LOCAL=$(git rev-parse HEAD 2>/dev/null)
REMOTE=$(git rev-parse origin/main 2>/dev/null || true)
if [[ -z "$REMOTE" ]]; then
  fail "origin/main bulunamadı — remote kontrol edin"
  exit 1
fi
if [[ "$LOCAL" == "$REMOTE" ]]; then
  ok "Zaten güncel (${LOCAL:0:7}) — git pull atlanıyor"
else
  git pull --ff-only origin main 2>&1 | sed 's/^/  /'
  ok "Güncellendi: ${LOCAL:0:7} → ${REMOTE:0:7}"
fi

# ------------------------------------------------------------
# 3. DB yedeği (varsayılan, --skip-backup ile atlanır)
# ------------------------------------------------------------
if [[ "$SKIP_BACKUP" -eq 0 ]]; then
  log "PostgreSQL yedeği alınıyor..."
  mkdir -p "$BACKUP_DIR"
  DBU=$(grep -oP "^DB_USERNAME=\K.*" .env)
  DBP=$(grep -oP "^DB_PASSWORD=\K.*" .env)
  DBNAME=$(grep -oP "^DB_DATABASE=\K.*" .env)
  DBBACKUP="$BACKUP_DIR/pre_deploy_$(date +%Y%m%d_%H%M%S).dump"
  if PGPASSWORD="$DBP" pg_dump -h 127.0.0.1 -U "$DBU" -d "$DBNAME" -F c -f "$DBBACKUP" 2>/dev/null; then
    ok "Yedek: $DBBACKUP ($(du -h "$DBBACKUP" | cut -f1))"
  else
    fail "pg_dump başarısız — script durduruldu (yedeği elle alın ya da --skip-backup)"
    exit 1
  fi
fi

# ------------------------------------------------------------
# 4. composer install
# ------------------------------------------------------------
log "composer install..."
if sudo -u www-data composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction >/tmp/roya_composer.log 2>&1; then
  ok "composer OK"
else
  tail -5 /tmp/roya_composer.log >&2
  fail "composer başarısız"
  exit 1
fi

# ------------------------------------------------------------
# 5. Migrasyonlar
# ------------------------------------------------------------
log "Migrasyonlar çalıştırılıyor..."
PENDING=$(sudo -u www-data php artisan migrate:status 2>/dev/null | grep -c Pending || true)
if [[ "$PENDING" -gt 0 ]]; then
  if sudo -u www-data php artisan migrate --force >/tmp/roya_migrate.log 2>&1; then
    ok "$PENDING migrasyon tamamlandı"
  else
    tail -8 /tmp/roya_migrate.log >&2
    fail "Migrasyon başarısız — geri almak için yedeği kullanın: $BACKUP_DIR"
    exit 1
  fi
else
  ok "Migrasyon yok (hepsi çalışmış)"
fi

# ------------------------------------------------------------
# 6. npm build (web + admin)
# ------------------------------------------------------------
log "npm build (web)..."
if npm run build >/tmp/roya_webbuild.log 2>&1; then
  ok "Web build OK ($(grep -oP 'built in \K[^ ]+' /tmp/roya_webbuild.log | tail -1))"
else
  tail -12 /tmp/roya_webbuild.log >&2
  fail "Web build başarısız"
  exit 1
fi

log "npm build (admin-panel)..."
if (cd admin-panel && npm run build) >/tmp/roya_adminbuild.log 2>&1; then
  ok "Admin build OK ($(grep -oP 'built in \K[^ ]+' /tmp/roya_adminbuild.log | tail -1))"
else
  tail -12 /tmp/roya_adminbuild.log >&2
  fail "Admin build başarısız"
  exit 1
fi

# ------------------------------------------------------------
# 7. Cache + yetkiler
# ------------------------------------------------------------
log "Cache ve yetkiler..."
sudo -u www-data php artisan optimize:clear >/dev/null 2>&1 || true
sudo -u www-data php artisan optimize >/dev/null 2>&1 || true
chown -R www-data:www-data \
  "$APP_DIR/public/build" \
  "$APP_DIR/admin-panel/dist" \
  "$APP_DIR/storage" \
  "$APP_DIR/bootstrap/cache" 2>/dev/null || true
ok "Cache + yetkiler tamam"

# ------------------------------------------------------------
# 8. Doğrulama
# ------------------------------------------------------------
log "Doğrulama..."
WEB_CODE=$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "https://$WEB_SITE/" || echo 000)
ADMIN_CODE=$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "https://$ADMIN_SITE/" || echo 000)
ok "Web  https://$WEB_SITE  → HTTP $WEB_CODE"
ok "Admin https://$ADMIN_SITE → HTTP $ADMIN_CODE"
if [[ "$WEB_CODE" != "200" ]]; then
  fail "Web 200 dönmedi — deploy BİTTİ ama doğrulama başarısız"
  exit 1
fi

log "Deploy başarıyla tamamlandı 🎉"
log "  Web   → https://$WEB_SITE"
log "  Admin → https://$ADMIN_SITE"
