set -euo pipefail

echo "== Detect nginx server config for cspot-etry.com =="
CONF=$(grep -l -R "server_name.*cspot-etry.com" /etc/nginx/sites-enabled /etc/nginx/sites-available 2>/dev/null | head -n1 || true)
if [ -z "$CONF" ]; then
  CONF=$(ls /etc/nginx/sites-enabled/*.conf 2>/dev/null | head -n1 || true)
fi
if [ -z "$CONF" ]; then echo "No nginx server conf found"; exit 1; fi
echo "Using conf: $CONF"

echo "== Ensure client_max_body_size is set to 20M =="
if grep -q "client_max_body_size" "$CONF"; then
  sed -i 's/\(client_max_body_size\s*\)\S\+/\120M;/g' "$CONF"
else
  # Insert after server_name line inside server block
  sed -i '/server_name .*cspot-etry.com.*/a \    client_max_body_size 20M;' "$CONF" || true
  # Fallback: insert near top of server block
  if ! grep -q "client_max_body_size" "$CONF"; then
    sed -i '/^server {/a \    client_max_body_size 20M;' "$CONF"
  fi
fi

echo "== Preview conf head (lines 1-120) =="
nl -ba "$CONF" | sed -n '1,120p'

echo "== Update PHP upload limits =="
FPM_INI=$(ls /etc/php/*/fpm/php.ini 2>/dev/null | head -n1 || true)
if [ -n "$FPM_INI" ] && [ -f "$FPM_INI" ]; then
  sed -i 's/^\s*upload_max_filesize\s*=.*/upload_max_filesize = 20M/' "$FPM_INI"
  sed -i 's/^\s*post_max_size\s*=.*/post_max_size = 20M/' "$FPM_INI"
  echo "Edited: $FPM_INI"
  grep -E "upload_max_filesize|post_max_size" "$FPM_INI" | sed -n '1,4p'
else
  echo "WARN: php-fpm php.ini not found"
fi

echo "== Test nginx conf and reload =="
nginx -t
systemctl reload nginx

echo "== Restart php-fpm =="
systemctl restart php-fpm || systemctl restart php8.2-fpm || true

echo "== Functional POST tests (expect NOT 413 under 10MB) =="
TEST_URL="https://cspot-etry.com/"
SMALL_CODE=$(head -c 1048576 /dev/zero | curl -s -o /dev/null -w "%{http_code}" -X POST -H "Content-Type: application/octet-stream" --data-binary @- "$TEST_URL" || true)
echo "1MB POST status: $SMALL_CODE"
MEDIUM_CODE=$(head -c 5242880 /dev/zero | curl -s -o /dev/null -w "%{http_code}" -X POST -H "Content-Type: application/octet-stream" --data-binary @- "$TEST_URL" || true)
echo "5MB POST status: $MEDIUM_CODE"

echo "== Done. If still 413, clear caches and recheck. =="