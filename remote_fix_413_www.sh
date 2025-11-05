#!/usr/bin/env bash
set -euo pipefail

# Fix 413 Request Entity Too Large by:
# - Ensuring server blocks include both cspot-etry.com and www.cspot-etry.com
# - Setting client_max_body_size at both http level and server level (20M)
# - Bumping PHP upload_max_filesize and post_max_size to 20M for FPM
# - Reloading Nginx and PHP-FPM if present
# - Performing quick checks to confirm config is active

WWW_HOST="www.cspot-etry.com"
ROOT_HOST="cspot-etry.com"
NGINX_CONF_DIR="/etc/nginx"
SITES_AVAILABLE="$NGINX_CONF_DIR/sites-available"
SITES_ENABLED="$NGINX_CONF_DIR/sites-enabled"
TARGET_CONF="$SITES_AVAILABLE/etry.conf"

PHP_VERSIONS=("8.3" "8.2" "8.1")

echo "[+] Detecting Nginx config files…"
if [[ ! -d "$NGINX_CONF_DIR" ]]; then
  echo "[-] Nginx dir not found: $NGINX_CONF_DIR" >&2
  exit 1
fi

# 1) Ensure global http client_max_body_size 20M
GLOBAL_CONF="$NGINX_CONF_DIR/nginx.conf"
if grep -q "http\s*{" "$GLOBAL_CONF"; then
  if grep -q "client_max_body_size" "$GLOBAL_CONF"; then
    sudo sed -i 's/\(client_max_body_size\s*\)\S\+/\120M/g' "$GLOBAL_CONF"
  else
    sudo awk 'BEGIN{printit=1}
      /http\s*\{/ {print; print "    client_max_body_size 20M;"; printit=0; next}
      {print}
      END{}' "$GLOBAL_CONF" | sudo tee "$GLOBAL_CONF.tmp" >/dev/null
    sudo mv "$GLOBAL_CONF.tmp" "$GLOBAL_CONF"
  fi
  echo "[+] Set global client_max_body_size 20M in nginx.conf"
else
  echo "[!] Could not find http block in nginx.conf; skipping global set"
fi

# 2) Ensure site config exists; if not, attempt to locate by server_name
if [[ ! -f "$TARGET_CONF" ]]; then
  echo "[!] $TARGET_CONF not found. Attempting to locate vhost by server_name…"
  TARGET_CONF=$(sudo nginx -T 2>/dev/null | awk '/server_name/ && /cspot-etry.com/ {print file} {file=$0}' RS='\n\n' | head -n1)
  if [[ -z "$TARGET_CONF" ]]; then
    echo "[-] Could not auto-detect vhost for domain. Please create or provide path." >&2
    exit 1
  fi
  echo "[+] Detected vhost: $TARGET_CONF"
fi

# 3) Ensure server_name includes both root and www
if grep -q "server_name" "$TARGET_CONF"; then
  sudo sed -i "s/server_name[[:space:]]\+.*;/server_name $ROOT_HOST $WWW_HOST;\/" "$TARGET_CONF"
  echo "[+] Updated server_name to include $ROOT_HOST and $WWW_HOST"
else
  # Insert server_name into first server block
  sudo awk 'BEGIN{inserted=0}
    /server\s*\{/ {
      print; if (!inserted){print "    server_name cspot-etry.com www.cspot-etry.com;"; inserted=1; next}
    }
    {print}
  ' "$TARGET_CONF" | sudo tee "$TARGET_CONF.tmp" >/dev/null
  sudo mv "$TARGET_CONF.tmp" "$TARGET_CONF"
  echo "[+] Inserted server_name into server block"
fi

# 4) Ensure server-level client_max_body_size 20M
if grep -q "client_max_body_size" "$TARGET_CONF"; then
  sudo sed -i 's/\(client_max_body_size\s*\)\S\+/\120M/g' "$TARGET_CONF"
else
  sudo awk 'BEGIN{added=0}
    /server\s*\{/ {print; if (!added){print "    client_max_body_size 20M;"; added=1; next}}
    {print}
  ' "$TARGET_CONF" | sudo tee "$TARGET_CONF.tmp" >/dev/null
  sudo mv "$TARGET_CONF.tmp" "$TARGET_CONF"
fi
echo "[+] Ensured server-level client_max_body_size 20M"

# 5) Link into sites-enabled if missing
if [[ -f "$TARGET_CONF" ]]; then
  BASENAME=$(basename "$TARGET_CONF")
  if [[ ! -f "$SITES_ENABLED/$BASENAME" ]]; then
    sudo ln -sf "$TARGET_CONF" "$SITES_ENABLED/$BASENAME"
    echo "[+] Linked $BASENAME into sites-enabled"
  fi
fi

# 6) Update PHP limits
for ver in "${PHP_VERSIONS[@]}"; do
  INI="/etc/php/$ver/fpm/php.ini"
  if [[ -f "$INI" ]]; then
    sudo sed -i 's/^\s*upload_max_filesize\s*=.*/upload_max_filesize = 20M/g' "$INI"
    sudo sed -i 's/^\s*post_max_size\s*=.*/post_max_size = 20M/g' "$INI"
    echo "[+] Updated PHP $ver FPM upload/post limits"
  fi
done

# 7) Test and reload Nginx
sudo nginx -t
sudo systemctl reload nginx || sudo service nginx reload || sudo /etc/init.d/nginx reload || true
echo "[+] Nginx reloaded"

# 8) Reload PHP-FPM if present
for ver in "${PHP_VERSIONS[@]}"; do
  if systemctl list-units --type=service | grep -q "php$ver-fpm"; then
    sudo systemctl reload "php$ver-fpm" || sudo systemctl restart "php$ver-fpm" || true
    echo "[+] Reloaded php$ver-fpm"
  fi
done

# 9) Quick verification: show active server blocks for domain and limit values
echo "[i] Active server blocks for domain:" 
sudo nginx -T 2>/dev/null | awk '/server_name/ && /cspot-etry.com|www.cspot-etry.com/ {print NR ":" $0}' | sed -n '1,10p'

echo "[i] Checking for client_max_body_size occurrences:" 
sudo nginx -T 2>/dev/null | grep -n "client_max_body_size" | sed -n '1,10p'

echo "[i] Functional check (expect 419 or 302, not 413):"
curl -sk -X POST https://$ROOT_HOST/checkout/process -d 'fake=1' -I | head -n 20 || true
curl -sk -X POST https://$WWW_HOST/checkout/process -d 'fake=1' -I | head -n 20 || true

echo "[+] Done. If you still see 413 on www, ensure DNS points to this server and no upstream proxy/CDN is enforcing a lower limit."