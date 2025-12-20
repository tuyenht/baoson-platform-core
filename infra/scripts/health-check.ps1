Write-Host ""
Write-Host "==============================="
Write-Host " BaoSon Platform Core - HEALTH CHECK"
Write-Host "==============================="
Write-Host ""

$errors = 0
function Fail($msg) {
    Write-Host "[FAIL] $msg" -ForegroundColor Red
    $global:errors++
}
function Pass($msg) {
    Write-Host "[OK]   $msg" -ForegroundColor Green
}

# -----------------------------
# 1. STRUCTURE CHECK
# -----------------------------
if (Test-Path "apps/control-plane") {
    Pass "apps/control-plane exists"
} else {
    Fail "apps/control-plane is missing"
}

if (Test-Path "packages") {
    Pass "packages folder exists"
} else {
    Fail "packages folder is missing"
}

if (Test-Path "infra/scripts") {
    Pass "infra/scripts exists"
} else {
    Fail "infra/scripts is missing"
}

# -----------------------------
# 2. COMPOSER SAFETY
# -----------------------------
if (Test-Path "composer.json") {
    Fail "composer.json FOUND AT ROOT (FORBIDDEN)"
} else {
    Pass "No composer.json at root"
}

if (Test-Path "vendor") {
    Fail "vendor/ FOUND AT ROOT (FORBIDDEN)"
} else {
    Pass "No vendor at root"
}

if (Test-Path "apps/control-plane/composer.json") {
    Pass "composer.json found in control-plane"
} else {
    Fail "composer.json missing in control-plane"
}

if (Test-Path "apps/control-plane/vendor") {
    Pass "vendor exists only in control-plane"
} else {
    Write-Host "[WARN] vendor not found in control-plane (run composer install when needed)" -ForegroundColor Yellow
}

# -----------------------------
# 3. PNPM / NODE CHECK
# -----------------------------
if (Test-Path "node_modules") {
    Pass "root node_modules exists (pnpm workspace)"
} else {
    Write-Host "[WARN] root node_modules missing (run pnpm install)" -ForegroundColor Yellow
}

$pnpmStore = pnpm config get store-dir 2>$null
if ($pnpmStore -like "*pnpm-store*") {
    Pass "pnpm store-dir configured: $pnpmStore"
} else {
    Fail "pnpm store-dir not properly configured"
}

# -----------------------------
# 4. WORKSPACE CONFIG
# -----------------------------
if (Test-Path "pnpm-workspace.yaml") {
    Pass "pnpm-workspace.yaml exists"
} else {
    Fail "pnpm-workspace.yaml missing"
}

if (Test-Path "apps/control-plane/package.json") {
    Pass "control-plane package.json exists"
} else {
    Fail "control-plane package.json missing"
}

# -----------------------------
# 5. ENV SAFETY
# -----------------------------
if (Test-Path "apps/control-plane/.env") {
    Pass ".env exists in control-plane"
} else {
    Write-Host "[WARN] .env missing in control-plane" -ForegroundColor Yellow
}

# -----------------------------
# 6. SUMMARY
# -----------------------------
Write-Host ""
Write-Host "==============================="
if ($errors -eq 0) {
    Write-Host " HEALTH CHECK PASSED ✅" -ForegroundColor Green
    Write-Host " Architecture is CLEAN and SAFE"
} else {
    Write-Host " HEALTH CHECK FAILED ❌" -ForegroundColor Red
    Write-Host " $errors issue(s) detected"
}
Write-Host "==============================="
Write-Host ""

exit $errors
