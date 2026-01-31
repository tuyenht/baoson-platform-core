Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Get-EnvKeysFromFile {
  param([Parameter(Mandatory=$true)][string]$Path)
  if (!(Test-Path $Path)) { return @() }

  $keys = New-Object System.Collections.Generic.HashSet[string]
  $content = Get-Content $Path -Raw
  foreach ($line in ($content -split "`r?`n")) {
    $t = $line.Trim()
    if ($t -eq "" -or $t.StartsWith("#")) { continue }
    if ($t -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=') {
      [void]$keys.Add($Matches[1])
    }
  }
  return $keys
}

function Get-EnvKeysFromTextFile {
  param([Parameter(Mandatory=$true)][string]$Path)
  if (!(Test-Path $Path)) { return @() }

  $keys = New-Object System.Collections.Generic.HashSet[string]
  $content = Get-Content $Path -Raw
  foreach ($line in ($content -split "`r?`n")) {
    $t = $line.Trim()
    if ($t -eq "" -or $t.StartsWith("#")) { continue }
    if ($t -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=') {
      [void]$keys.Add($Matches[1])
    }
  }
  return $keys
}

function Merge-KeySets {
  param(
    [Parameter(Mandatory=$true)]$Sets
  )
  $merged = New-Object System.Collections.Generic.HashSet[string]
  foreach ($s in $Sets) {
    if ($null -eq $s) { continue }
    foreach ($k in $s) { [void]$merged.Add($k) }
  }
  return $merged
}

function Normalize-CoreBlockComments {
  param([Parameter(Mandatory=$true)][string]$Content)

  # Nếu trước đó có block tiếng Anh cũ, thay bằng block tiếng Việt có dấu.
  $content2 = $Content
  $content2 = $content2 -replace "(?ms)^#\s*Core platform keys \(RBAC/2FA/Perf/Tenancy\).*?(?=^\S|\z)", ""
  return $content2.TrimEnd() + "`r`n"
}

function Cleanup-EnvFile {
  param(
    [Parameter(Mandatory=$true)][string]$Path,
    [Parameter(Mandatory=$true)]$AllowedKeys
  )

  $raw = Get-Content $Path -Raw
  $raw = Normalize-CoreBlockComments -Content $raw

  $lines = $raw -split "`r?`n"

  # Map key -> last line index for KEY= entries
  $lastIndex = @{}
  for ($i = 0; $i -lt $lines.Length; $i++) {
    $line = $lines[$i]
    if ($line -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=') {
      $k = $Matches[1]
      $lastIndex[$k] = $i
    }
  }

  $removedDuplicates = New-Object System.Collections.Generic.List[string]
  $removedUnknown = New-Object System.Collections.Generic.List[string]

  $out = New-Object System.Collections.Generic.List[string]
  for ($i = 0; $i -lt $lines.Length; $i++) {
    $line = $lines[$i]

    if ($line -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=') {
      $k = $Matches[1]

      # Remove duplicates (keep last)
      if ($lastIndex.ContainsKey($k) -and $lastIndex[$k] -ne $i) {
        $removedDuplicates.Add($k) | Out-Null
        continue
      }

      # Remove unknown keys (not in allow-set)
      if ($AllowedKeys -and -not $AllowedKeys.Contains($k)) {
        $removedUnknown.Add($k) | Out-Null
        continue
      }
    }

    $out.Add($line) | Out-Null
  }

  # Backup
  $ts = Get-Date -Format "yyyyMMdd-HHmmss"
  $backup = "$Path.bak-$ts"
  Copy-Item -Path $Path -Destination $backup -Force

  # Write back
  $final = ($out -join "`r`n").TrimEnd() + "`r`n"
  Set-Content -Path $Path -Value $final -NoNewline -Encoding UTF8

  return @{
    backup = $backup
    removed_duplicates = ($removedDuplicates | Select-Object -Unique)
    removed_unknown = ($removedUnknown | Select-Object -Unique)
  }
}

Write-Host "Dọn dẹp .env / .env.example (xoá khoá trùng, xoá khoá thừa, backup trước khi xoá)."
Write-Host "Lưu ý: comment sẽ ưu tiên tiếng Việt có dấu để tránh lỗi font."
Write-Host ""

if (!(Test-Path ".env")) { throw "Không tìm thấy .env. Hãy chạy script trong thư mục apps/control-plane." }
if (!(Test-Path ".env.example")) { throw "Không tìm thấy .env.example. Hãy chạy script trong thư mục apps/control-plane." }

$allowed = Merge-KeySets -Sets @(
  (Get-EnvKeysFromFile -Path ".env.example"),
  (Get-EnvKeysFromTextFile -Path "ENV_OAUTH_VARIABLES.txt"),
  (Get-EnvKeysFromTextFile -Path "ENV_CORE_VARIABLES.txt")
)

$r1 = Cleanup-EnvFile -Path ".env" -AllowedKeys $allowed
$r2 = Cleanup-EnvFile -Path ".env.example" -AllowedKeys $allowed

Write-Host "Đã backup:"
Write-Host " - .env -> $($r1.backup)"
Write-Host " - .env.example -> $($r2.backup)"
Write-Host ""

Write-Host "Khoá bị xoá do trùng (giữ lại dòng cuối):"
Write-Host (" - " + (($r1.removed_duplicates + $r2.removed_duplicates | Sort-Object -Unique) -join ", "))
Write-Host ""

Write-Host "Khoá bị xoá do thừa (không có trong .env.example / ENV_OAUTH_VARIABLES.txt / ENV_CORE_VARIABLES.txt):"
Write-Host (" - " + (($r1.removed_unknown + $r2.removed_unknown | Sort-Object -Unique) -join ", "))
Write-Host ""

Write-Host "Hoàn tất."


