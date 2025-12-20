# Script to add Google and Facebook OAuth environment variables to .env file
# Run this script: .\add-oauth-env.ps1

$envFile = ".env"
$envExampleFile = ".env.example"

# Check if .env file exists
if (-not (Test-Path $envFile)) {
    Write-Host "Creating .env file from .env.example..." -ForegroundColor Yellow
    if (Test-Path $envExampleFile) {
        Copy-Item $envExampleFile $envFile
    } else {
        Write-Host "Error: .env.example not found. Please create .env file manually." -ForegroundColor Red
        exit 1
    }
}

# Read current .env content
$content = Get-Content $envFile -Raw

# OAuth variables to add/update
$oauthVars = @"
# Admin Configuration
ADMIN_PREFIX=bsadm
ADMIN_ROUTE_NAME=bsadm.

# Google OAuth Configuration
# Get credentials from: https://console.cloud.google.com/apis/credentials
# Note: GOOGLE_REDIRECT_URL is auto-generated from APP_URL if not set
AUTH_GOOGLE_ENABLED=true
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
# GOOGLE_REDIRECT_URL will be auto-generated as: {APP_URL}/{ADMIN_PREFIX}/oauth/google/callback
# Uncomment and set manually if you need a custom redirect URL:
# GOOGLE_REDIRECT_URL=http://localhost/bsadm/oauth/google/callback
GOOGLE_STATELESS=false

# Facebook OAuth Configuration
# Get credentials from: https://developers.facebook.com/apps/
# Note: FACEBOOK_REDIRECT_URL is auto-generated from APP_URL if not set
AUTH_FACEBOOK_ENABLED=true
FACEBOOK_CLIENT_ID=your-facebook-app-id-here
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret-here
# FACEBOOK_REDIRECT_URL will be auto-generated as: {APP_URL}/{ADMIN_PREFIX}/oauth/facebook/callback
# Uncomment and set manually if you need a custom redirect URL:
# FACEBOOK_REDIRECT_URL=http://localhost/bsadm/oauth/facebook/callback
FACEBOOK_STATELESS=false
"@

# Check if OAuth variables already exist
if ($content -match "AUTH_GOOGLE_ENABLED" -or $content -match "AUTH_FACEBOOK_ENABLED") {
    Write-Host "OAuth variables already exist in .env file." -ForegroundColor Yellow
    Write-Host "Please update them manually if needed." -ForegroundColor Yellow
} else {
    # Append OAuth variables to .env file
    Add-Content -Path $envFile -Value "`n$oauthVars"
    Write-Host "OAuth environment variables have been added to .env file!" -ForegroundColor Green
    Write-Host "Please update the placeholder values with your actual credentials." -ForegroundColor Yellow
}

Write-Host "`nNext steps:" -ForegroundColor Cyan
Write-Host "1. Get Google OAuth credentials from: https://console.cloud.google.com/apis/credentials" -ForegroundColor White
Write-Host "2. Get Facebook App credentials from: https://developers.facebook.com/apps/" -ForegroundColor White
Write-Host "3. Update GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, FACEBOOK_CLIENT_ID, FACEBOOK_CLIENT_SECRET in .env" -ForegroundColor White
Write-Host "4. Update redirect URLs if your domain is different from http://localhost" -ForegroundColor White

