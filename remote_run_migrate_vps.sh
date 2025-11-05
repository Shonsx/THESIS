set -eo pipefail

# Try default app dir, else auto-detect by locating artisan
APP_DIR="/var/www/etry"
if [ ! -f "$APP_DIR/artisan" ]; then
  FOUND_ARTISAN="$(find /var/www -maxdepth 3 -name artisan 2>/dev/null | head -n 1)"
  if [ -n "$FOUND_ARTISAN" ]; then
    APP_DIR="$(dirname "$FOUND_ARTISAN")"
  fi
fi
cd "$APP_DIR"

echo "== Laravel migrate =="
php artisan migrate --force

echo "== Laravel optimize:clear =="
php artisan optimize:clear || true

echo "== Migration status =="
php artisan migrate:status || true

echo "== Done =="