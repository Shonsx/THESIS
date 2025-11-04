set -euo pipefail
cd /var/www/etry

# Show Git info if repo exists
if [ -d .git ]; then
  echo "== git HEAD and origin =="
  git rev-parse HEAD || true
  git remote -v || true
else
  echo "No .git directory; code is deployed without git checkout"
fi

# Show current Blade line to confirm hover color present on server
FILE=resources/views/components/layout.blade.php
if [ -f "$FILE" ]; then
  echo "== snippet around GCASH link =="
  nl -ba "$FILE" | sed -n '1,80p' | grep -n "route('gcash.index')" -n -A2 -B2 || sed -n '1,120p' "$FILE"
else
  echo "Blade file not found at $FILE"
fi

# Check Node/Vite and manifest
node -v || true
npm -v || true
ls -l public/build/manifest.json || echo "No Vite manifest present"