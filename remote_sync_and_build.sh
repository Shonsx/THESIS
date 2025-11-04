set -euo pipefail
cd /var/www/etry

# Pull latest from origin if repo exists and safe.directory set
if [ -d .git ]; then
  git config --global --add safe.directory /var/www/etry || true
  git fetch --all || true
  git reset --hard origin/main || true
fi

# Rebuild assets to capture new classes
npm ci --no-audit --no-fund
npm run build

# Clear Laravel caches
php artisan optimize:clear

# Show updated manifest timestamp
ls -l public/build/manifest.json