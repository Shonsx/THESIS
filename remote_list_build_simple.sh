set -e
APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"
echo "== public/build contents =="
ls -l public/build || true
echo "== manifest timestamp =="
ls -l public/build/manifest.json || true