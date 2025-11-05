set -euo pipefail
APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"

php -v
php artisan --version

# Maintenance mode
php artisan down || true

# Show current migration status (optional)
php artisan migrate:status || true

# Reset and reseed
php artisan migrate:refresh --force
php artisan db:seed --force

# Ensure assets are migrated to storage if needed
php artisan assets:migrate-storage || true

# Quick verification via Tinker (non-interactive)
php artisan tinker --execute="App\\Models\\User::select('id','email','first_login')->orderBy('id')->get()->toArray();"
php artisan tinker --execute="App\\Models\\Product::select('id','name','image')->orderBy('id')->get()->toArray();"

# Clear caches and bring app up
php artisan optimize:clear
php artisan up