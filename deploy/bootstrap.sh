#!/usr/bin/env bash
set -euo pipefail

# Simple deploy helper to refresh migrations and seed admin on the server.
# Usage options:
#   - Default: runs in APP_DIR (defaults to /var/www/etry)
#   - Set APP_DIR env to override: APP_DIR=/path/to/app ./deploy/bootstrap.sh
#   - Set SEED_PRODUCTS=1 to also run ProductSeeder

APP_DIR=${APP_DIR:-/var/www/etry}
SEED_PRODUCTS=${SEED_PRODUCTS:-0}

echo "[deploy] Using APP_DIR=$APP_DIR"
cd "$APP_DIR"

echo "[deploy] PHP version:"
php -v || true

echo "[deploy] Putting app into maintenance mode"
php artisan down || true

echo "[deploy] Running migrate:refresh (this will re-run all migrations)"
php artisan migrate:refresh --force

echo "[deploy] Seeding admin user via DatabaseSeeder"
php artisan db:seed --force

if [ "$SEED_PRODUCTS" = "1" ]; then
  echo "[deploy] Seeding demo products via ProductSeeder"
  php artisan db:seed --class=ProductSeeder --force
fi

echo "[deploy] Clearing caches"
php artisan optimize:clear || true

echo "[deploy] Bringing app out of maintenance"
php artisan up || true

echo "[deploy] Done. Verify login with admin@temp.com / admiN123456789"

set -euo pipefail

# Simple Laravel deploy bootstrap for Ubuntu/Debian with Nginx + PHP-FPM.
# Usage (as root on the server):
#   bash bootstrap.sh [domain] [repo_url] [app_dir]
# Defaults:
#   domain   = cspot-etry.com
#   repo_url = https://github.com/Shonsx/THESIS.git
#   app_dir  = /var/www/etry

DOMAIN="${1:-cspot-etry.com}"
REPO="${2:-https://github.com/Shonsx/THESIS.git}"
APP_DIR="${3:-/var/www/etry}"

echo "Domain: ${DOMAIN}"
echo "Repo:   ${REPO}"
echo "AppDir: ${APP_DIR}"

echo "[1/8] Installing packages (nginx, php, composer)..."
export DEBIAN_FRONTEND=noninteractive
apt update -y
apt install -y nginx git unzip curl php-cli php-curl php-zip php-mbstring php-xml php-bcmath

# Install PHP-FPM (prefer 8.2, fallback to installed default)
if ! dpkg -s php8.2-fpm >/dev/null 2>&1; then
  apt install -y php-fpm || true
  apt install -y php8.2-fpm || true
fi

# Install Composer if missing
if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi

echo "[2/8] Cloning or updating repository..."
mkdir -p "${APP_DIR}"
if [ -d "${APP_DIR}/.git" ]; then
  git -C "${APP_DIR}" fetch --all --prune
  git -C "${APP_DIR}" reset --hard origin/HEAD || git -C "${APP_DIR}" pull --rebase
else
  git clone "${REPO}" "${APP_DIR}"
fi

cd "${APP_DIR}"

echo "[3/8] Preparing environment..."
if [ ! -f .env ]; then
  cp -n .env.example .env || true
fi

# Ensure APP_URL uses provided domain
if grep -q '^APP_URL=' .env; then
  sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
else
  echo "APP_URL=https://${DOMAIN}" >> .env
fi

echo "[4/8] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "[5/8] Laravel setup (key, storage link)..."
php artisan key:generate || true
php artisan storage:link || true

echo "[6/8] Setting permissions..."
chown -R www-data:www-data "${APP_DIR}"
chmod -R ug+rwx "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

echo "[7/8] Configuring Nginx..."
# Detect PHP-FPM socket automatically
PHP_FPM_SOCK=$(ls /run/php/php*-fpm.sock 2>/dev/null | head -n1)
if [ -z "${PHP_FPM_SOCK}" ]; then
  echo "Could not detect PHP-FPM socket. Ensure php-fpm is running." >&2
  systemctl start php8.2-fpm || systemctl start php-fpm || true
  PHP_FPM_SOCK=$(ls /run/php/php*-fpm.sock 2>/dev/null | head -n1)
fi

NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}.conf"
cat >"${NGINX_CONF}" <<EOF
server {
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_FPM_SOCK};
    }

    location ~ /\. {
        deny all;
    }
}
EOF

ln -sf "${NGINX_CONF}" "/etc/nginx/sites-enabled/${DOMAIN}.conf"
nginx -t
systemctl enable nginx php-fpm || true
systemctl restart php-fpm || systemctl restart php8.2-fpm || true
systemctl reload nginx

echo "[8/8] Done. Next steps:"
cat <<NEXT
- Edit ${APP_DIR}/.env to set DB_* credentials.
- Run: cd ${APP_DIR} && php artisan migrate --force
- Optional SSL: apt install -y certbot python3-certbot-nginx && \
  certbot --nginx -d ${DOMAIN} -d www.${DOMAIN} -m you@email.com --agree-tos --non-interactive
- Check logs if any issue: tail -n 200 /var/log/nginx/error.log
NEXT

echo "Bootstrap complete for ${DOMAIN} at ${APP_DIR}."
set -euo pipefail

# Simple Laravel deploy/bootstrap script for Ubuntu/Debian servers
# Usage:
#   sudo bash deploy/bootstrap.sh <domain> <app_dir>
# Example:
#   sudo bash deploy/bootstrap.sh cspot-etry.com /var/www/shon

DOMAIN=${1:-cspot-etry.com}
APP_DIR=${2:-/var/www/shon}

echo "Bootstrap starting for domain: ${DOMAIN}, app dir: ${APP_DIR}"

apt update
apt install -y nginx php-fpm php-cli php-curl php-zip php-mbstring php-xml php-bcmath git unzip curl

# Composer
if ! command -v composer >/dev/null 2>&1; then
  echo "Installing Composer..."
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi

mkdir -p "${APP_DIR}"
cd "${APP_DIR}"

# If vendor is missing, install
if [ ! -d vendor ]; then
  composer install --no-dev --optimize-autoloader
fi

# Ensure .env exists
if [ ! -f .env ]; then
  cp -n .env.example .env
fi

php artisan key:generate || true
php artisan storage:link || true

chown -R www-data:www-data "${APP_DIR}"
chmod -R ug+rwx "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}.conf"
cat >"${NGINX_CONF}" <<EOF
server {
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        # auto-detect php-fpm socket
        set \$sock /run/php/$(ls /run/php | grep -E 'php.*fpm.*sock' | head -n1);
        fastcgi_pass unix:\$sock;
    }

    location ~ /\. { deny all; }
}
EOF

ln -sf "${NGINX_CONF}" "/etc/nginx/sites-enabled/${DOMAIN}.conf"
nginx -t
systemctl enable nginx || true
systemctl enable php-fpm || true
systemctl restart php-fpm
systemctl reload nginx

echo "Bootstrap complete. Edit .env with DB credentials, then run:"
echo "  cd ${APP_DIR} && php artisan migrate --force"