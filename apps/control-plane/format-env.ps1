Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Parse-Env {
  param([Parameter(Mandatory=$true)][string]$Path)

  $map = @{}
  $rawLines = @{}  # Luu dong goc (bao gom comment inline) cho cac key dac biet
  $preserveKeys = @("SESSION_DRIVER", "SESSION_SECURE_COOKIE", "CACHE_STORE")
  
  $raw = Get-Content $Path -Raw
  foreach ($line in ($raw -split "`r?`n")) {
    $t = $line.Trim()
    if ($t -eq "" -or $t.StartsWith("#")) { continue }
    if ($t -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)\s*$') {
      $k = $Matches[1]
      $v = (Normalize-EnvValue -Value $Matches[2])
      # Keep last occurrence
      $map[$k] = $v
      # Neu la key can giu nguyen, luu lai dong goc (trim nhung giu comment)
      if ($preserveKeys -contains $k) {
        $rawLines[$k] = $t
      }
    }
  }
  return @{ Map = $map; RawLines = $rawLines }
}

function Normalize-EnvValue {
  param([Parameter(Mandatory=$true)][AllowEmptyString()][string]$Value)

  $v = $Value.Trim()

  # Keep quoted values as-is (may contain #, spaces)
  if (
    ($v.StartsWith('"') -and $v.EndsWith('"')) -or
    ($v.StartsWith("'") -and $v.EndsWith("'"))
  ) {
    return $v
  }

  # Strip inline comments safely: "VALUE   # comment" => "VALUE"
  $idx = $v.IndexOf(' #')
  if ($idx -ge 0) {
    $v = $v.Substring(0, $idx).TrimEnd()
  }

  return $v
}

function Get-Value {
  param(
    [Parameter(Mandatory=$true)]$Map,
    [Parameter(Mandatory=$true)][string]$Key,
    [Parameter(Mandatory=$false)][string]$Default = ""
  )
  if ($Map.ContainsKey($Key)) { return $Map[$Key] }
  return $Default
}

function Add-Line {
  param(
    [Parameter(Mandatory=$true)]$Lines,
    [Parameter(Mandatory=$true)][AllowEmptyString()][string]$Line
  )
  $Lines.Add($Line) | Out-Null
}

function Add-KV {
  param(
    [Parameter(Mandatory=$true)]$Lines,
    [Parameter(Mandatory=$true)]$Map,
    [Parameter(Mandatory=$true)][string]$Key,
    [Parameter(Mandatory=$false)][string]$Default = "",
    [Parameter(Mandatory=$false)]$RawLines = $null
  )
  # Neu co raw line (giu nguyen comment inline), uu tien dung no
  if ($RawLines -ne $null -and $RawLines.ContainsKey($Key)) {
    Add-Line -Lines $Lines -Line $RawLines[$Key]
    return
  }
  # Neu khong, ghi binh thuong
  $val = Get-Value -Map $Map -Key $Key -Default $Default
  Add-Line -Lines $Lines -Line ("{0}={1}" -f $Key, $val)
}

if (!(Test-Path ".env")) { throw "Không tìm thấy .env. Hãy chạy script trong thư mục apps/control-plane." }

$parsed = Parse-Env -Path ".env"
$env = $parsed.Map
$rawLines = $parsed.RawLines

# Dùng danh sách khoá lõi để gợi ý (nếu có)
$coreKeys = @{}
if (Test-Path "ENV_CORE_VARIABLES.txt") {
  foreach ($line in (Get-Content "ENV_CORE_VARIABLES.txt")) {
    $t = $line.Trim()
    if ($t -eq "" -or $t.StartsWith("#")) { continue }
    if ($t -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=') {
      $coreKeys[$Matches[1]] = $true
    }
  }
}

$lines = New-Object System.Collections.Generic.List[string]

Add-Line $lines "# =============================================="
Add-Line $lines "# Tep .env (da duoc sap xep lai bang format-env.ps1)"
Add-Line $lines "# Muc tieu: de doc, de van hanh, co goi y theo moi truong."
Add-Line $lines "# =============================================="
Add-Line $lines ""

# 1) Ung dung
Add-Line $lines "# ========================="
Add-Line $lines "# 1) Ung dung (APP)"
Add-Line $lines "# Goi y:"
Add-Line $lines "# - local: APP_ENV=local, APP_DEBUG=true"
Add-Line $lines "# - staging/prod: APP_ENV=staging|production, APP_DEBUG=false"
Add-Line $lines "# - APP_TIMEZONE nen la ten time zone (vi du: Asia/Ho_Chi_Minh)"
Add-Line $lines "# ========================="
Add-KV $lines $env "APP_NAME" (Get-Value $env "APP_NAME" '"Baoson Control Plane"')
Add-KV $lines $env "APP_ENV" (Get-Value $env "APP_ENV" "local")
Add-KV $lines $env "APP_KEY" (Get-Value $env "APP_KEY" "")
Add-KV $lines $env "APP_DEBUG" (Get-Value $env "APP_DEBUG" "true")
Add-KV $lines $env "APP_URL" (Get-Value $env "APP_URL" "http://localhost:8000")
if ($env.ContainsKey("APP_TIMEZONE")) { Add-KV $lines $env "APP_TIMEZONE" } else { Add-KV $lines $env "APP_TIMEZONE" "Asia/Ho_Chi_Minh" }
Add-Line $lines ""

# 2) Admin prefix
Add-Line $lines "# ========================="
Add-Line $lines "# 2) Admin (prefix + route name)"
Add-Line $lines "# ========================="
Add-KV $lines $env "ADMIN_PREFIX" (Get-Value $env "ADMIN_PREFIX" "bsadm")
Add-KV $lines $env "ADMIN_ROUTE_NAME" (Get-Value $env "ADMIN_ROUTE_NAME" "bsadm.")
Add-Line $lines ""

# 3) Ngon ngu
Add-Line $lines "# ========================="
Add-Line $lines "# 3) Ngon ngu (Locale)"
Add-Line $lines "# ========================="
Add-KV $lines $env "APP_LOCALE" (Get-Value $env "APP_LOCALE" "en")
Add-KV $lines $env "APP_FALLBACK_LOCALE" (Get-Value $env "APP_FALLBACK_LOCALE" "en")
Add-Line $lines ""

# 4) CSDL
Add-Line $lines "# ========================="
Add-Line $lines "# 4) Co so du lieu"
Add-Line $lines "# Goi y:"
Add-Line $lines "# - local (nhanh/don gian): sqlite"
Add-Line $lines "# - staging/prod: mysql/pgsql (tuy ha tang)"
Add-Line $lines "#"
Add-Line $lines "# Huong dan doi nhanh (chi can sua dung 1 block):"
Add-Line $lines "# - SQLITE (local): DB_CONNECTION=sqlite va DB_DATABASE=database/database.sqlite"
Add-Line $lines "# - MYSQL (staging/prod): DB_CONNECTION=mysql va set DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD"
Add-Line $lines "#"
Add-Line $lines "# Sau khi doi DB:"
Add-Line $lines "# 1) php artisan optimize:clear"
Add-Line $lines "# 2) php artisan migrate"
Add-Line $lines "# 3) php artisan core:doctor"
Add-Line $lines "# ========================="
Add-KV $lines $env "DB_CONNECTION" (Get-Value $env "DB_CONNECTION" "sqlite")
Add-KV $lines $env "DB_DATABASE" (Get-Value $env "DB_DATABASE" "database/database.sqlite")
Add-KV $lines $env "DB_HOST" (Get-Value $env "DB_HOST" "127.0.0.1")
Add-KV $lines $env "DB_PORT" (Get-Value $env "DB_PORT" "3306")
Add-KV $lines $env "DB_USERNAME" (Get-Value $env "DB_USERNAME" "root")
Add-KV $lines $env "DB_PASSWORD" (Get-Value $env "DB_PASSWORD" "")
Add-Line $lines "#"
Add-Line $lines "# --- Mau SQLITE (local) ---"
Add-Line $lines "# DB_CONNECTION=sqlite"
Add-Line $lines "# DB_DATABASE=database/database.sqlite"
Add-Line $lines "#"
Add-Line $lines "# --- Mau MYSQL (staging/prod) ---"
Add-Line $lines "# DB_CONNECTION=mysql"
Add-Line $lines "# DB_HOST=127.0.0.1"
Add-Line $lines "# DB_PORT=3306"
Add-Line $lines "# DB_DATABASE=baoson"
Add-Line $lines "# DB_USERNAME=baoson"
Add-Line $lines "# DB_PASSWORD=your-password"
Add-Line $lines "#"
Add-Line $lines "# --- Mau POSTGRESQL (staging/prod) ---"
Add-Line $lines "# DB_CONNECTION=pgsql"
Add-Line $lines "# DB_HOST=127.0.0.1"
Add-Line $lines "# DB_PORT=5432"
Add-Line $lines "# DB_DATABASE=baoson"
Add-Line $lines "# DB_USERNAME=baoson"
Add-Line $lines "# DB_PASSWORD=your-password"
Add-Line $lines ""

# 5) Redis / Cache / Session
Add-Line $lines "# ========================="
Add-Line $lines "# 5) Cache / Session / Redis"
Add-Line $lines "# Goi y:"
Add-Line $lines "# - local: REDIS_ENABLED=false (fallback file)"
Add-Line $lines "# - staging/prod co Redis: REDIS_ENABLED=true"
Add-Line $lines "# ========================="
Add-KV $lines $env "REDIS_ENABLED" (Get-Value $env "REDIS_ENABLED" "false") -RawLines $rawLines
Add-KV $lines $env "CACHE_STORE" (Get-Value $env "CACHE_STORE" "") -RawLines $rawLines
Add-KV $lines $env "SESSION_DRIVER" (Get-Value $env "SESSION_DRIVER" "") -RawLines $rawLines
Add-KV $lines $env "SESSION_LIFETIME" (Get-Value $env "SESSION_LIFETIME" "120")
Add-KV $lines $env "SESSION_SECURE_COOKIE" (Get-Value $env "SESSION_SECURE_COOKIE" "false") -RawLines $rawLines
Add-KV $lines $env "SESSION_HTTP_ONLY" (Get-Value $env "SESSION_HTTP_ONLY" "true")
Add-KV $lines $env "SESSION_SAME_SITE" (Get-Value $env "SESSION_SAME_SITE" "lax")
Add-Line $lines ""

# 6) Bao mat: pepper + login/2FA throttling + audit retention
Add-Line $lines "# ========================="
Add-Line $lines "# 6) Bao mat (pepper / 2FA / audit)"
Add-Line $lines "# ========================="
Add-KV $lines $env "AUTH_PASSWORD_PEPPER" (Get-Value $env "AUTH_PASSWORD_PEPPER" "")
Add-KV $lines $env "AUTH_PASSWORD_PEPPER_PREVIOUS" (Get-Value $env "AUTH_PASSWORD_PEPPER_PREVIOUS" "")
Add-KV $lines $env "AUTH_PASSWORD_PEPPER_REQUIRED" (Get-Value $env "AUTH_PASSWORD_PEPPER_REQUIRED" "false")
Add-KV $lines $env "TWO_FACTOR_CHALLENGE_MAX_ATTEMPTS" (Get-Value $env "TWO_FACTOR_CHALLENGE_MAX_ATTEMPTS" "5")
Add-KV $lines $env "TWO_FACTOR_CHALLENGE_DECAY_SECONDS" (Get-Value $env "TWO_FACTOR_CHALLENGE_DECAY_SECONDS" "60")
Add-KV $lines $env "LOGIN_MAX_ATTEMPTS" (Get-Value $env "LOGIN_MAX_ATTEMPTS" "5")
Add-KV $lines $env "LOGIN_DECAY_SECONDS" (Get-Value $env "LOGIN_DECAY_SECONDS" "60")
Add-KV $lines $env "AUDIT_LOG_RETENTION_DAYS" (Get-Value $env "AUDIT_LOG_RETENTION_DAYS" "90")
Add-Line $lines ""

# 7) Scheduler heartbeat
Add-Line $lines "# ========================="
Add-Line $lines "# 7) Scheduler (de core:doctor kiem tra scheduler co chay)"
Add-Line $lines "# ========================="
Add-KV $lines $env "CORE_SCHEDULER_HEARTBEAT_TTL_SECONDS" (Get-Value $env "CORE_SCHEDULER_HEARTBEAT_TTL_SECONDS" "3600")
Add-KV $lines $env "CORE_SCHEDULER_HEARTBEAT_MAX_AGE_SECONDS" (Get-Value $env "CORE_SCHEDULER_HEARTBEAT_MAX_AGE_SECONDS" "300")
Add-Line $lines ""

# 8) Tenancy
Add-Line $lines "# ========================="
Add-Line $lines "# 8) Tenancy (tat mac dinh; bat khi can multi-tenant)"
Add-Line $lines "# ========================="
Add-KV $lines $env "TENANCY_ENABLED" (Get-Value $env "TENANCY_ENABLED" "false")
Add-KV $lines $env "TENANCY_STRATEGY" (Get-Value $env "TENANCY_STRATEGY" "subdomain")
Add-KV $lines $env "TENANCY_HEADER" (Get-Value $env "TENANCY_HEADER" "X-Tenant")
Add-Line $lines ""

# 9) OAuth (giu nguyen neu co)
Add-Line $lines "# ========================="
Add-Line $lines "# 9) OAuth (Google/Facebook)"
Add-Line $lines "# Goi y: web app thuong de *_STATELESS=false (dung session)."
Add-Line $lines "# Chi bat *_STATELESS=true neu ban thuc su muon stateless (API/SPAs khong dung session)."
Add-Line $lines "# ========================="
foreach ($k in @(
  "AUTH_GOOGLE_ENABLED","GOOGLE_CLIENT_ID","GOOGLE_CLIENT_SECRET","GOOGLE_REDIRECT_URL","GOOGLE_STATELESS",
  "AUTH_FACEBOOK_ENABLED","FACEBOOK_CLIENT_ID","FACEBOOK_CLIENT_SECRET","FACEBOOK_REDIRECT_URL","FACEBOOK_STATELESS"
)) {
  if ($env.ContainsKey($k)) { Add-KV $lines $env $k }
}
Add-Line $lines ""

# 10) Phan con lai: giu lai cac key khac (khong xoa)
Add-Line $lines "# ========================="
Add-Line $lines "# 10) Khac (giu nguyen cac khoa khong thuoc nhom tren)"
Add-Line $lines "# ========================="
$used = New-Object System.Collections.Generic.HashSet[string]
foreach ($line in $lines) {
  if ($line -match '^\s*([A-Za-z_][A-Za-z0-9_]*)=') { [void]$used.Add($Matches[1]) }
}

foreach ($k in ($env.Keys | Sort-Object)) {
  if (-not $used.Contains($k)) {
    Add-Line $lines ("{0}={1}" -f $k, $env[$k])
  }
}

# Backup + write
$ts = Get-Date -Format "yyyyMMdd-HHmmss"
$backup = ".env.bak-format-$ts"
Copy-Item -Path ".env" -Destination $backup -Force

$final = ($lines -join "`r`n").TrimEnd() + "`r`n"
Set-Content -Path ".env" -Value $final -NoNewline -Encoding UTF8

Write-Host "Da sap xep lai .env (backup: $backup)"


