# PHASE 0 — REPO SCAN REPORT (READ ONLY)
## Báo cáo quét repository — Hệ thống Authorization SaaS

**Ngày:** 2025-01-XX  
**Project:** baoson-platform-core  
**Stack:** Laravel 12 + Inertia + React + TypeScript  
**Admin prefix:** `/bsadm` (NON-NEGOTIABLE)

---

## 1. TỔNG QUAN CÁC THÀNH PHẦN ĐÃ TỒN TẠI

### ✅ AUTHENTICATION (Đã có)

**Files:**
- `apps/control-plane/app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `apps/control-plane/app/Auth/PepperedUserProvider.php` (custom password pepper)
- `apps/control-plane/app/Models/User.php` (có 2FA fields: `two_factor_enabled`, `two_factor_secret`, `two_factor_recovery_codes`)
- `apps/control-plane/app/Http/Controllers/Auth/TwoFactorChallengeController.php`
- `apps/control-plane/app/Http/Controllers/Security/TwoFactorController.php`
- `apps/control-plane/app/Support/TwoFactor/Totp.php` (TOTP implementation)

**Tính năng:**
- ✅ Email + password login
- ✅ Password pepper (server-side)
- ✅ 2FA TOTP (RFC6238, SHA1, 30s, 6 digits)
- ✅ Recovery codes
- ✅ Rate limiting trên login
- ✅ OAuth skeleton (Google/Facebook) — routes có sẵn nhưng chưa đầy đủ validation

**Routes:**
- `/bsadm/login` (GET/POST)
- `/bsadm/two-factor/challenge` (GET/POST)
- `/bsadm/security/two-factor` (settings)
- `/bsadm/oauth/{provider}/redirect` và `/callback`

---

### ✅ RBAC (Đã có, NHƯNG SAI ROLES)

**Files:**
- `apps/control-plane/app/Enums/Permission.php` (canonical permissions enum)
- `apps/control-plane/app/Support/Rbac/RbacService.php`
- `apps/control-plane/app/Support/Rbac/Rbac.php`
- `apps/control-plane/app/Console/Commands/RbacSync.php`
- `apps/control-plane/app/Console/Commands/RbacGrant.php`
- `apps/control-plane/database/seeders/RbacSeeder.php`
- `apps/control-plane/config/permission.php` (Spatie config)
- `apps/control-plane/config/rbac.php`

**Library:** `spatie/laravel-permission` v6.24

**Roles hiện tại (SAI):**
- ❌ `super_admin` (phải là `saasOwner` hoặc `saasAdmin`)
- ❌ `admin` (phải là `siteAdmin` hoặc tương đương)
- ❌ `editor` (phải là `siteEditor`)
- ❌ `viewer` (phải là `siteViewer`)

**Roles yêu cầu (CHƯA CÓ):**
- ❌ `saasOwner` — chưa có
- ❌ `saasAdmin` — chưa có
- ❌ `siteOwner` — chưa có
- ❌ `siteAdmin` — chưa có
- ❌ `siteEditor` — chưa có
- ❌ `siteTranslator` — chưa có
- ❌ `siteViewer` — chưa có

**Permissions naming convention:**
- ✅ Đã có: `<domain>.<resource>.<action>` (ví dụ: `admin.users.view`)
- ⚠️ Cần mở rộng: thêm scope prefix (`platform.*`, `tenant.*`, `site.*`)

**Commands:**
- ✅ `php artisan rbac:sync` — sync permissions từ enum
- ✅ `php artisan rbac:grant <permission> --role=<role>`
- ✅ `php artisan rbac:bootstrap-super-admin <email>` — tạo super_admin

**Enforcement:**
- ✅ Route middleware: `can:`, `role:`, `permission:`
- ✅ Policies: `apps/control-plane/app/Policies/` (RolePolicy, PermissionPolicy, UserPolicy)
- ✅ Frontend: `<Can />` component (`apps/control-plane/resources/js/components/auth/Can.tsx`)

---

### ⚠️ TENANCY (Có skeleton, THIẾU Site)

**Files:**
- `apps/control-plane/app/Models/Tenant.php` ✅
- `apps/control-plane/app/Support/Tenancy/TenantContext.php` ✅
- `apps/control-plane/app/Http/Middleware/ResolveTenant.php` ✅
- `apps/control-plane/database/migrations/2025_12_22_000003_create_tenants_table.php` ✅
- `apps/control-plane/config/tenancy.php` ✅

**Tenant table:**
- ✅ `id` (bigint, auto-increment)
- ✅ `key` (string, unique)
- ✅ `name` (string)
- ✅ `is_active` (boolean)
- ⚠️ Thiếu: `status` enum (chỉ có `is_active` boolean)

**Site model/table:**
- ❌ **KHÔNG TỒN TẠI** — cần tạo mới

**Site memberships:**
- ❌ **KHÔNG TỒN TẠI** — cần tạo `site_memberships` pivot

**TenantContext:**
- ✅ Resolve từ subdomain hoặc header (`X-Tenant`)
- ⚠️ Chưa có logic resolve từ session/user membership
- ⚠️ Chưa có SiteContext

**Frontend types:**
- ✅ `packages/shared-types/src/tenancy.ts` — có type definitions cho Tenant và Site (nhưng backend chưa có Site model)

---

### ✅ AUDIT LOG (Đã có đầy đủ)

**Files:**
- `apps/control-plane/app/Models/AuditLog.php`
- `apps/control-plane/app/Support/Audit/Audit.php`
- `apps/control-plane/app/Http/Controllers/Admin/AuditLogController.php`
- `apps/control-plane/app/Console/Commands/AuditPrune.php`
- `apps/control-plane/database/migrations/2025_12_22_000002_create_audit_logs_table.php`

**Tính năng:**
- ✅ Immutable log (append-only)
- ✅ Capture: event, actor, IP, user agent, route, status code, meta (JSON)
- ✅ Sanitization (không log secrets)
- ✅ Prune command
- ✅ Frontend UI (`/bsadm/audit-logs`)

**Integration:**
- ✅ Đã tích hợp vào login flow
- ✅ Đã tích hợp vào 2FA flow

---

### ✅ SECURITY HEADERS (Đã có)

**Files:**
- `apps/control-plane/app/Http/Middleware/SecurityHeaders.php`

**Headers:**
- ✅ `X-Frame-Options: SAMEORIGIN`
- ✅ `X-Content-Type-Options: nosniff`
- ✅ `Referrer-Policy: strict-origin-when-cross-origin`
- ✅ `Permissions-Policy` (tắt các quyền nhạy cảm)
- ✅ `X-XSS-Protection: 1; mode=block`
- ✅ `Content-Security-Policy` (tương thích Inertia/Vite)

**Applied:**
- ✅ Đã apply trong `bootstrap/app.php` middleware stack

---

### ⚠️ FEATURE FLAGS (CHƯA CÓ)

**Status:** ❌ **KHÔNG TỒN TẠI**

**Cần tạo:**
- Migration: `feature_flags` table
- Model: `app/Models/FeatureFlag.php`
- Service: `app/Services/FeatureFlags/FeatureFlagService.php`
- Middleware: `app/Http/Middleware/RequireFeatureFlag.php`

**Note:** Có đề cập trong `Step prompt.txt` nhưng chưa implement.

---

### ⚠️ DYNAMIC MENU (Có frontend, THIẾU backend DB-driven)

**Files:**
- `apps/control-plane/resources/js/utils/adminMenu.ts` — hardcoded menu array
- `apps/control-plane/resources/js/layouts/AppShell.tsx` — layout sử dụng menu
- `apps/control-plane/resources/js/components/auth/Can.tsx` — permission guard

**Status:**
- ✅ Frontend có filter menu theo permissions
- ❌ Menu hardcoded trong TypeScript (không DB-driven)
- ❌ Không có `admin_menu_items` table
- ❌ Không có `AdminMenuResolver` service
- ❌ Không share menu qua Inertia (chỉ share permissions)

**Inertia shared props:**
- ✅ `auth.user`, `auth.roles`, `auth.permissions` (trong `HandleInertiaRequests.php`)
- ❌ Không có `ui.menu`
- ❌ Không có `features.enabledFlags`

---

### ❌ WORKFLOW ENGINE (CHƯA CÓ)

**Status:** ❌ **KHÔNG TỒN TẠI**

**Cần tạo:**
- Service: `app/Services/Workflow/ContentWorkflow.php`
- States: `draft`, `pending`, `published`, `archived`
- Transition rules với role requirements
- Policy helpers: `canTransition()`

**Note:** Chưa có content model nào (Post/Page) để áp dụng workflow.

---

### ⚠️ BOOTSTRAP "FIRST saasOwner" (Có command, THIẾU setup flow)

**Files:**
- `apps/control-plane/app/Console/Commands/RbacBootstrapSuperAdmin.php` ✅

**Status:**
- ✅ Có command: `php artisan rbac:bootstrap-super-admin <email>`
- ❌ Không có route `/bsadm/setup`
- ❌ Không có middleware check "empty DB" → redirect to setup
- ❌ Không có setup controller/page

**Current flow:**
- Seeder tạo users trong `local/testing` environment
- Production: dùng command line

---

### ⚠️ UPLOAD HARDENING (CHƯA CÓ)

**Status:** ❌ **KHÔNG TỒN TẠI**

**Cần tạo:**
- Middleware/service: MIME allowlist, size limits, path traversal prevention, randomized filenames
- Disk config: store outside webroot hoặc protected disk

---

### ⚠️ HONEYPOT (CHƯA CÓ)

**Status:** ❌ **KHÔNG TỒN TẠI**

**Cần tạo:**
- Middleware/validation rule cho honeypot fields

---

### ✅ CSRF (Laravel built-in)

**Status:** ✅ Đã có sẵn (Laravel framework)

**Verification:**
- ✅ `HandleInertiaRequests.php` share `csrfToken`
- ✅ Forms có thể dùng `@csrf` hoặc Inertia CSRF token

---

### ⚠️ SOCIAL LOGIN (Skeleton có, THIẾU validation/rate limit)

**Files:**
- `apps/control-plane/app/Http/Controllers/Auth/OAuthController.php` (cần verify)

**Status:**
- ✅ Routes có sẵn
- ⚠️ Cần verify: rate limiting, validation, error handling

---

## 2. INVENTORY KEY FILES

### Routes
- `apps/control-plane/routes/web.php` ✅

### Models
- `apps/control-plane/app/Models/User.php` ✅
- `apps/control-plane/app/Models/Tenant.php` ✅
- `apps/control-plane/app/Models/AuditLog.php` ✅
- ❌ `app/Models/Site.php` — **CHƯA CÓ**

### Middleware
- `apps/control-plane/app/Http/Middleware/HandleInertiaRequests.php` ✅
- `apps/control-plane/app/Http/Middleware/ResolveTenant.php` ✅
- `apps/control-plane/app/Http/Middleware/SecurityHeaders.php` ✅
- `apps/control-plane/app/Http/Middleware/SetLocale.php` ✅
- `apps/control-plane/app/Http/Middleware/LogContext.php` ✅

### Policies
- `apps/control-plane/app/Policies/UserPolicy.php` ✅
- `apps/control-plane/app/Policies/RolePolicy.php` ✅
- `apps/control-plane/app/Policies/PermissionPolicy.php` ✅

### Migrations
- `apps/control-plane/database/migrations/0001_01_01_000000_create_users_table.php` ✅
- `apps/control-plane/database/migrations/2025_12_22_000001_add_two_factor_to_users_table.php` ✅
- `apps/control-plane/database/migrations/2025_12_22_000002_create_audit_logs_table.php` ✅
- `apps/control-plane/database/migrations/2025_12_22_000003_create_tenants_table.php` ✅
- `apps/control-plane/database/migrations/0001_01_01_000003_create_permission_tables.php` ✅ (Spatie)
- ❌ `create_sites_table.php` — **CHƯA CÓ**
- ❌ `create_site_memberships_table.php` — **CHƯA CÓ**
- ❌ `create_feature_flags_table.php` — **CHƯA CÓ**
- ❌ `create_admin_menu_items_table.php` — **CHƯA CÓ**

### Seeders
- `apps/control-plane/database/seeders/DatabaseSeeder.php` ✅
- `apps/control-plane/database/seeders/RbacSeeder.php` ✅

### Support/Enums
- `apps/control-plane/app/Enums/Permission.php` ✅
- `apps/control-plane/app/Support/Rbac/RbacService.php` ✅
- `apps/control-plane/app/Support/Rbac/Rbac.php` ✅
- `apps/control-plane/app/Support/Tenancy/TenantContext.php` ✅
- `apps/control-plane/app/Support/Audit/Audit.php` ✅
- `apps/control-plane/app/Support/TwoFactor/Totp.php` ✅
- ❌ `app/Support/Tenancy/SiteContext.php` — **CHƯA CÓ**

### Frontend
- `apps/control-plane/resources/js/components/auth/Can.tsx` ✅
- `apps/control-plane/resources/js/utils/adminMenu.ts` ✅ (hardcoded)
- `apps/control-plane/resources/js/layouts/AppShell.tsx` ✅
- `packages/shared-types/src/tenancy.ts` ✅ (types only)

---

## 3. PHÁT HIỆN TRÙNG LẶP

### ✅ KHÔNG CÓ TRÙNG LẶP

- ✅ Chỉ có 1 permission system (Spatie)
- ✅ Chỉ có 1 tenant resolver (TenantContext)
- ✅ Chỉ có 1 audit log system
- ✅ Chỉ có 1 auth system

**Lưu ý:** Có nhiều migration files cho permission tables (có thể do development history), nhưng chỉ 1 bộ tables thực tế.

---

## 4. TÓM TẮT: CÓ GÌ / THIẾU GÌ

### ✅ ĐÃ CÓ (Không cần tạo mới)

1. **Authentication:** Email + password, 2FA TOTP, OAuth skeleton
2. **RBAC foundation:** Spatie permission, enum-based permissions, sync commands
3. **Tenant model:** Table, model, context resolver (subdomain/header)
4. **Audit log:** Full implementation với sanitization
5. **Security headers:** CSP, X-Frame-Options, etc.
6. **CSRF:** Laravel built-in
7. **Rate limiting:** Có trên login endpoints

### ❌ THIẾU (Cần tạo mới)

1. **Roles:** Phải thay đổi từ `super_admin/admin/editor/viewer` → `saasOwner/saasAdmin/siteOwner/siteAdmin/siteEditor/siteTranslator/siteViewer`
2. **Site model/table:** Chưa có
3. **Site memberships:** Chưa có pivot table
4. **SiteContext:** Chưa có service
5. **Feature flags:** Chưa có table/service/middleware
6. **DB-driven menu:** Chưa có `admin_menu_items` table, `AdminMenuResolver`
7. **Workflow engine:** Chưa có
8. **Setup flow:** Chưa có `/bsadm/setup` route/controller
9. **Upload hardening:** Chưa có middleware/service
10. **Honeypot:** Chưa có
11. **Permissions scope:** Cần thêm `platform.*`, `tenant.*`, `site.*` prefix
12. **Social login validation:** Cần verify và bổ sung rate limit

### ⚠️ CẦN MỞ RỘNG/REFACTOR

1. **RBAC roles:** Thay đổi roles hiện tại → roles mới (migration + seeder)
2. **Permissions enum:** Thêm scope prefix (`platform.*`, `site.*`)
3. **TenantContext:** Mở rộng để resolve từ user membership + session
4. **Menu system:** Chuyển từ hardcoded → DB-driven
5. **Inertia shared props:** Thêm `ui.menu`, `features.enabledFlags`, `context.tenant`, `context.site`

---

## 5. CHỌN PHƯƠNG ÁN

### Approach: **EXTEND + MINIMAL REFACTOR**

**Nguyên tắc:**
1. **KHÔNG xóa** code hiện có (trừ khi thay thế hoàn toàn)
2. **Mở rộng** các service/context hiện có
3. **Thêm mới** các thành phần thiếu
4. **Migration strategy:** Tạo migration mới, không sửa migration cũ (trừ khi cần thiết)

**Chiến lược cụ thể:**

#### A) Roles Migration
- **Không xóa** roles cũ ngay
- **Tạo mới** 7 roles yêu cầu
- **Migration script:** Map `super_admin` → `saasOwner` (nếu cần)
- **Seeder:** Tạo cả 7 roles mới, giữ roles cũ tạm thời (deprecation path)

#### B) Permissions
- **Mở rộng** `app/Enums/Permission.php` với scope prefix
- **Giữ** permissions cũ (backward compatibility)
- **Thêm** `platform.*`, `site.*` permissions

#### C) Tenancy
- **Mở rộng** `TenantContext` → thêm `SiteContext`
- **Thêm** `sites` table + `site_memberships` pivot
- **Mở rộng** `ResolveTenant` middleware → resolve site từ session

#### D) Menu
- **Giữ** frontend code hiện tại (fallback)
- **Thêm** DB-driven menu system
- **Merge:** Frontend ưu tiên DB menu, fallback hardcoded

#### E) Workflow
- **Tạo mới** hoàn toàn (chưa có code liên quan)

---

## 6. BLOCKERS (Nếu có)

### ⚠️ BLOCKER 1: Roles hiện tại vs Roles yêu cầu

**Vấn đề:**  
Roles hiện tại (`super_admin`, `admin`, `editor`, `viewer`) khác hoàn toàn với roles yêu cầu (`saasOwner`, `saasAdmin`, `siteOwner`, `siteAdmin`, `siteEditor`, `siteTranslator`, `siteViewer`).

**Giải pháp:**
- Tạo migration script map roles (nếu có users hiện tại)
- Tạo 7 roles mới
- Deprecate roles cũ (hoặc giữ lại cho backward compatibility)

**Quyết định cần user:**  
Có cần migrate users hiện tại sang roles mới không? Hay tạo mới hoàn toàn?

### ⚠️ BLOCKER 2: Content model chưa có

**Vấn đề:**  
Workflow engine cần content model (Post/Page) để áp dụng state transitions.

**Giải pháp:**
- Tạo generic workflow service (không phụ thuộc model cụ thể)
- Hoặc tạo skeleton Post/Page model để demo workflow

**Quyết định cần user:**  
Có cần tạo content model (Post/Page) để demo workflow không?

### ✅ KHÔNG CÓ BLOCKER NGHIÊM TRỌNG

Tất cả thành phần thiếu đều có thể tạo mới mà không phá vỡ code hiện tại.

---

## 7. KẾT LUẬN

### ✅ CÓ THỂ TIẾP TỤC

Repository đã có foundation tốt:
- Auth + 2FA ✅
- RBAC (Spatie) ✅
- Tenant skeleton ✅
- Audit log ✅
- Security headers ✅

**Cần bổ sung:**
- 7 roles mới (thay thế/mở rộng roles cũ)
- Site model + memberships
- Feature flags
- DB-driven menu
- Workflow engine
- Setup flow
- Upload hardening + honeypot

**Approach:** Extend + minimal refactor (không breaking changes)

---

## 8. NEXT STEPS

Sau khi user approve báo cáo này, tiến hành:

1. **PHASE 1:** Authorization Model (Tenant + Site + Roles)
2. **PHASE 2:** RBAC (Hierarchy + Permissions Naming)
3. **PHASE 3:** Workflow Engine
4. **PHASE 4:** Bootstrap "First saasOwner"
5. **PHASE 5:** UI Guards + Dynamic Menu
6. **PHASE 6:** Security Baseline
7. **PHASE 7:** Audit Integration

---

**END OF PHASE 0 REPORT**

