# DATABASE RESET FLOW
## Quy trình tự động tạo lại dữ liệu khi xóa database

---

## Câu trả lời ngắn gọn

**CÓ**, nếu bạn xóa database và chạy lại `migrate` + `seed`, hệ thống sẽ **tự động tạo lại hoàn toàn** tất cả dữ liệu.

---

## Flow tự động

### 1. Xóa database (SQLite)

```powershell
cd apps/control-plane
# Xóa file database
Remove-Item database/database.sqlite -ErrorAction SilentlyContinue
# Hoặc tạo file mới
New-Item database/database.sqlite -ItemType File -Force
```

### 2. Chạy migrations

```powershell
php artisan migrate
```

**Kết quả:**
- ✅ Tạo tất cả tables (users, tenants, sites, site_memberships, roles, permissions, feature_flags, admin_menu_items, audit_logs, ...)
- ✅ Tables rỗng (chưa có dữ liệu)

### 3. Chạy seeders

```powershell
php artisan db:seed
```

**Kết quả:**
- ✅ `RbacSeeder` → Tạo 7 roles mới + sync tất cả permissions
- ✅ `FeatureFlagsSeeder` → Tạo baseline feature flags (admin.dashboard, admin.users, ...)
- ✅ `AdminMenuItemsSeeder` → Tạo baseline menu items (Dashboard, Users, Roles, ...)
- ✅ `DatabaseSeeder` → Tạo demo users (chỉ trong local/testing environment)

---

## Chi tiết các seeders

### RbacSeeder
- Tạo 7 roles: `saasOwner`, `saasAdmin`, `siteOwner`, `siteAdmin`, `siteEditor`, `siteTranslator`, `siteViewer`
- Sync tất cả permissions từ `Permission` enum
- Assign permissions cho từng role theo hierarchy

### FeatureFlagsSeeder
- Tạo 10 feature flags baseline:
  - `admin.dashboard`, `admin.users`, `admin.roles`, `admin.permissions`, `admin.tenants`, `admin.settings`, `admin.audit`
  - `site.content`, `site.media`, `site.i18n`
- Tất cả flags mặc định `enabled = true`

### AdminMenuItemsSeeder
- Tạo menu structure:
  - **Dashboard** (root)
  - **Identity & Access** (parent group)
    - Users
    - Roles
    - Permissions
    - Tenants
    - Audit Logs
  - **Settings** (root)
- Mỗi item có:
  - Required permissions (từ Permission enum)
  - Required feature flags
  - Route names (nếu có)

### DatabaseSeeder (chỉ local/testing)
- Tạo demo users với roles khác nhau (nếu `APP_ENV=local` hoặc `testing`)

---

## Flow hoàn chỉnh (từ đầu)

```powershell
# 1. Xóa database
cd apps/control-plane
Remove-Item database/database.sqlite -ErrorAction SilentlyContinue

# 2. Tạo database mới
New-Item database/database.sqlite -ItemType File -Force

# 3. Chạy migrations (tạo tables)
php artisan migrate

# 4. Chạy seeders (tạo dữ liệu)
php artisan db:seed

# 5. (Optional) Tạo first saasOwner nếu chưa có
# Truy cập: http://localhost:8000/bsadm
# Sẽ redirect đến /bsadm/setup
# Hoặc dùng command:
php artisan rbac:bootstrap-super-admin admin@example.com --name="Admin" --password="password"
```

---

## Lưu ý

### Idempotent Seeders
Tất cả seeders sử dụng `updateOrCreate()` nên:
- ✅ Chạy nhiều lần không tạo duplicate
- ✅ An toàn để chạy lại sau khi đã có dữ liệu

### Demo Users
- Demo users chỉ được tạo trong `local` hoặc `testing` environment
- Production: không tạo demo users (bảo mật)

### Setup Flow
- Nếu database empty và chưa có `saasOwner`:
  - Truy cập `/bsadm` → tự động redirect đến `/bsadm/setup`
  - Tạo first saasOwner qua setup form
  - Sau đó có thể login và sử dụng

---

## Verification

Sau khi chạy `migrate` + `seed`, kiểm tra:

```powershell
# Kiểm tra roles
php artisan tinker
>>> \Spatie\Permission\Models\Role::pluck('name')
# Kết quả: ["saasOwner", "saasAdmin", "siteOwner", "siteAdmin", "siteEditor", "siteTranslator", "siteViewer"]

# Kiểm tra feature flags
>>> \App\Models\FeatureFlag::pluck('key')
# Kết quả: ["admin.dashboard", "admin.users", "admin.roles", ...]

# Kiểm tra menu items
>>> \App\Models\AdminMenuItem::count()
# Kết quả: 8 (1 dashboard + 1 parent group + 5 children + 1 settings)
```

---

## Kết luận

**CÓ**, hệ thống sẽ tự động tạo lại hoàn toàn dữ liệu khi:
1. Xóa database
2. Chạy `php artisan migrate` (tạo tables)
3. Chạy `php artisan db:seed` (tạo dữ liệu)

Tất cả seeders đã được tích hợp vào `DatabaseSeeder`, nên chỉ cần chạy 1 lệnh `db:seed` là đủ.

