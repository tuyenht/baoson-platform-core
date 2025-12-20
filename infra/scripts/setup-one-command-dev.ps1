# ==========================================
# BaoSon Platform Core - One Command Dev Setup
# - Adds concurrently
# - Updates root package.json so "pnpm dev" runs:
#     1) Vite dev (control-plane)
#     2) Laravel serve (control-plane)
# ==========================================

$ErrorActionPreference = "Stop"

function Read-Json($path) {
  if (!(Test-Path $path)) { throw "Missing file: $path" }
  return (Get-Content $path -Raw) | ConvertFrom-Json
}

function Write-Json($path, $obj) {
  $json = $obj | ConvertTo-Json -Depth 100
  Set-Content -Path $path -Value $json -Encoding UTF8
}

function To-Hashtable($psObj) {
  $ht = @{}
  if ($null -eq $psObj) { return $ht }
  $psObj.PSObject.Properties | ForEach-Object {
    $ht[$_.Name] = $_.Value
  }
  return $ht
}

Write-Host "== BaoSon One Command Dev Setup ==" -ForegroundColor Cyan

# 1) Ensure we're at repo root with package.json
$rootPkgPath = ".\package.json"
if (!(Test-Path $rootPkgPath)) {
  throw "package.json not found at repo root. Run this script from the monorepo root."
}

# 2) Backup package.json
$ts = Get-Date -Format "yyyyMMdd-HHmmss"
$backupPath = ".\package.json.bak-$ts"
Copy-Item $rootPkgPath $backupPath -Force
Write-Host "Backup created: $backupPath" -ForegroundColor Green

# 3) Install concurrently (workspace root dev dependency)
Write-Host "Installing concurrently (dev dependency)..." -ForegroundColor Cyan
pnpm add -wD concurrently

# 4) Load and update root package.json scripts
$rootPkg = Read-Json $rootPkgPath
$rootScripts = To-Hashtable $rootPkg.scripts

# Ensure existing helpers exist (do not break if already present)
if (-not $rootScripts.ContainsKey("dev:control-plane")) {
  $rootScripts["dev:control-plane"] = "pnpm --filter control-plane run dev"
}
if (-not $rootScripts.ContainsKey("cp:serve")) {
  $rootScripts["cp:serve"] = "cd apps/control-plane && php artisan serve"
}

# Set one-command dev (overwrites dev to be the unified dev runner)
$rootScripts["dev"] = "concurrently -n VITE,LARAVEL -c green,cyan `"pnpm dev:control-plane`" `"pnpm cp:serve`""

# Optional convenience:
if (-not $rootScripts.ContainsKey("dev:split")) {
  $rootScripts["dev:split"] = "echo Use two terminals: (1) pnpm dev:control-plane  (2) pnpm cp:serve"
}

$rootPkg.scripts = $rootScripts
Write-Json $rootPkgPath $rootPkg

Write-Host "Root package.json updated successfully." -ForegroundColor Green
Write-Host ""
Write-Host "✅ From now on, you can run ONE command:" -ForegroundColor Green
Write-Host "   pnpm dev" -ForegroundColor White
Write-Host ""
Write-Host "It will start:" -ForegroundColor Cyan
Write-Host " - VITE (control-plane frontend dev server)" -ForegroundColor White
Write-Host " - LARAVEL (php artisan serve)" -ForegroundColor White
Write-Host ""

# 5) Ask user to run dev now (auto-run safe default = no)
$runNow = Read-Host "Run 'pnpm dev' now? (y/n)"
if ($runNow -match "^(y|Y)$") {
  Write-Host "Starting pnpm dev..." -ForegroundColor Cyan
  pnpm dev
} else {
  Write-Host "Done. You can start dev later with: pnpm dev" -ForegroundColor Yellow
}
