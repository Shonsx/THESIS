set -euo pipefail
CONF=/etc/nginx/sites-enabled/etry.conf
mkdir -p /root/nginx_backups
cp "$CONF" "/root/nginx_backups/$(basename $CONF).$(date +%s).bak"

# Ensure no stray backups in sites-enabled
rm -f /etc/nginx/sites-enabled/*.bak.* || true

# Remove any existing /files location blocks to avoid duplicates
awk '/location \^~ \/files\//, /^\s*\}/ {next} {print}' "$CONF" > /tmp/etry.conf.nofiles

# Insert a correct sibling /files block right after the location / block closes
awk 'BEGIN{inloc=0; depth=0; inserted=0}
{
  line=$0;
  if (inserted==0 && line ~ /^\s*location \/ \{/ ) { inloc=1; depth=1; print line; next }
  if (inloc==1) {
    print line;
    # update depth for braces in this line
    n_open=gsub(/{/,"{"); n_close=gsub(/}/,"}"); depth += n_open - n_close;
    if (depth==0) {
      print "    location ^~ /files/ {";
      print "        try_files $uri $uri/ /index.php?$query_string;";
      print "    }";
      inloc=0; inserted=1;
    }
    next;
  }
  print line;
}
END{if(inserted==0){print "# WARN: did not insert /files block"}}
' /tmp/etry.conf.nofiles > /tmp/etry.conf.new

mv /tmp/etry.conf.new "$CONF"

echo "=== show resulting conf (first 130 lines) ==="
nl -ba "$CONF" | sed -n '1,130p'

nginx -t && systemctl reload nginx

IMG=$(ls -1 /var/www/etry/storage/app/public/products | head -n1 || true)
if [ -n "$IMG" ]; then
  echo "Testing URL: https://cspot-etry.com/files/products/$IMG";
  curl -I --max-time 10 https://cspot-etry.com/files/products/$IMG || true;
fi