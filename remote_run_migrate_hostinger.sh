#!/usr/bin/env bash
set -eo pipefail

log() {
  echo "[migrate][Hostinger] $1" >&2
}

# Auto-detect Laravel app dir (contains artisan) starting from HOME and common paths
APP_DIR=""
search_roots=("$HOME" "/home" "/var/www" "/srv/www" "/opt")
for root in "${search_roots[@]}"; do
  if [ -d "$root" ]; then
    found=$(find "$root" -maxdepth 5 -type f -name artisan 2>/dev/null | head -n 1 || true)
    if [ -n "$found" ]; then
      APP_DIR=$(dirname "$found")
      break
    fi
  fi
done

if [ -z "$APP_DIR" ]; then
  log "ERROR: Could not locate Laravel app (artisan)."
  exit 1
fi

cd "$APP_DIR"
log "App dir: $APP_DIR"

log "Running migrations (force)…"
php artisan migrate --force || true

log "Clearing Laravel caches…"
php artisan optimize:clear || true

log "Done."