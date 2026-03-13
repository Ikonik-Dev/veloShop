<# PowerShell setup script for frontend dependencies #>
param()

Write-Host "=== Frontend setup ==="

# Install node deps (npm ci if lockfile exists, otherwise npm install)
if (Test-Path -Path "package-lock.json") {
    Write-Host "Installing node deps with npm ci (lockfile found)"
    npm ci
}
else {
    Write-Host "Installing node deps with npm install"
    npm install
}

Write-Host ""
Write-Host "=== Setup complete ==="
Write-Host "Run 'npm run dev' to start the Vite dev server (HMR)"
Write-Host "Run 'npm run build' to build for production"
