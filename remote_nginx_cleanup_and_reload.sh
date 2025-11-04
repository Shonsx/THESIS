set -euo pipefail

# Clean up incorrectly placed backups inside sites-enabled
for f in /etc/nginx/sites-enabled/*.bak.*; do
  [ -e "$f" ] || continue
  echo "Removing stray backup: $f"
  rm -f "$f"
done

echo "Testing nginx configuration after cleanup";
nginx -t;

echo "Reloading nginx";
systemctl reload nginx;

IMG=$(ls -1 /var/www/etry/storage/app/public/products | head -n1 || true)
if [ -n "$IMG" ]; then
  echo "Testing URL: https://cspot-etry.com/files/products/$IMG";
  curl -I --max-time 10 https://cspot-etry.com/files/products/$IMG || true;
fi