# Security Policy

## Supported Versions

We actively support and provide security updates for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability, please report it responsibly:

1. **Do NOT** create a public GitHub issue
2. Email security details to: [security@baoson.com]
3. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

We will acknowledge receipt within 48 hours and provide a timeline for resolution.

## Security Best Practices

### Environment Variables

- Never commit `.env` files
- Use strong, unique values for `APP_KEY`
- Rotate secrets regularly in production

### Dependencies

- Keep all dependencies up to date
- Review security advisories regularly
- Use `composer audit` and `pnpm audit` regularly

### Database

- Use parameterized queries (Laravel Eloquent handles this)
- Never expose database credentials
- Use connection encryption in production

### Authentication

- Implement rate limiting on login endpoints
- Use secure session configuration
- Enable CSRF protection (Laravel default)
- Enable 2FA (TOTP) for privileged accounts (recommended)

### Project-specific security features (already implemented)

- **RBAC (roles/permissions)**: backend enforcement (middleware `can:` + policies), UI guard (Inertia shared `auth.permissions`).
  - Docs: `docs/rbac.md`
- **2FA (TOTP + recovery codes)**: sau bước email+password sẽ chuyển sang OTP challenge nếu user bật 2FA.
  - Settings: `/bsadm/security/two-factor`
  - Challenge: `/bsadm/two-factor/challenge`
- **Server-side password pepper (optional)**:
  - Env: `AUTH_PASSWORD_PEPPER`
  - Notes: đổi pepper sẽ làm invalid toàn bộ password hiện có nếu không có chiến lược rotate.
- **Session/Cache auto-default theo môi trường**:
  - `APP_ENV` + `REDIS_ENABLED` tự chọn driver (fallback file nếu chưa có Redis).
  - Tham chiếu: `apps/control-plane/config/session.php`, `apps/control-plane/config/cache.php`
- **Audit logging (security events)**:
  - Bảng: `audit_logs` (migration: `apps/control-plane/database/migrations/2025_12_22_000002_create_audit_logs_table.php`)
  - Ghi event chính: login/logout, OAuth, 2FA enable/confirm/challenge, rate-limit.
  - Mục tiêu: trace sự cố/auth issue nhanh, phục vụ compliance.
- **Request correlation id**:
  - Middleware: `App\Http\Middleware\LogContext` sẽ gắn header `X-Request-Id` + log context (`request_id`, `user_id`, `route`, `ip`).
  - Khi debug production: lấy `X-Request-Id` từ response và grep log theo id này.

### Scheduled maintenance (recommended)
- Audit retention:
  - Configure: `AUDIT_LOG_RETENTION_DAYS`
  - Command: `php artisan audit:prune`
  - Scheduler: configured daily in `apps/control-plane/routes/console.php` (requires running Laravel scheduler in production)

- Scheduler setup (production):
  - Linux (cron): chạy mỗi phút:
    - `* * * * * cd /path/to/apps/control-plane && php artisan schedule:run >> /dev/null 2>&1`
  - Windows (Task Scheduler): tạo task chạy mỗi 1 phút:
    - Program: `php`
    - Arguments: `artisan schedule:run`
    - Start in: `D:\baoson-platform-core\apps\control-plane`
  - Verify:
    - `php artisan core:doctor` → mục `scheduler.heartbeat` phải “fresh”

### API Security

- Implement proper authentication/authorization
- Use HTTPS in production
- Validate and sanitize all inputs
- Implement rate limiting

## Security Checklist

Before deploying to production:

- [ ] All environment variables are set securely
- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials are secure
- [ ] HTTPS is enabled
- [ ] `SESSION_SECURE_COOKIE=true` (khi HTTPS)
- [ ] `AUTH_PASSWORD_PEPPER` được set qua secret manager (nếu dùng pepper)
- [ ] Bật 2FA cho tài khoản admin/super admin
- [ ] Dependencies are up to date
- [ ] Security headers are configured
- [ ] Rate limiting is enabled
- [ ] Logging is configured for security events
- [ ] Audit logs are stored/retained (audit_logs table + retention policy)
- [ ] Backup strategy is in place

## Updates

Security updates will be released as patches. Always update to the latest patch version when available.

