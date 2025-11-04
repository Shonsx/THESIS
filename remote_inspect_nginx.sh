set -e

echo "== find nginx conf by server_name"
CONF=$(grep -l -R "server_name.*cspot-etry.com" /etc/nginx/sites-enabled /etc/nginx/sites-available 2>/dev/null | head -n1 || true)
if [ -z "$CONF" ]; then
  CONF=$(ls /etc/nginx/sites-enabled/*.conf 2>/dev/null | head -n1 || true)
fi
if [ -z "$CONF" ]; then
  echo "No nginx conf found"; exit 1
fi

echo "Using conf: $CONF"

echo "== first 200 lines of conf"
sed -n '1,200p' "$CONF"

echo "== any /files locations?"
grep -nE "location[^
]* /files" -n "$CONF" || true

echo "== root and index"
grep -nE "root |index " "$CONF" || true

echo "== public/storage symlink status"
ls -l /var/www/etry/public/storage || true

echo "== list some product images"
ls -l /var/www/etry/storage/app/public/products | head -n 10 || true