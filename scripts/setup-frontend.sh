#!/usr/bin/env bash
set -euo pipefail

echo "=== Frontend setup ==="

# Install node dependencies (use npm ci if package-lock.json exists)
if [ -f package-lock.json ]; then
  echo "Installing node deps with npm ci (lockfile found)"
  npm ci
else
  echo "Installing node deps with npm install"
  npm install
fi

echo ""
echo "=== Setup complete ==="
echo "Run 'npm run dev' to start the Vite dev server (HMR)"
echo "Run 'npm run build' to build for production"
