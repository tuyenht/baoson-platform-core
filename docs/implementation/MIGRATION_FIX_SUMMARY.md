# MIGRATION FIX SUMMARY

## Vấn đề đã sửa

### 1. ✅ Xóa migration duplicate sessions
- **Đã xóa:** `0001_01_01_000003_create_sessions_table.php`
- **Lý do:** `0001_01_01_000000_create_users_table.php` đã tạo table `sessions`

### 2. ✅ Xóa migration duplicate permissions
- **Đã xóa:** `2025_12_21_000001_create_permission_tables.php`
- **Đã xóa:** `2025_12_21_000003_create_permission_tables.php`
- **Giữ lại:** `0001_01_01_000003_create_permission_tables.php` (Laravel default)

### 3. ✅ Đổi tên migration files với timestamp thực tế
- `2025_01_XX_000001_create_sites_table.php` → `2025_12_22_221600_create_sites_table.php`
- `2025_01_XX_000002_create_site_memberships_table.php` → `2025_12_22_221601_create_site_memberships_table.php`
- `2025_01_XX_000003_update_tenants_table_add_status.php` → `2025_12_22_221602_update_tenants_table_add_status.php`
- `2025_01_XX_000004_migrate_old_roles_to_new_roles.php` → `2025_12_22_221603_migrate_old_roles_to_new_roles.php`
- `2025_01_XX_000005_create_feature_flags_table.php` → `2025_12_22_221604_create_feature_flags_table.php`
- `2025_01_XX_000006_create_admin_menu_items_table.php` → `2025_12_22_221605_create_admin_menu_items_table.php`

## Danh sách migrations hiện tại (theo thứ tự)

1. `0001_01_01_000000_create_users_table.php` ✅
2. `0001_01_01_000001_create_cache_table.php` ✅
3. `0001_01_01_000002_create_jobs_table.php` ✅
4. `0001_01_01_000003_create_permission_tables.php` ✅ (Spatie permissions)
5. `2025_12_22_000001_add_two_factor_to_users_table.php` ✅
6. `2025_12_22_000002_create_audit_logs_table.php` ✅
7. `2025_12_22_000003_create_tenants_table.php` ✅
8. `2025_12_22_221600_create_sites_table.php` ✅
9. `2025_12_22_221601_create_site_memberships_table.php` ✅
10. `2025_12_22_221602_update_tenants_table_add_status.php` ✅
11. `2025_12_22_221603_migrate_old_roles_to_new_roles.php` ✅
12. `2025_12_22_221604_create_feature_flags_table.php` ✅
13. `2025_12_22_221605_create_admin_menu_items_table.php` ✅

## Lệnh chạy lại

```powershell
cd apps/control-plane

# Xóa database và tạo lại
Remove-Item database/database.sqlite -ErrorAction SilentlyContinue
New-Item database/database.sqlite -ItemType File

# Chạy migrations (bây giờ sẽ chạy được tất cả)
php artisan migrate

# Chạy seeders
php artisan db:seed
```

## Kết quả mong đợi

✅ Tất cả migrations chạy thành công (không còn lỗi duplicate)
✅ Tất cả tables được tạo: `sites`, `site_memberships`, `feature_flags`, `admin_menu_items`, etc.
✅ Seeders chạy thành công: roles, permissions, feature flags, menu items, demo users

