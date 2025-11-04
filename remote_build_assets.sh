set -euo pipefail
cd /var/www/etry

# Install and build frontend assets
if [ -f package.json ]; then
  echo "== Installing node dependencies =="
  npm ci --no-audit --no-fund
  echo "== Building with Vite =="
  npm run build
else
  echo "package.json not found; skipping build"
fi

# Show manifest timestamp
ls -l public/build/manifest.json || true

# Clear Laravel caches
php artisan optimize:clear

# Test a cache-busting asset URL from vite
if [ -f public/build/manifest.json ]; then
  echo "== Sample asset entries =="
  head -n 5 public/build/manifest.json || true
fi