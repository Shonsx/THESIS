#!/usr/bin/env bash
set -eo pipefail

log() {
  echo "[pull-https][VPS] $1" >&2
}

# Try default app dir, else auto-detect by locating artisan
APP_DIR="/var/www/etry"
if [ ! -f "$APP_DIR/artisan" ]; then
  FOUND_ARTISAN="$(find /var/www -maxdepth 3 -type f -name artisan 2>/dev/null | head -n 1 || true)"
  if [ -n "$FOUND_ARTISAN" ]; then
    APP_DIR="$(dirname "$FOUND_ARTISAN")"
  fi
fi

if [ ! -d "$APP_DIR" ]; then
  log "ERROR: App dir not found"
  exit 1
fi

cd "$APP_DIR"
log "App dir: $APP_DIR"

# Ensure git safe directory (in case of root)
git config --global --add safe.directory "$APP_DIR" || true

log "Setting remote origin to HTTPS (GitHub)…"
git remote set-url origin https://github.com/Shonsx/THESIS.git || true
git remote -v || true

log "Fetching origin…"
git fetch --all --tags --prune || true

# Determine default branch, prefer origin/main then origin/master
DEFAULT_BRANCH="main"
if git show-ref --verify --quiet refs/remotes/origin/main; then
  DEFAULT_BRANCH="main"
elif git show-ref --verify --quiet refs/remotes/origin/master; then
  DEFAULT_BRANCH="master"
fi

log "Pulling origin/$DEFAULT_BRANCH (fast-forward)…"
git checkout "$DEFAULT_BRANCH" 2>/dev/null || true
git pull --ff-only origin "$DEFAULT_BRANCH" || true

log "Clearing Laravel caches…"
php artisan optimize:clear || true

log "Current HEAD and last 3 commits:"
git rev-parse --short HEAD || true
git --no-pager log --oneline -n 3 || true

log "Done."