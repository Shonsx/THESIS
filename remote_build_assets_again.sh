set -euo pipefail
cd /var/www/etry

# Rebuild assets to pick up class changes
npm ci --no-audit --no-fund
npm run build

# Clear Laravel caches
php artisan optimize:clear

# Show updated manifest timestamp
ls -l public/build/manifest.json