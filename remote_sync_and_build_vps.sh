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

echo "== Git info and sync =="
git config --global --add safe.directory "$APP_DIR" || true
git remote -v || true
git fetch --all || true
UPSTREAM=""
if git rev-parse --verify origin/master >/dev/null 2>&1; then UPSTREAM="origin/master"; fi
if [ -z "$UPSTREAM" ] && git rev-parse --verify origin/main >/dev/null 2>&1; then UPSTREAM="origin/main"; fi
if [ -z "$UPSTREAM" ]; then echo "No origin/main or origin/master found"; git branch -r || true; exit 1; fi
git pull --ff-only "$UPSTREAM" || git reset --hard "$UPSTREAM" || true

echo "== Laravel optimize:clear =="
php artisan optimize:clear || true

echo "== Ensure Node via nvm =="
export NVM_DIR="$HOME/.nvm"
if [ ! -s "$NVM_DIR/nvm.sh" ]; then
  curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
fi
. "$NVM_DIR/nvm.sh"
nvm install --lts || true
nvm use --lts || true
node -v || true
npm -v || true

echo "== Build assets =="
export RAYON_NUM_THREADS=1
export LIGHTNINGCSS_THREADS=1
export NODE_OPTIONS="--max-old-space-size=256"
npm ci --no-audit --no-fund
npm run build

echo "== Manifest timestamp =="
ls -l public/build/manifest.json || echo "No Vite manifest present"

echo "== Done =="