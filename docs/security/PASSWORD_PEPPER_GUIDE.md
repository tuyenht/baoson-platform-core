# PASSWORD PEPPER - HƯỚNG DẪN CHI TIẾT

## Tổng quan

Password pepper là một **secret key** được thêm vào password trước khi hash. Nó giúp tăng cường bảo mật bằng cách:
- Ngăn chặn rainbow table attacks
- Bảo vệ ngay cả khi database bị leak (nếu hacker không có pepper)
- Mỗi user có pepper riêng (user-specific) dựa trên global pepper

---

## 1. AUTH_PASSWORD_PEPPER

### Mục đích
**Global pepper** - Secret key dùng chung cho tất cả users trong hệ thống.

### Cách hoạt động
```
Password: "mypassword123"
   ↓
Global Pepper: "my_secret_pepper_key"
   ↓
hash_hmac('sha256', "mypassword123", "my_secret_pepper_key")
   ↓
Hash::make($peppered_value)
   ↓
Stored in DB: "$2y$10$abc..."
```

### Cách set

#### Bước 1: Tạo random pepper
```powershell
# PowerShell: Generate random 64-char string
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 64 | ForEach-Object {[char]$_})

# Kết quả ví dụ:
# aBc123XyZ456DeF789GhI012JkL345MnO678PqR901StU234VwX567YzA890
```

#### Bước 2: Thêm vào `.env`
```env
AUTH_PASSWORD_PEPPER=aBc123XyZ456DeF789GhI012JkL345MnO678PqR901StU234VwX567YzA890
```

### Ví dụ sử dụng

#### Khi tạo user:
```php
$user = User::create([
    'email' => 'user@example.com',
    'password' => 'mypassword123'
]);

// Hệ thống tự động:
// 1. Lấy global pepper: config('security.password_pepper')
// 2. Tạo user-specific pepper: HMAC(email|id, global_pepper)
// 3. Hash password với user-specific pepper
// 4. Lưu vào DB
```

#### Khi login:
```php
Auth::attempt([
    'email' => 'user@example.com',
    'password' => 'mypassword123'
]);

// Hệ thống tự động:
// 1. Lấy global pepper
// 2. Tạo user-specific pepper (giống khi tạo user)
// 3. Hash password với user-specific pepper
// 4. So sánh với hash trong DB
```

### Lưu ý
- ✅ **Bắt buộc trong production** (khuyến nghị)
- ✅ **Không commit vào git** (lưu trong secret manager)
- ✅ **Mỗi môi trường có pepper riêng** (dev/staging/prod)
- ❌ **Nếu đổi pepper** → Tất cả passwords cũ sẽ invalid (trừ khi dùng PREVIOUS)

---

## 2. AUTH_PASSWORD_PEPPER_PREVIOUS

### Mục đích
**Previous pepper** - Pepper cũ để hỗ trợ **rotation không downtime**.

### Tại sao cần?
Khi bạn muốn đổi pepper mới:
- ❌ **Không có PREVIOUS:** Tất cả users phải reset password (downtime)
- ✅ **Có PREVIOUS:** Users có thể login với pepper cũ, hệ thống tự động re-hash với pepper mới (zero downtime)

### Cách hoạt động

#### Kịch bản rotation:

**Bước 1:** Users đang dùng pepper cũ
```env
AUTH_PASSWORD_PEPPER=old_pepper_123
AUTH_PASSWORD_PEPPER_PREVIOUS=
```

**Bước 2:** Bạn muốn đổi sang pepper mới
```env
AUTH_PASSWORD_PEPPER=new_pepper_456
AUTH_PASSWORD_PEPPER_PREVIOUS=old_pepper_123  # ← Set pepper cũ vào đây
```

**Bước 3:** Khi user login
```php
// User login với password cũ
Auth::attempt(['email' => 'user@example.com', 'password' => 'mypassword123']);

// Hệ thống thử:
// 1. Hash với NEW pepper → ❌ FAIL (vì password được hash với OLD pepper)
// 2. Hash với PREVIOUS pepper → ✅ SUCCESS
// 3. Tự động re-hash password với NEW pepper → Lưu vào DB
// 4. Login thành công
```

**Bước 4:** Sau khi tất cả users đã login (đã được re-hash)
```env
AUTH_PASSWORD_PEPPER=new_pepper_456
AUTH_PASSWORD_PEPPER_PREVIOUS=  # ← Xóa pepper cũ (hoặc để trống)
```

### Ví dụ cụ thể

#### Trước khi rotation:
```env
AUTH_PASSWORD_PEPPER=secret_key_abc123
AUTH_PASSWORD_PEPPER_PREVIOUS=
```

User A login → Password được hash với `secret_key_abc123`

#### Khi rotation (zero downtime):
```env
AUTH_PASSWORD_PEPPER=secret_key_xyz789  # ← Pepper mới
AUTH_PASSWORD_PEPPER_PREVIOUS=secret_key_abc123  # ← Pepper cũ
```

User A login:
1. Thử với pepper mới → ❌ Fail
2. Thử với pepper cũ → ✅ Success
3. Tự động re-hash với pepper mới → Lưu vào DB
4. Login thành công

#### Sau khi rotation (tất cả users đã re-hash):
```env
AUTH_PASSWORD_PEPPER=secret_key_xyz789
AUTH_PASSWORD_PREVIOUS=  # ← Xóa pepper cũ
```

User A login → Password đã được hash với `secret_key_xyz789` → ✅ Success ngay

### Lưu ý
- ✅ **Chỉ dùng khi rotation** pepper
- ✅ **Xóa sau khi rotation xong** (sau vài tuần/tháng)
- ✅ **Hỗ trợ zero-downtime** rotation
- ⚠️ **Không set nếu không cần rotation**

---

## 3. AUTH_PASSWORD_PEPPER_REQUIRED

### Mục đích
**Fail-fast flag** - Bắt buộc phải có pepper, nếu không sẽ throw exception khi app khởi động.

### Cách hoạt động

#### Khi `false` (mặc định):
```env
AUTH_PASSWORD_PEPPER_REQUIRED=false
AUTH_PASSWORD_PEPPER=  # ← Có thể để trống
```

**Kết quả:**
- ✅ App vẫn chạy được (không có pepper)
- ✅ Passwords được hash bình thường (không có pepper)
- ⚠️ **Kém an toàn hơn** (không có lớp bảo vệ pepper)

#### Khi `true`:
```env
AUTH_PASSWORD_PEPPER_REQUIRED=true
AUTH_PASSWORD_PEPPER=  # ← Để trống
```

**Kết quả:**
- ❌ App **KHÔNG CHẠY ĐƯỢC**
- ❌ Throw exception: `Missing AUTH_PASSWORD_PEPPER in production.`
- ✅ **Bắt buộc** phải set pepper mới chạy được

### Ví dụ sử dụng

#### Development (không bắt buộc):
```env
AUTH_PASSWORD_PEPPER_REQUIRED=false
AUTH_PASSWORD_PEPPER=  # Có thể để trống cho dev
```

#### Production (bắt buộc):
```env
AUTH_PASSWORD_PEPPER_REQUIRED=true
AUTH_PASSWORD_PEPPER=aBc123XyZ456...  # Phải có giá trị
```

Nếu thiếu pepper → App sẽ **crash ngay khi khởi động** (fail-fast).

### Code xử lý
```php
// apps/control-plane/app/Providers/AppServiceProvider.php
if ((bool) config('security.require_password_pepper', false) 
    && empty(config('security.password_pepper'))) {
    throw new RuntimeException('Missing AUTH_PASSWORD_PEPPER in production.');
}
```

### Lưu ý
- ✅ **Khuyến nghị: `true` cho production**
- ✅ **Fail-fast** → Phát hiện lỗi sớm (khi deploy)
- ✅ **Đảm bảo** production luôn có pepper
- ⚠️ **Không set `true`** nếu chưa có pepper (app sẽ không chạy)

---

## So sánh các cấu hình

### Cấu hình 1: Không có pepper (development)
```env
AUTH_PASSWORD_PEPPER=
AUTH_PASSWORD_PEPPER_PREVIOUS=
AUTH_PASSWORD_PEPPER_REQUIRED=false
```

**Kết quả:**
- ✅ App chạy được
- ✅ Passwords được hash bình thường (bcrypt only)
- ⚠️ Không có lớp bảo vệ pepper

### Cấu hình 2: Có pepper (production)
```env
AUTH_PASSWORD_PEPPER=aBc123XyZ456...
AUTH_PASSWORD_PEPPER_PREVIOUS=
AUTH_PASSWORD_PEPPER_REQUIRED=true
```

**Kết quả:**
- ✅ App chạy được
- ✅ Passwords được hash với pepper (an toàn hơn)
- ✅ Fail-fast nếu thiếu pepper

### Cấu hình 3: Đang rotation pepper
```env
AUTH_PASSWORD_PEPPER=new_pepper_xyz789
AUTH_PASSWORD_PEPPER_PREVIOUS=old_pepper_abc123
AUTH_PASSWORD_PEPPER_REQUIRED=true
```

**Kết quả:**
- ✅ App chạy được
- ✅ Users với pepper cũ vẫn login được
- ✅ Tự động re-hash với pepper mới khi login
- ✅ Zero-downtime rotation

---

## Checklist sử dụng

### Development
- [ ] `AUTH_PASSWORD_PEPPER=` (có thể để trống)
- [ ] `AUTH_PASSWORD_PEPPER_PREVIOUS=` (để trống)
- [ ] `AUTH_PASSWORD_PEPPER_REQUIRED=false`

### Production
- [ ] `AUTH_PASSWORD_PEPPER=<random_64_chars>` (bắt buộc có)
- [ ] `AUTH_PASSWORD_PEPPER_PREVIOUS=` (để trống, trừ khi đang rotation)
- [ ] `AUTH_PASSWORD_PEPPER_REQUIRED=true` (bắt buộc)

### Khi rotation pepper
- [ ] Set `AUTH_PASSWORD_PEPPER` = pepper mới
- [ ] Set `AUTH_PASSWORD_PEPPER_PREVIOUS` = pepper cũ
- [ ] Đợi tất cả users login (để re-hash)
- [ ] Xóa `AUTH_PASSWORD_PEPPER_PREVIOUS` sau vài tuần/tháng

---

## Tóm tắt

| Key | Mục đích | Bắt buộc | Khi nào dùng |
|-----|----------|----------|--------------|
| `AUTH_PASSWORD_PEPPER` | Global pepper secret | Production: Có<br>Dev: Không | Luôn set trong production |
| `AUTH_PASSWORD_PEPPER_PREVIOUS` | Pepper cũ (rotation) | Không | Chỉ khi đang rotation pepper |
| `AUTH_PASSWORD_PEPPER_REQUIRED` | Fail-fast flag | Không | Set `true` trong production |

---

## Best Practices

1. **Production:**
   ```env
   AUTH_PASSWORD_PEPPER=<random_64_chars>
   AUTH_PASSWORD_PEPPER_REQUIRED=true
   ```

2. **Development:**
   ```env
   AUTH_PASSWORD_PEPPER=
   AUTH_PASSWORD_PEPPER_REQUIRED=false
   ```

3. **Rotation:**
   ```env
   AUTH_PASSWORD_PEPPER=<new_pepper>
   AUTH_PASSWORD_PEPPER_PREVIOUS=<old_pepper>
   AUTH_PASSWORD_PEPPER_REQUIRED=true
   ```

4. **Sau rotation:**
   ```env
   AUTH_PASSWORD_PEPPER=<new_pepper>
   AUTH_PASSWORD_PEPPER_PREVIOUS=  # Xóa
   AUTH_PASSWORD_PEPPER_REQUIRED=true
   ```

