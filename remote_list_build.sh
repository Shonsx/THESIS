set -euo pipefail
APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"

echo "== Ensure Node/NPM available =="
export NVM_DIR="$HOME/.nvm"
if [ -s "$NVM_DIR/nvm.sh" ]; then . "$NVM_DIR/nvm.sh"; fi
if command -v nvm >/dev/null 2>&1; then nvm use --lts >/dev/null 2>&1 || true; fi
if ! command -v node >/dev/null 2>&1; then
  NODE_BIN="$(ls -d "$HOME"/.nvm/versions/node/*/bin 2>/dev/null | tail -n 1)"
  if [ -d "$NODE_BIN" ]; then export PATH="$NODE_BIN:$PATH"; fi
fi
node -v || true
npm -v || true

echo "== package.json scripts =="
cat package.json | sed -n '1,120p' || true

echo "== Running npm run build (verbose) =="
npm run build --silent || npm run build || true

echo "== public/build contents =="
ls -l public/build || true
echo "== manifest content head =="
head -n 20 public/build/manifest.json || echo "No manifest"