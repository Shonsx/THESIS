set -euo pipefail
cd /var/www/etry

FILE=resources/views/components/layout.blade.php
if [ -f "$FILE" ]; then
  echo "== Login button snippet =="
  nl -ba "$FILE" | sed -n '140,180p' | grep -n "href=\"/login\"" -n -A2 -B4 || sed -n '140,180p' "$FILE"
else
  echo "Blade file not found at $FILE"
fi

ls -l public/build/manifest.json || echo "No Vite manifest present"