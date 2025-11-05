#!/usr/bin/env bash
set -eo pipefail

log() {
  echo "[pull-clear][VPS] $1" >&2
}

# Auto-detect Laravel app dir (contains artisan)
APP_DIR=""
for d in /var/www /var/www/html /srv/www /opt; do
  if [ -d "$d" ]; then
    found=$(find "$d" -maxdepth 4 -type f -name artisan 2>/dev/null | head -n 1 || true)
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

# Ensure git safe directory (in case of root)
git config --global --add safe.directory "$APP_DIR" || true

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