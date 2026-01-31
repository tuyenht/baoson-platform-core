# BaoSon Platform Core – Setup Guide (Core Seed)

## Requirements
- PHP 8.2+ (see `apps/control-plane/composer.json` constraint)
- Composer
- Node.js 18+
- pnpm
- SQLite (local dev)
- Windows 10 Pro (Developer Mode ON)

## 1. Install dependencies
Run at repository root:

    pnpm install

Install PHP dependencies (ONLY for control-plane):

    cd apps/control-plane
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate
    php artisan db:seed

### Quick verify (local/testing)
Seed sẽ tạo sẵn 4 user để verify nhanh (chỉ `local`/`testing`):
- `super_admin@example.com` / `password`
- `admin@example.com` / `password`
- `editor@example.com` / `password`
- `viewer@example.com` / `password`

> Production: không dùng seed user mặc định, dùng `php artisan rbac:bootstrap-super-admin ...` (xem `docs/rbac.md`).

### Ensure core env keys exist (.env / .env.example)
Vi `.env` thuong bi ignore, repo cung cap script de **tu them day du key** (idempotent):

    cd apps/control-plane
    powershell -ExecutionPolicy Bypass -File .\update-env-core.ps1

Danh sách key + note theo môi trường:
- `apps/control-plane/ENV_CORE_VARIABLES.txt`

### Dọn dẹp khoá .env (xoá trùng / xoá thừa)
Neu `.env` cua ban bi trung khoa hoac co khoa thua (khong con dung), chay script sau (script se **backup truoc khi xoa**):

    cd apps/control-plane
    powershell -ExecutionPolicy Bypass -File .\cleanup-env.ps1

### Sắp xếp .env theo nhóm (khuyến nghị)
De `.env` duoc nhom gon gang theo tieu chi (ghi chu tieng Viet KHONG DAU + goi y theo moi truong), chay:

    cd apps/control-plane
    powershell -ExecutionPolicy Bypass -File .\format-env.ps1

### Security maintenance (recommended)
Audit retention (prune old audit logs):

    cd apps/control-plane
    php artisan audit:prune

## 1.1 Environment strategy (no manual switching)
This repo is designed so bạn **không cần đổi thủ công** `SESSION_DRIVER` / `CACHE_STORE` mỗi khi đổi môi trường.

### Auto-defaults (the “matrix chuẩn nhất”)
Driver mặc định được chọn theo `APP_ENV` + `REDIS_ENABLED`:

- **local/development**: session=`file`, cache=`file`
- **testing**: session=`array`, cache=`array`
- **staging/production**:
  - nếu `REDIS_ENABLED=true` → session=`redis`, cache=`redis`
  - nếu `REDIS_ENABLED` không set/false → **fallback** session=`file`, cache=`file`

Code thực thi:
- `apps/control-plane/config/session.php`
- `apps/control-plane/config/cache.php`

### What you must set per environment
- **Always set `APP_ENV`** đúng cho môi trường (local/testing/staging/production).
- **Chỉ bật Redis khi có Redis**:
  - `REDIS_ENABLED=true` (staging/prod có Redis)
  - không set hoặc `false` (staging/prod chưa có Redis)

### Cookie hardening (recommended)
Trong `.env` (đặc biệt staging/prod):
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax` (khuyến nghị; vẫn tương thích OAuth redirect)
- `SESSION_SECURE_COOKIE=true` khi chạy HTTPS

## 1.2 Tenancy (future-ready, optional)
Mặc định repo chạy **single-tenant**. Nếu sau này cần multi-tenant, core đã có sẵn “tenant context” (tắt mặc định).

- Enable:
  - `TENANCY_ENABLED=true`
  - `TENANCY_STRATEGY=subdomain` (mặc định) hoặc `TENANCY_STRATEGY=header`
  - Nếu dùng header: `TENANCY_HEADER=X-Tenant`
- Data model tối thiểu:
  - Bảng `tenants` (migration: `apps/control-plane/database/migrations/2025_12_22_000003_create_tenants_table.php`)
- Middleware:
  - `App\Http\Middleware\ResolveTenant` sẽ resolve tenant và gắn context cho request.

> Khi bật Spatie `teams` (RBAC multi-tenant), middleware sẽ tự set team id cho PermissionRegistrar theo tenant id.

## 2. Development
Run admin (Inertia + React):

    pnpm dev

Laravel serve (optional):

    cd apps/control-plane
    php artisan serve

## 3. Common commands
From root:

    pnpm dev                  # Dev admin UI
    pnpm build                # Build admin assets
    pnpm lint                 # Frontend lint (ESLint)
    pnpm typecheck            # Frontend typecheck (tsc --noEmit)
    pnpm cp:migrate           # Run migrations
    pnpm cp:seed              # Seed database
    pnpm cp:test              # Run Laravel tests
    pnpm clean                # Clean node_modules & vendor

Control-plane health check:

    cd apps/control-plane
    php artisan core:doctor

PHP formatting/lint (control-plane):

    cd apps/control-plane
    composer run lint         # Pint --test
    composer run format       # Pint (auto-fix)

Audit log (security events):

    cd apps/control-plane
    php artisan migrate
    # Query latest audit events
    php artisan tinker
    >>> \App\Models\AuditLog::query()->latest('occurred_at')->limit(20)->get(['occurred_at','event','actor_id','actor_email','ip'])->toArray();

## 4. Important rules
- NEVER run composer at repository root
- NEVER run php artisan at repository root
- Always use pnpm at root for JS tasks
- Laravel lives in apps/control-plane

This repository is the CORE SEED for BaoSon SaaS Platform.
