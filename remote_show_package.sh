set -euo pipefail
APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"
echo "== package.json exists? =="
ls -l package.json || true
echo "== package.json content =="
sed -n '1,120p' package.json || true