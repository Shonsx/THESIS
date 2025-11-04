set -euo pipefail

CONF=$(grep -l -R "server_name.*cspot-etry.com" /etc/nginx/sites-enabled /etc/nginx/sites-available 2>/dev/null | head -n1 || true)
if [ -z "$CONF" ]; then
  CONF=$(ls /etc/nginx/sites-enabled/*.conf 2>/dev/null | head -n1 || true)
fi
if [ -z "$CONF" ]; then echo "No nginx conf found"; exit 1; fi

echo "Using conf: $CONF"

if grep -q "location ^~ /files/" "$CONF"; then
  echo "location for /files exists; leaving as-is";
else
  echo "Adding explicit /files location to route to Laravel";
  cp "$CONF" "$CONF.bak.$(date +%s)";
  awk 'BEGIN{added=0}
    {print}
    $0 ~ /^\s*location \/ \{/ && added==0 {print "    location ^~ /files/ {"; print "        try_files $uri $uri/ /index.php?$query_string;"; print "    }"; added=1}
  ' "$CONF" > /tmp/etry.conf.new;
  mv /tmp/etry.conf.new "$CONF";
fi

echo "Testing nginx configuration";
nginx -t;

echo "Reloading nginx";
systemctl reload nginx;

IMG=$(ls -1 /var/www/etry/storage/app/public/products | head -n1 || true)
if [ -n "$IMG" ]; then
  echo "Testing URL: https://cspot-etry.com/files/products/$IMG";
  curl -I --max-time 10 https://cspot-etry.com/files/products/$IMG || true;
fi