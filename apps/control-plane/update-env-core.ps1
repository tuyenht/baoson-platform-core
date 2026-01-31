Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Upsert-Line {
  param(
    [Parameter(Mandatory=$true)][string]$Content,
    [Parameter(Mandatory=$true)][string]$Key,
    [Parameter(Mandatory=$true)][string]$ValueLine
  )

  $pattern = "(?m)^\s*" + [regex]::Escape($Key) + "\s*=.*$"
  if ($Content -match $pattern) {
    return [regex]::Replace($Content, $pattern, $ValueLine)
  }
  return ($Content.TrimEnd() + "`r`n" + $ValueLine + "`r`n")
}

function Ensure-Block {
  param(
    [Parameter(Mandatory=$true)][string]$Content,
    [Parameter(Mandatory=$true)][string]$Marker,
    [Parameter(Mandatory=$true)][string]$Block
  )

  if ($Content -match [regex]::Escape($Marker)) { return $Content }
  return ($Content.TrimEnd() + "`r`n`r`n" + $Block.Trim() + "`r`n")
}

$files = @(".env", ".env.example")
foreach ($f in $files) {
  if (!(Test-Path $f)) { throw "Missing file: $f (run from apps/control-plane)" }
}

$block = @"
# =========================
# Cac khoa cau hinh loi (RBAC/2FA/Hieu nang/Tenancy)
# Tu dong them boi update-env-core.ps1
# =========================

# Session/Cache: staging/production co Redis thi bat; khong co thi de false/khong set.
REDIS_ENABLED=false

# Password pepper (tuy chon; khuyen nghi production dung secret manager)
AUTH_PASSWORD_PEPPER=
AUTH_PASSWORD_PEPPER_PREVIOUS=
AUTH_PASSWORD_PEPPER_REQUIRED=false

# Tenancy scaffold (tat mac dinh)
TENANCY_ENABLED=false
TENANCY_STRATEGY=subdomain
TENANCY_HEADER=X-Tenant

# 2FA hardening (gioi han thu OTP/recovery)
TWO_FACTOR_CHALLENGE_MAX_ATTEMPTS=5
TWO_FACTOR_CHALLENGE_DECAY_SECONDS=60

# Audit retention
AUDIT_LOG_RETENTION_DAYS=90

# Scheduler heartbeat (do san sang van hanh)
CORE_SCHEDULER_HEARTBEAT_TTL_SECONDS=3600
CORE_SCHEDULER_HEARTBEAT_MAX_AGE_SECONDS=300
"@

foreach ($f in $files) {
  $content = Get-Content $f -Raw

  # Append block once (readable, with notes)
  $content = Ensure-Block -Content $content -Marker "# Cac khoa cau hinh loi (RBAC/2FA/Hieu nang/Tenancy)" -Block $block

  # Ensure keys exist (idempotent)
  $content = Upsert-Line -Content $content -Key "REDIS_ENABLED" -ValueLine "REDIS_ENABLED=false"

  $content = Upsert-Line -Content $content -Key "AUTH_PASSWORD_PEPPER" -ValueLine "AUTH_PASSWORD_PEPPER="
  $content = Upsert-Line -Content $content -Key "AUTH_PASSWORD_PEPPER_PREVIOUS" -ValueLine "AUTH_PASSWORD_PEPPER_PREVIOUS="
  $content = Upsert-Line -Content $content -Key "AUTH_PASSWORD_PEPPER_REQUIRED" -ValueLine "AUTH_PASSWORD_PEPPER_REQUIRED=false"

  $content = Upsert-Line -Content $content -Key "TENANCY_ENABLED" -ValueLine "TENANCY_ENABLED=false"
  $content = Upsert-Line -Content $content -Key "TENANCY_STRATEGY" -ValueLine "TENANCY_STRATEGY=subdomain"
  $content = Upsert-Line -Content $content -Key "TENANCY_HEADER" -ValueLine "TENANCY_HEADER=X-Tenant"

  $content = Upsert-Line -Content $content -Key "TWO_FACTOR_CHALLENGE_MAX_ATTEMPTS" -ValueLine "TWO_FACTOR_CHALLENGE_MAX_ATTEMPTS=5"
  $content = Upsert-Line -Content $content -Key "TWO_FACTOR_CHALLENGE_DECAY_SECONDS" -ValueLine "TWO_FACTOR_CHALLENGE_DECAY_SECONDS=60"

  $content = Upsert-Line -Content $content -Key "AUDIT_LOG_RETENTION_DAYS" -ValueLine "AUDIT_LOG_RETENTION_DAYS=90"

  $content = Upsert-Line -Content $content -Key "CORE_SCHEDULER_HEARTBEAT_TTL_SECONDS" -ValueLine "CORE_SCHEDULER_HEARTBEAT_TTL_SECONDS=3600"
  $content = Upsert-Line -Content $content -Key "CORE_SCHEDULER_HEARTBEAT_MAX_AGE_SECONDS" -ValueLine "CORE_SCHEDULER_HEARTBEAT_MAX_AGE_SECONDS=300"

  Set-Content -Path $f -Value $content -NoNewline -Encoding UTF8
  Write-Host "Updated: $f"
}

Write-Host ""
Write-Host "Current values in .env:"
Select-String -Path .\.env -Pattern '^(REDIS_ENABLED|AUTH_PASSWORD_PEPPER|AUTH_PASSWORD_PEPPER_PREVIOUS|AUTH_PASSWORD_PEPPER_REQUIRED|TENANCY_ENABLED|TENANCY_STRATEGY|TENANCY_HEADER|TWO_FACTOR_CHALLENGE_MAX_ATTEMPTS|TWO_FACTOR_CHALLENGE_DECAY_SECONDS|AUDIT_LOG_RETENTION_DAYS|CORE_SCHEDULER_HEARTBEAT_TTL_SECONDS|CORE_SCHEDULER_HEARTBEAT_MAX_AGE_SECONDS)='


