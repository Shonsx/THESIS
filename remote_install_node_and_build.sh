set -euo pipefail

APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"

echo "== Ensure nvm installed =="
export NVM_DIR="$HOME/.nvm"
if [ ! -s "$NVM_DIR/nvm.sh" ]; then
  curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
fi

echo "== Load nvm =="
# shellcheck disable=SC1090
. "$NVM_DIR/nvm.sh"

echo "== Install and use Node LTS =="
nvm install --lts
nvm use --lts
node -v
npm -v

echo "== Build frontend assets =="
npm ci --no-audit --no-fund
npm run build

echo "== Clear Laravel caches =="
php artisan optimize:clear || true

echo "== Manifest timestamp =="
ls -l public/build/manifest.json || echo "No Vite manifest present"