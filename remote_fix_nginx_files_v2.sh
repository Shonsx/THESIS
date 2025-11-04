set -euo pipefail
CONF=/etc/nginx/sites-enabled/etry.conf
cp "$CONF" "$CONF.edit.bak.$(date +%s)"

# Remove any wrongly nested /files location blocks
awk '/location \^~ \/files\//, /^\s*\}/ {next} {print}' "$CONF" > /tmp/etry.conf.step1

# Insert correct /files block before the static assets regex location
awk 'BEGIN{inserted=0}
{
  if ($0 ~ /location ~\* \\.(jpg\|jpeg\|png\|gif\|ico\|css\|js\|svg\|webp)\$/ && inserted==0) {
    print "    location ^~ /files/ {";
    print "        try_files $uri $uri/ /index.php?$query_string;";
    print "    }";
    inserted=1;
  }
  print
}
END{if(inserted==0){# fallback: append at end of server block before closing
}}
' /tmp/etry.conf.step1 > /tmp/etry.conf.step2

mv /tmp/etry.conf.step2 "$CONF"

echo "=== preview lines around /files and assets regex ==="
nl -ba "$CONF" | sed -n '1,120p'

nginx -t && systemctl reload nginx || (echo "nginx test failed"; exit 1)

IMG=$(ls -1 /var/www/etry/storage/app/public/products | head -n1 || true)
if [ -n "$IMG" ]; then
  echo "Testing URL: https://cspot-etry.com/files/products/$IMG";
  curl -I --max-time 10 https://cspot-etry.com/files/products/$IMG || true;
fi