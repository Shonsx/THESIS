set -e
CANDIDATES="/var/www/etry /var/www/html /var/www"
APP_DIR=""
for d in $CANDIDATES; do
  if [ -f "$d/artisan" ]; then APP_DIR="$d"; break; fi
done
if [ -z "$APP_DIR" ]; then echo "artisan not found"; exit 1; fi
cd "$APP_DIR"
php -v
php artisan --version
php artisan down || true
php artisan db:seed --force
php artisan db:seed --class=AdminSeeder --force
php artisan tinker --execute='echo optional(App\Models\User::where("name","admin")->first())->email;'
php artisan optimize:clear || true
php artisan up || true
