set -euo pipefail
CONF=/etc/nginx/sites-enabled/etry.conf

# Insert /files block before the static assets regex location using sed
if ! grep -q "location ^~ /files/" "$CONF"; then
  echo "Inserting /files location via sed"
  sed -i '/location ~\* \\.(jpg\\|jpeg\\|png\\|gif\\|ico\\|css\\|js\\|svg\\|webp)\$/i \\    location ^~ /files/ {\n\\        try_files \\$uri \\$uri/ /index.php?\\$query_string;\n\\    }' "$CONF"
fi

nl -ba "$CONF" | sed -n '1,130p'
nginx -t && systemctl reload nginx

IMG=$(ls -1 /var/www/etry/storage/app/public/products | head -n1 || true)
if [ -n "$IMG" ]; then
  echo "Testing URL: https://cspot-etry.com/files/products/$IMG";
  curl -I --max-time 10 https://cspot-etry.com/files/products/$IMG || true;
fi