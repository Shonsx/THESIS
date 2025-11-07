#!/usr/bin/env bash
set -e

# Auto-detect Laravel app dir (contains artisan) starting from HOME and common paths
APP_DIR=""
for root in "$HOME" "/home" "/var/www" "/srv/www"; do
  if [ -d "$root" ]; then
    found=$(find "$root" -maxdepth 5 -type f -name artisan 2>/dev/null | head -n 1)
    if [ -n "$found" ]; then
      APP_DIR=$(dirname "$found")
      break
    fi
  fi
done

if [ -z "$APP_DIR" ]; then
  echo "ERROR: Could not locate Laravel app (artisan)."
  exit 1
fi

cd "$APP_DIR"
echo "[Hostinger] App dir: $APP_DIR"

# Ensure git safe directory (in case of different user)
git config --global --add safe.directory "$APP_DIR" || true

echo "[Hostinger] Fetching origin…"
git fetch --all --tags --prune

# Determine default branch, prefer origin/main then origin/master
DEFAULT_BRANCH="main"
if git show-ref --verify --quiet refs/remotes/origin/main; then
  DEFAULT_BRANCH="main"
elif git show-ref --verify --quiet refs/remotes/origin/master; then
  DEFAULT_BRANCH="master"
fi

echo "[Hostinger] Pulling origin/$DEFAULT_BRANCH (fast-forward)"
git checkout "$DEFAULT_BRANCH" 2>/dev/null || true
git pull --ff-only origin "$DEFAULT_BRANCH"

echo "[Hostinger] Current HEAD:"
git rev-parse --short HEAD
git --no-pager log --oneline -n 3