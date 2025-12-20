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
- [ ] Dependencies are up to date
- [ ] Security headers are configured
- [ ] Rate limiting is enabled
- [ ] Logging is configured for security events
- [ ] Backup strategy is in place

## Updates

Security updates will be released as patches. Always update to the latest patch version when available.

