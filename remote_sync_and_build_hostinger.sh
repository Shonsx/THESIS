set -euo pipefail

APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"

echo "== Git info and sync =="
if [ -d .git ]; then
  git config --global --add safe.directory "$APP_DIR" || true
  git remote -v || true
  git fetch --all || true
  UPSTREAM=""
  if git rev-parse --verify origin/main >/dev/null 2>&1; then UPSTREAM="origin/main"; fi
  if [ -z "$UPSTREAM" ] && git rev-parse --verify origin/master >/dev/null 2>&1; then UPSTREAM="origin/master"; fi
  if [ -z "$UPSTREAM" ]; then echo "No origin/main or origin/master found"; git branch -r || true; exit 1; fi
  git reset --hard "$UPSTREAM" || true
else
  echo "No .git directory; code may be deployed without git checkout"
fi

echo "== Node/Vite build =="
node -v || true
npm -v || true
npm ci --no-audit --no-fund
npm run build

echo "== Clear Laravel caches =="
php artisan optimize:clear || true

echo "== Manifest timestamp =="
ls -l public/build/manifest.json || echo "No Vite manifest present"

echo "== Blade snippet around /signup =="
if [ -f resources/views/components/layout.blade.php ]; then
  grep -n "/signup" -n -A2 -B2 resources/views/components/layout.blade.php || sed -n '150,170p' resources/views/components/layout.blade.php
else
  echo "Blade file not found at resources/views/components/layout.blade.php"
fi