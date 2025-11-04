set -euo pipefail

echo "== Environment =="
whoami
pwd
ls -la ~ || true

echo "== Candidate directories =="
CANDIDATES="
$HOME/public_html
$HOME/www
$HOME/html
$HOME/etry
"
for d in $HOME/domains/*/public_html; do
  echo "$d"
done
for d in $CANDIDATES; do
  if [ -d "$d" ]; then echo "exists: $d"; fi
done

echo "== Searching for artisan within home (depth<=4) =="
find "$HOME" -maxdepth 4 -name artisan -print 2>/dev/null || true

echo "== Searching for git repos =="
find "$HOME" -maxdepth 3 -name .git -type d -print 2>/dev/null || true