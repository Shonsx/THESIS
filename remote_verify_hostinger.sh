set -euo pipefail

APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"

echo "== Blade snippet around /signup =="
if [ -f resources/views/components/layout.blade.php ]; then
  grep -n "/signup" -n -A2 -B2 resources/views/components/layout.blade.php || sed -n '150,170p' resources/views/components/layout.blade.php
else
  echo "Blade file not found at resources/views/components/layout.blade.php"
fi

echo "== Clear Laravel caches =="
php artisan optimize:clear || true

echo "== Manifest timestamp =="
ls -l public/build/manifest.json || echo "No Vite manifest present"