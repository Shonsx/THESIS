#!/usr/bin/env bash
set -e

# Prefer default app dir, else auto-detect by locating artisan
APP_DIR="/var/www/etry"
if [ ! -f "$APP_DIR/artisan" ]; then
  FOUND_ARTISAN="$(find /var/www -maxdepth 3 -type f -name artisan 2>/dev/null | head -n 1)"
  if [ -n "$FOUND_ARTISAN" ]; then
    APP_DIR="$(dirname "$FOUND_ARTISAN")"
  fi
fi

cd "$APP_DIR"
echo "[VPS] App dir: $APP_DIR"

# Ensure git safe directory (in case of root)
git config --global --add safe.directory "$APP_DIR" || true

echo "[VPS] Fetching origin…"
git fetch --all --tags --prune

# Determine default branch, prefer origin/main then origin/master
DEFAULT_BRANCH="main"
if git show-ref --verify --quiet refs/remotes/origin/main; then
  DEFAULT_BRANCH="main"
elif git show-ref --verify --quiet refs/remotes/origin/master; then
  DEFAULT_BRANCH="master"
fi

echo "[VPS] Pulling origin/$DEFAULT_BRANCH (fast-forward)"
git checkout "$DEFAULT_BRANCH" 2>/dev/null || true
git pull --ff-only origin "$DEFAULT_BRANCH"

echo "[VPS] Current HEAD:"
git rev-parse --short HEAD
git --no-pager log --oneline -n 3