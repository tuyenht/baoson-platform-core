# Documentation Index

## 📚 Tổng quan

Thư mục này chứa tất cả tài liệu của dự án **baoson-platform-core**.

---

## 📁 Cấu trúc thư mục

### `/docs/` - Root documentation
- `CORE_SETUP.md` - Hướng dẫn setup cơ bản
- `SECURITY.md` - Tài liệu bảo mật tổng quan
- `rbac.md` - Tài liệu RBAC (Roles & Permissions)
- `i18n.md` - Tài liệu đa ngôn ngữ
- `PHP_EXTENSIONS.md` - Tài liệu PHP extensions

### `/docs/implementation/` - Tài liệu triển khai
- `PHASE_0_REPO_SCAN_REPORT.md` - Báo cáo quét repository (Phase 0)
- `IMPLEMENTATION_SUMMARY.md` - Tổng hợp triển khai hệ thống authorization
- `MIGRATION_FIX_SUMMARY.md` - Tóm tắt sửa lỗi migrations
- `DATABASE_RESET_FLOW.md` - Quy trình reset database

### `/docs/security/` - Tài liệu bảo mật
- `PASSWORD_PEPPER_GUIDE.md` - Hướng dẫn chi tiết về password pepper
- `GLOBAL_PEPPER_CONFIGURATION.md` - Cấu hình global pepper
- `USER_SPECIFIC_PEPPER_SECURITY.md` - Bảo mật user-specific pepper
- `PASSWORD_HASH_COPY_SECURITY.md` - Phân tích bảo mật copy hash
- `PASSWORD_SECURITY_EXPLANATION.md` - Giải thích bảo mật password

### `/docs/ADR/` - Architecture Decision Records
- `0001-monorepo-structure.md` - Quyết định về cấu trúc monorepo

---

## 🔍 Tìm kiếm nhanh

### Setup & Configuration
- [CORE_SETUP.md](./CORE_SETUP.md) - Setup cơ bản
- [rbac.md](./rbac.md) - RBAC configuration

### Security
- [SECURITY.md](./SECURITY.md) - Security overview
- [PASSWORD_PEPPER_GUIDE.md](./security/PASSWORD_PEPPER_GUIDE.md) - Password pepper guide
- [USER_SPECIFIC_PEPPER_SECURITY.md](./security/USER_SPECIFIC_PEPPER_SECURITY.md) - User-specific pepper

### Implementation
- [IMPLEMENTATION_SUMMARY.md](./implementation/IMPLEMENTATION_SUMMARY.md) - Implementation summary
- [PHASE_0_REPO_SCAN_REPORT.md](./implementation/PHASE_0_REPO_SCAN_REPORT.md) - Phase 0 report

---

## 📝 Ghi chú

- Tất cả file `.md` trong root project đã được di chuyển vào thư mục `docs/`
- `README.md` ở root được giữ lại (theo thông lệ)
- Các file trong `apps/control-plane/` được giữ nguyên (project-specific docs)

