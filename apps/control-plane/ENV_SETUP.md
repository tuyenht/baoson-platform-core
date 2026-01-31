# Environment Variables Setup

## Quick Setup

### Option 1: Use PowerShell Script (Recommended)
```powershell
cd apps/control-plane
.\add-oauth-env.ps1
```

### Option 2: Manual Setup
Copy the variables from `ENV_OAUTH_VARIABLES.txt` and paste them into your `.env` file.

## Google OAuth Configuration

Add these variables to your `.env` file to enable Google login:

```env
# Google OAuth Configuration
# Get credentials from: https://console.cloud.google.com/apis/credentials
# Note: GOOGLE_REDIRECT_URL is auto-generated from APP_URL if not set
AUTH_GOOGLE_ENABLED=true
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
# GOOGLE_REDIRECT_URL will be auto-generated as: {APP_URL}/{ADMIN_PREFIX}/oauth/google/callback
# Uncomment and set manually if you need a custom redirect URL:
# GOOGLE_REDIRECT_URL=http://localhost:8000/bsadm/oauth/google/callback
GOOGLE_STATELESS=false
```

## Facebook OAuth Configuration

Add these variables to your `.env` file to enable Facebook login:

```env
# Facebook OAuth Configuration
# Get credentials from: https://developers.facebook.com/apps/
# Note: FACEBOOK_REDIRECT_URL is auto-generated from APP_URL if not set
AUTH_FACEBOOK_ENABLED=true
FACEBOOK_CLIENT_ID=your-facebook-app-id-here
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret-here
# FACEBOOK_REDIRECT_URL will be auto-generated as: {APP_URL}/{ADMIN_PREFIX}/oauth/facebook/callback
# Uncomment and set manually if you need a custom redirect URL:
# FACEBOOK_REDIRECT_URL=http://localhost:8000/bsadm/oauth/facebook/callback
FACEBOOK_STATELESS=false
```

## Admin Configuration

```env
# Admin Configuration
ADMIN_PREFIX=bsadm
ADMIN_ROUTE_NAME=bsadm.
```

## Stateless OAuth Explained

### What is Stateless OAuth?

**Stateless OAuth** (`GOOGLE_STATELESS` / `FACEBOOK_STATELESS`) determines whether OAuth authentication uses session storage or not.

### When to use `stateless=true`:

✅ **Use `stateless=true` for:**
- **API applications** (REST APIs, GraphQL APIs)
- **Mobile apps** (React Native, Flutter, etc.)
- **SPA (Single Page Applications)** that don't use server-side sessions
- **Microservices** or serverless architectures
- **When you want to avoid session storage** for performance/scalability

**How it works:** Uses signed URL parameters instead of session to verify OAuth state and prevent CSRF attacks.

### When to use `stateless=false` (Default):

✅ **Use `stateless=false` for:**
- **Traditional web applications** (like this control plane)
- **Applications that use Laravel sessions**
- **When you need session-based authentication**
- **Most standard web apps**

**How it works:** Uses Laravel session to store OAuth state and verify CSRF tokens.

### Current Setup:

For this **web application with session-based authentication**, keep:
```env
GOOGLE_STATELESS=false
FACEBOOK_STATELESS=false
```

This is the **recommended setting** for traditional web applications.

### Change to `true` only if:

- You're building an API that mobile apps will consume
- You're building a SPA that doesn't use server-side sessions
- You have specific requirements to avoid session storage

## Complete Setup Steps

1. **Copy variables to .env file:**
   - Use the PowerShell script: `.\add-oauth-env.ps1`
   - Or copy from `ENV_OAUTH_VARIABLES.txt`

2. **Get OAuth credentials:**
   - Google: https://console.cloud.google.com/apis/credentials
   - Facebook: https://developers.facebook.com/apps/

3. **Update .env file:**
   - Replace `your-google-client-id-here` with your actual Google Client ID
   - Replace `your-google-client-secret-here` with your actual Google Client Secret
   - Replace `your-facebook-app-id-here` with your actual Facebook App ID
   - Replace `your-facebook-app-secret-here` with your actual Facebook App Secret
   - **Redirect URLs are auto-generated** from `APP_URL` and `ADMIN_PREFIX` (no need to set manually)
   - Only set `GOOGLE_REDIRECT_URL` or `FACEBOOK_REDIRECT_URL` if you need a custom URL
   - **Keep `GOOGLE_STATELESS=false` and `FACEBOOK_STATELESS=false`** for web applications

4. **Socialite**
   - `laravel/socialite` **đã có sẵn** trong `apps/control-plane/composer.json`.
   - Nếu bạn vừa thay đổi dependencies, hãy chạy trong **external PowerShell**:
   ```powershell
   cd apps/control-plane
   composer install --no-interaction
   php artisan optimize:clear --no-interaction
   ```

5. **Restart your server**

## Notes

- Replace `http://localhost` with your actual domain in production
- If you run locally with `php artisan serve` default port `8000`, your redirect URI must include `:8000`
- Make sure redirect URLs match exactly in both .env and OAuth provider settings
- Default values: `AUTH_GOOGLE_ENABLED=true` and `AUTH_FACEBOOK_ENABLED=true` (can be set to `false` to disable)
- **Stateless mode**: Only enable if building APIs or SPAs without server-side sessions
