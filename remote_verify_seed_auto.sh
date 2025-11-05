set -euo pipefail

APP_DIR=""
for d in /var/www/etry /var/www/html /home/*/public_html /home/*/domains/*/public_html /srv/www /opt/etry; do
  if [ -f "$d/artisan" ]; then APP_DIR="$d"; break; fi
done
if [ -z "$APP_DIR" ]; then
  APP_DIR=$(find /var/www /home /srv /opt -maxdepth 4 -type f -name artisan 2>/dev/null | head -n1 | xargs dirname || true)
fi
if [ -z "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then echo "artisan not found"; exit 1; fi
echo "Using APP_DIR=$APP_DIR"
cd "$APP_DIR"

php artisan tinker --execute="App\\Models\\User::select('id','email')->orderBy('id')->limit(5)->get()->toArray();" || true
php artisan tinker --execute="App\\Models\\Product::count();" || true