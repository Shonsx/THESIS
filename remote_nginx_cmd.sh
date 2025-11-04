set -e
echo "== etry.conf"
if [ -f /etc/nginx/sites-enabled/etry.conf ]; then sed -n '1,200p' /etc/nginx/sites-enabled/etry.conf; else echo 'missing /etc/nginx/sites-enabled/etry.conf'; fi

echo
echo "== grep files in nginx conf"
grep -n files /etc/nginx/sites-enabled/* || true

echo
echo "== public/storage symlink"
ls -l /var/www/etry/public/storage || true

echo
echo "== storage/app/public/products listing"
ls -l /var/www/etry/storage/app/public/products | head -n 10 || true