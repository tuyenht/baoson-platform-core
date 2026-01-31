# PASSWORD SECURITY - GIẢI THÍCH BẢO MẬT MẬT KHẨU

## Câu trả lời: **KHÔNG THỂ DỊCH NGƯỢC**

Password trong hệ thống này **KHÔNG THỂ** dịch ngược lại được. Đây là thiết kế bảo mật đúng chuẩn.

---

## Quy trình hash password (2 lớp)

### Bước 1: Server-side Pepper (nếu có)
```php
// File: app/Models/User.php - setPasswordAttribute()
$pepper = config('security.password_pepper');
$toHash = $pepper !== ''
    ? hash_hmac('sha256', $plainPassword, $pepper)  // HMAC-SHA256
    : $plainPassword;
```

**HMAC-SHA256:**
- ✅ One-way hash function (không thể reverse)
- ✅ Sử dụng secret key (pepper) từ server
- ✅ Ngay cả khi hacker có database, không thể reverse nếu không có pepper

### Bước 2: Laravel Hash (bcrypt)
```php
$this->attributes['password'] = Hash::make($toHash);  // bcrypt
```

**bcrypt:**
- ✅ One-way hash function (không thể reverse)
- ✅ Adaptive hashing (có thể tăng cost factor)
- ✅ Salt tự động (mỗi password có salt riêng)
- ✅ Chống brute-force (chậm, tốn tài nguyên)

---

## Tại sao không thể reverse?

### 1. Hash là One-way Function
```
Plain password: "mypassword123"
         ↓
    hash_hmac('sha256', ...)  (nếu có pepper)
         ↓
    Hash::make() (bcrypt)
         ↓
Stored: "$2y$10$abcdefghijklmnopqrstuvwxyz..."
```

**Không có cách nào** để từ `$2y$10$abcdef...` → `"mypassword123"`

### 2. Bcrypt là Adaptive Hash
- Mỗi lần hash tạo ra kết quả khác nhau (do salt random)
- Cùng password → hash khác nhau mỗi lần
- Cost factor cao → hash chậm (chống brute-force)

### 3. Pepper là Secret Key
- Pepper không lưu trong database
- Chỉ có trong `.env` hoặc secret manager
- Ngay cả khi hacker có database, không thể reverse nếu không có pepper

---

## So sánh với Encryption (có thể reverse)

### ❌ Encryption (CÓ THỂ reverse)
```
Plain: "mypassword123"
   ↓ encrypt với key
Cipher: "aBc123XyZ..."
   ↓ decrypt với key
Plain: "mypassword123"  ← CÓ THỂ LẤY LẠI
```

### ✅ Hashing (KHÔNG THỂ reverse)
```
Plain: "mypassword123"
   ↓ hash
Hash: "$2y$10$abcdef..."
   ↓ ???
Plain: ???  ← KHÔNG THỂ LẤY LẠI
```

---

## Cách hệ thống verify password

Khi user login:

```php
// File: app/Auth/PepperedUserProvider.php
public function validateCredentials(Authenticatable $user, array $credentials): bool
{
    $plain = $credentials['password'];  // "mypassword123"
    
    // 1. Apply pepper (nếu có)
    $peppered = hash_hmac('sha256', $plain, $pepper);
    
    // 2. Verify với hash trong database
    return $this->hasher->check($peppered, $user->getAuthPassword());
}
```

**Quy trình:**
1. User nhập password: `"mypassword123"`
2. Apply pepper: `hash_hmac('sha256', "mypassword123", $pepper)` → `"abc123..."`
3. Hash plain password: `Hash::make("abc123...")` → `"$2y$10$xyz..."`
4. So sánh với hash trong DB: `Hash::check("abc123...", "$2y$10$xyz...")`
5. Nếu match → login thành công

**Lưu ý:** Không bao giờ reverse hash, chỉ verify bằng cách hash lại và so sánh.

---

## Bảo mật hiện tại

### ✅ Điểm mạnh
1. **Two-layer hashing:** Pepper + bcrypt
2. **One-way hash:** Không thể reverse
3. **Salt tự động:** Mỗi password có salt riêng
4. **Adaptive cost:** Có thể tăng độ khó
5. **Pepper secret:** Không lưu trong database

### ⚠️ Lưu ý
1. **Pepper rotation:** Nếu đổi pepper, tất cả passwords cũ sẽ invalid
2. **Pepper storage:** Phải lưu pepper ở secret manager (không commit vào git)
3. **Brute-force:** Vẫn có thể brute-force nếu password yếu (nhưng rất chậm)

---

## Kết luận

**Password trong hệ thống này:**
- ✅ **KHÔNG THỂ** dịch ngược
- ✅ **KHÔNG THỂ** decrypt
- ✅ **KHÔNG THỂ** lấy lại plain text từ hash
- ✅ **CHỈ CÓ THỂ** verify bằng cách hash lại và so sánh

Đây là thiết kế bảo mật **ĐÚNG CHUẨN** và **AN TOÀN**.

---

## Nếu cần reset password?

Nếu user quên password, hệ thống **KHÔNG THỂ** lấy lại password cũ. Thay vào đó:

1. **Password Reset Flow:**
   - User request reset password
   - System gửi reset token qua email
   - User dùng token để set password mới
   - Password cũ bị thay thế

2. **Admin Reset:**
   - Admin có thể reset password cho user
   - System tạo password mới (random hoặc admin set)
   - Password cũ bị thay thế

**Không bao giờ** có cách lấy lại password cũ từ hash.

