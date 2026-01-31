# PASSWORD PEPPER ROTATION - HƯỚNG DẪN

## Vấn đề

Khi bạn thay đổi `AUTH_PASSWORD_PEPPER`, tất cả passwords hiện tại sẽ **KHÔNG CÒN HOẠT ĐỘNG** vì:
- User-specific pepper thay đổi (dựa trên global pepper)
- Hash cũ không match với pepper mới

---

## Giải pháp: Zero-Downtime Rotation (Khuyến nghị)

### Cách hoạt động

Hệ thống đã có sẵn logic tự động re-hash khi login (trong `PepperedUserProvider`). Bạn chỉ cần set `AUTH_PASSWORD_PEPPER_PREVIOUS`.

### Quy trình Rotation

#### Bước 1: Chuẩn bị (trước khi đổi pepper)

```env
# Pepper hiện tại
AUTH_PASSWORD_PEPPER=current_pepper_abc123
AUTH_PASSWORD_PEPPER_PREVIOUS=
```

**Lưu lại giá trị pepper cũ** (bạn sẽ cần nó ở bước 2).

#### Bước 2: Rotation (zero downtime)

```env
# Set pepper mới
AUTH_PASSWORD_PEPPER=new_pepper_xyz789

# Set pepper cũ vào PREVIOUS (QUAN TRỌNG!)
AUTH_PASSWORD_PEPPER_PREVIOUS=current_pepper_abc123
```

**Deploy và restart app.**

#### Bước 3: Khi user login

Hệ thống tự động:
1. Thử verify với pepper mới → ❌ Fail (password được hash với pepper cũ)
2. Thử verify với pepper cũ → ✅ Success
3. **Tự động re-hash** password với pepper mới → Lưu vào DB
4. Login thành công

**Kết quả:** ✅ Zero downtime, tự động re-hash

#### Bước 4: Cleanup (sau vài tuần/tháng)

Sau khi tất cả users đã login (đã được re-hash):

```env
AUTH_PASSWORD_PEPPER=new_pepper_xyz789
AUTH_PASSWORD_PEPPER_PREVIOUS=  # Xóa pepper cũ
```

**Deploy và restart app.**

---

## Command: Kiểm tra Rotation Status

### Command đã có sẵn

```powershell
cd apps/control-plane
php artisan password:check-rotation
```

### Output

```
=== PASSWORD PEPPER ROTATION STATUS ===

Configuration:
  AUTH_PASSWORD_PEPPER: ✅ Set
  AUTH_PASSWORD_PEPPER_PREVIOUS: ✅ Set (rotation active)

Total users: 6

✅ Rotation is active (AUTH_PASSWORD_PEPPER_PREVIOUS is set).

How it works:
  → Users can login with old pepper
  → Passwords automatically re-hash with new pepper on login
  → Zero downtime - no user action required

After all users have logged in (few weeks/months):
  → Remove AUTH_PASSWORD_PEPPER_PREVIOUS
  → Restart application
```

---

## Ví dụ cụ thể

### Scenario: Đổi pepper mới

#### Trước khi đổi:
```env
AUTH_PASSWORD_PEPPER=old_secret_key_123
AUTH_PASSWORD_PEPPER_PREVIOUS=
```

User A login → Password hash với `old_secret_key_123`

#### Khi đổi (zero downtime):
```env
AUTH_PASSWORD_PEPPER=new_secret_key_456
AUTH_PASSWORD_PEPPER_PREVIOUS=old_secret_key_123  # ← QUAN TRỌNG!
```

User A login:
1. Thử với pepper mới → ❌ Fail
2. Thử với pepper cũ → ✅ Success
3. Tự động re-hash với pepper mới → Lưu DB
4. Login thành công

#### Sau khi tất cả users đã login:
```env
AUTH_PASSWORD_PEPPER=new_secret_key_456
AUTH_PASSWORD_PEPPER_PREVIOUS=  # Xóa
```

User A login → Password đã được hash với `new_secret_key_456` → ✅ Success ngay

---

## Nếu không có Previous Pepper

### Vấn đề

Nếu bạn **KHÔNG SET** `AUTH_PASSWORD_PEPPER_PREVIOUS`:
- ❌ Tất cả passwords cũ sẽ **KHÔNG HOẠT ĐỘNG**
- ❌ Users **KHÔNG THỂ LOGIN**
- ⚠️ Phải reset password cho tất cả users

### Giải pháp

#### Option 1: Set Previous Pepper (Khuyến nghị)

Nếu bạn còn nhớ pepper cũ:
```env
AUTH_PASSWORD_PEPPER=new_pepper
AUTH_PASSWORD_PEPPER_PREVIOUS=old_pepper  # ← Set lại
```

#### Option 2: Force Reset Password

Nếu không còn nhớ pepper cũ:
1. Users sử dụng "Forgot Password" flow
2. Nhận reset token qua email
3. Set password mới (sẽ được hash với pepper mới)

**Kết quả:** ⚠️ Downtime (users không thể login cho đến khi reset)

---

## Test Rotation

### Test local

```powershell
# 1. Set pepper cũ
$env:AUTH_PASSWORD_PEPPER="old_pepper_123"

# 2. Tạo user
cd apps/control-plane
php artisan tinker
>>> $user = User::create(['email' => 'test@example.com', 'password' => 'password123']);

# 3. Set pepper mới + previous
$env:AUTH_PASSWORD_PEPPER="new_pepper_456"
$env:AUTH_PASSWORD_PEPPER_PREVIOUS="old_pepper_123"

# 4. Clear config cache
php artisan config:clear

# 5. Test login (sẽ tự động re-hash)
php artisan tinker
>>> Auth::attempt(['email' => 'test@example.com', 'password' => 'password123']);
# Should return true (login với pepper cũ, tự động re-hash với pepper mới)

# 6. Verify password đã được re-hash
>>> $user->refresh();
>>> // Password hash đã thay đổi (re-hashed với pepper mới)
```

---

## Monitoring

### Check rotation status

```powershell
php artisan password:check-rotation
```

### Monitor users đã login

Sau khi rotation, monitor số lượng users đã login:
- Users đã login → Password đã được re-hash
- Users chưa login → Vẫn dùng pepper cũ (sẽ re-hash khi login)

---

## Best Practices

1. **Luôn dùng PREVIOUS pepper** khi rotation
2. **Lưu lại pepper cũ** trước khi đổi (để set vào PREVIOUS)
3. **Monitor** số lượng users đã login sau rotation
4. **Đợi vài tuần/tháng** trước khi xóa previous pepper
5. **Test** rotation trên staging trước
6. **Backup database** trước khi rotation

---

## Troubleshooting

### Vấn đề: Users không thể login sau khi đổi pepper

**Nguyên nhân:**
- Không set `AUTH_PASSWORD_PEPPER_PREVIOUS`
- Pepper cũ không đúng

**Giải pháp:**
1. Set `AUTH_PASSWORD_PEPPER_PREVIOUS` = pepper cũ
2. Restart app: `php artisan config:clear`
3. Users login → tự động re-hash

### Vấn đề: Một số users vẫn không login được

**Nguyên nhân:**
- User ID hoặc email thay đổi → user-specific pepper thay đổi

**Giải pháp:**
- User phải reset password (vì user-specific pepper đã thay đổi)

---

## Tóm tắt

### ✅ Khuyến nghị (Zero Downtime)
```env
AUTH_PASSWORD_PEPPER=new_pepper
AUTH_PASSWORD_PEPPER_PREVIOUS=old_pepper
```
→ Users login tự động re-hash

### ⚠️ Không khuyến nghị (Downtime)
```env
AUTH_PASSWORD_PEPPER=new_pepper
AUTH_PASSWORD_PEPPER_PREVIOUS=
```
→ Users phải reset password

---

## Command Reference

```powershell
# Check rotation status
php artisan password:check-rotation

# Clear config cache (sau khi đổi .env)
php artisan config:clear
```
