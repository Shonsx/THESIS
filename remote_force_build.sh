set -e
APP_DIR="/home/u225827314/domains/cspot-etry.com/public_html"
cd "$APP_DIR"
echo "== Prepare Node =="
export NVM_DIR="$HOME/.nvm"; if [ -s "$NVM_DIR/nvm.sh" ]; then . "$NVM_DIR/nvm.sh"; fi; if command -v nvm >/dev/null 2>&1; then nvm use --lts >/dev/null 2>&1 || true; fi
if ! command -v node >/dev/null 2>&1; then NODE_BIN="$(ls -d "$HOME"/.nvm/versions/node/*/bin 2>/dev/null | tail -n 1)"; if [ -d "$NODE_BIN" ]; then export PATH="$NODE_BIN:$PATH"; fi; fi
node -v; npm -v
echo "== npm ci =="
npm ci --no-audit --no-fund
echo "== npm run build =="
export RAYON_NUM_THREADS=1
export LIGHTNINGCSS_THREADS=1
export NODE_OPTIONS="--max-old-space-size=256"
npm run build
echo "== manifest =="
ls -l public/build/manifest.json