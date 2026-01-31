# RBAC (Roles & Permissions) — Baoson Platform Core

Stack: Laravel 12 + Inertia.js + React + TypeScript  
Thư mục app: `apps/control-plane`

## Mục tiêu

- Quyền **DB-driven** (không hardcode trong code enforcement).
- Convention thống nhất để đặt tên permission.
- Enforce **backend là source of truth** (middleware + policies).
- Frontend chỉ là **UI guard** (ẩn/hiện) + route/page guard mềm để tránh “flash”.
- Sẵn đường nâng cấp **multi-tenant** trong tương lai (Spatie “teams”), hiện chạy single-tenant.

## Naming convention (BẮT BUỘC)

Permission name theo format:

- **`<domain>.<resource>.<action>`**

Ví dụ:

- `admin.users.view`
- `admin.users.create`
- `admin.users.update`
- `admin.users.delete`
- `admin.audit.view`

Permission cho UI/menu:

- `admin.menu.dashboard`
- `admin.menu.users`
- `admin.menu.roles`
- `admin.menu.permissions`
- `admin.menu.settings`
- `admin.menu.audit`

## Canonical permissions (nguồn chuẩn)

Tất cả permission canonical nằm trong:

- `apps/control-plane/app/Enums/Permission.php` (**single source of truth**)

Khi thêm permission mới:

1. Thêm case mới vào enum.
2. Chạy sync:
   - `php artisan rbac:sync`
3. Export type-safe permissions cho frontend:
   - `php artisan rbac:export-permissions`

## Sync permissions (deploy-safe)

Lệnh:

- `php artisan rbac:sync`
- `php artisan rbac:sync --assign-super-admin`

## Provisioning helpers (recommended)

### Grant a permission to a role (idempotent)

- `php artisan rbac:grant admin.audit.view --role=admin`

> Tip: chạy `php artisan rbac:sync` trước để đảm bảo permission canonical đã có trong DB.

## Seed roles/permissions (idempotent)

Seeder:

- `apps/control-plane/database/seeders/RbacSeeder.php`

Roles mặc định:

- `super_admin`, `admin`, `editor`, `viewer`

Gợi ý: `super_admin` luôn có toàn quyền (sync tất cả permissions).

Seeder tạo sẵn user để verify nhanh (chỉ `local`/`testing`):

- `super_admin@example.com` / `password` (role: `super_admin`)
- `admin@example.com` / `password` (role: `admin`)
- `editor@example.com` / `password` (role: `editor`)
- `viewer@example.com` / `password` (role: `viewer`)

> Lưu ý:
> - Trong production, **không nên** seed user mặc định. Hãy dùng lệnh bootstrap bên dưới.
> - Dự án có hỗ trợ pepper (`AUTH_PASSWORD_PEPPER`). Seeder dùng `User::setPasswordAttribute()` nên vẫn tương thích pepper (không dùng `Hash::make()` trực tiếp).

## Khởi tạo tài khoản super_admin (chuẩn production)

Khuyến nghị: bootstrap bằng artisan command (idempotent, không hardcode trong seeder).

Ví dụ (dev/staging):

- `php artisan rbac:bootstrap-super-admin admin@your-company.com --name="Platform Admin" --password="your-strong-password" --force`

Nếu không truyền `--password`, hệ thống sẽ tạo password ngẫu nhiên và in ra **một lần**.

## Security notes (session cookies + password pepper)

### Session cookies
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax` (khuyến nghị; vẫn tương thích OAuth redirect)
- `SESSION_SECURE_COOKIE=true` khi chạy HTTPS

### Session/Cache auto-defaults (no manual switching)
Repo tự chọn session/cache driver theo `APP_ENV` + `REDIS_ENABLED`:

- **local/development**: `file` / `file`
- **testing**: `array` / `array`
- **staging/production**:
  - `REDIS_ENABLED=true` → `redis` / `redis`
  - không set hoặc `false` → fallback `file` / `file`

Tham chiếu:
- `apps/control-plane/config/session.php`
- `apps/control-plane/config/cache.php`

### Password pepper (advanced)
Hệ thống hỗ trợ pepper server-side qua biến môi trường:
- `AUTH_PASSWORD_PEPPER=...`

Pepper rotation (no downtime):
- `AUTH_PASSWORD_PEPPER_PREVIOUS=...` (optional)
- Khi set biến này, hệ thống sẽ:
  - chấp nhận password hash theo pepper hiện tại **hoặc** pepper cũ
  - nếu login bằng pepper cũ thành công → tự re-hash lại theo pepper mới (best-effort)

Nếu muốn bắt buộc pepper theo môi trường:
- `AUTH_PASSWORD_PEPPER_REQUIRED=true`

Lưu ý:
- Đây là secret, lưu trong secret manager / env.
- Nếu thay đổi pepper, **mọi mật khẩu hiện có sẽ không đăng nhập được** trừ khi bạn có chiến lược rotate.

## Two-step verification (2FA) — Email + Password

Mục tiêu: chỉ áp dụng 2FA cho flow đăng nhập bằng **email + mật khẩu**.
Đăng nhập bằng OAuth vẫn theo flow OAuth (không bắt OTP ở baseline).

### Database
- Migration: `apps/control-plane/database/migrations/2025_12_22_000001_add_two_factor_to_users_table.php`
- Fields:
  - `two_factor_enabled` (bool)
  - `two_factor_confirmed_at` (timestamp)
  - `two_factor_secret` (text; **được encrypt bằng `Crypt`**)
  - `two_factor_recovery_codes` (text; JSON; **được encrypt bằng `Crypt`**)

### Backend flow
- Khi user nhập đúng email+password:
  - Nếu user **đã bật 2FA** → redirect sang `bsadm.two-factor.challenge`
  - Nếu chưa bật → login như thường

Routes:
- `GET bsadm/two-factor/challenge` → `Auth\TwoFactorChallengeController@create`
- `POST bsadm/two-factor/challenge` → `Auth\TwoFactorChallengeController@store`
- `GET bsadm/security/two-factor` → Settings page

### Frontend pages
- OTP challenge: `apps/control-plane/resources/js/Pages/Auth/TwoFactorChallenge.tsx`
- TwoFactor settings: `apps/control-plane/resources/js/Pages/Security/TwoFactor.tsx`

### JS dependency (QR code)
Trang settings dùng QR code từ `otpauthUrl`, cần package:
- `qrcode`

### Verification checklist (2FA)
1) Login bình thường (email+password) → vào Security page:
- `GET /bsadm/security/two-factor`
2) Bật 2FA:
- nhập current password → “Bắt đầu thiết lập”
- quét QR / nhập secret → nhập OTP → Confirm
- lưu recovery codes
3) Logout → login lại:
- đúng email/password → chuyển sang `/bsadm/two-factor/challenge`
- nhập OTP hoặc recovery code → vào dashboard


## Backend enforcement

### Route middleware (recommended)

Ví dụ:

- Dashboard: `can:admin.dashboard.view`
- Users: `can:admin.users.view`

Trong Laravel 12 skeleton (không có `Http\Kernel.php`), alias middleware được đặt trong:

- `apps/control-plane/bootstrap/app.php` (alias `role`, `permission`, `role_or_permission`)

### Policies (recommended for controllers)

Ví dụ policy:

- `apps/control-plane/app/Policies/UserPolicy.php`
- `apps/control-plane/app/Policies/RolePolicy.php`

Provider đăng ký policy:

- `apps/control-plane/app/Providers/AuthServiceProvider.php`
- đăng ký trong `apps/control-plane/bootstrap/providers.php`

### 403 Inertia page

Khi thiếu quyền, server trả Inertia 403:

- Page: `apps/control-plane/resources/js/Pages/Errors/Forbidden.tsx`
- Render tại `apps/control-plane/bootstrap/app.php`

## Frontend UI guard

### Auth payload

Inertia shared props:

- `apps/control-plane/app/Http/Middleware/HandleInertiaRequests.php`

Shape:

```ts
auth: {
  user: { ... } | null,
  roles: string[],
  permissions: string[],
}
```

Type:

- `apps/control-plane/resources/js/types/index.d.ts`

### `<Can />` + hooks

- Component: `apps/control-plane/resources/js/components/auth/Can.tsx`
 - Generated permission types: `apps/control-plane/resources/js/types/permissions.generated.ts` (generate bằng `php artisan rbac:export-permissions`)

API:

- `<Can permission="admin.users.view">...</Can>`
- `<Can any={["p1","p2"]}>...</Can>`
- `<Can all={["p1","p2"]}>...</Can>`
- `useCan("admin.users.view")`
- `useHasRole("admin")`

### Menu guard

- Menu config: `apps/control-plane/resources/js/utils/adminMenu.ts`
- Layout dùng menu filter: `apps/control-plane/resources/js/layouts/AppShell.tsx`

### Page guard (optional)

- Helper: `apps/control-plane/resources/js/components/auth/withPermission.tsx`

Ví dụ:

- `export default withPermission(Page, "admin.users.view")`

## Multi-tenant (future)

Hiện `config/permission.php` đang `teams: false`.

Khi bật multi-tenant:

1. Set `teams: true`
2. Thêm `team_id` vào bảng roles/permissions & pivot theo hướng dẫn Spatie
3. Chọn `team_foreign_key` phù hợp (mặc định `team_id`)

Lưu ý: khi bật “teams”, toàn bộ truy vấn permission/role sẽ được scoping theo team.


