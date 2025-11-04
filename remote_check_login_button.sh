set -euo pipefail
cd /var/www/etry

# Show current Blade line to confirm Login button classes
FILE=resources/views/components/layout.blade.php
if [ -f "$FILE" ]; then
  echo "== snippet around Login button =="
  nl -ba "$FILE" | sed -n '120,200p' | grep -n "href=\"/login\"" -n -A2 -B4 || sed -n '120,200p' "$FILE"
else
  echo "Blade file not found at $FILE"
fi

# Check manifest timestamp
ls -l public/build/manifest.json || echo "No Vite manifest present"