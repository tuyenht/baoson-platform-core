# USER-SPECIFIC PEPPER - BẢO MẬT CHỐNG COPY HASH

## Vấn đề đã giải quyết

**Trước đây:** Nếu hacker có quyền truy cập database và biết password của user A, có thể copy hash sang user B và login được.

**Bây giờ:** Mỗi user có pepper riêng, nên **KHÔNG THỂ** copy hash giữa các users.

---

## Cách hoạt động

### User-Specific Pepper

Mỗi user có pepper riêng, tính từ:
```
User Pepper = HMAC-SHA256(user_id + "|" + email, global_pepper)
```

**Ví dụ:**
- User A: ID=1, email="usera@example.com"
  - User Pepper A = `HMAC-SHA256("1|usera@example.com", global_pepper)`
  
- User B: ID=2, email="userb@example.com"
  - User Pepper B = `HMAC-SHA256("2|userb@example.com", global_pepper)`

**Kết quả:** User Pepper A ≠ User Pepper B (ngay cả khi cùng global pepper)

---

## Quy trình hash password

### Khi tạo/set password:

```php
// User A: password = "mypassword123"
$userPepperA = getUserSpecificPepper($userA);  // "abc123..." (unique cho user A)
$peppered = hash_hmac('sha256', "mypassword123", $userPepperA);  // "xyz789..."
$hash = Hash::make($peppered);  // "$2y$10$abc..." (stored in DB)
```

### Khi verify password:

```php
// User A login với "mypassword123"
$userPepperA = getUserSpecificPepper($userA);  // "abc123..." (same as when created)
$peppered = hash_hmac('sha256', "mypassword123", $userPepperA);  // "xyz789..." (same)
Hash::check($peppered, $storedHash);  // ✅ TRUE
```

---

## Tại sao không thể copy hash?

### Kịch bản tấn công:

1. **Hacker có quyền truy cập database**
2. **Hacker biết password của user A:** `"mypassword123"`
3. **Hacker copy hash từ user A sang user B:**

```sql
-- Copy hash từ user A sang user B
UPDATE users SET password = '$2y$10$abc...' WHERE email = 'userb@example.com';
```

4. **Hacker thử login user B với password của user A:**

```php
// Login user B với "mypassword123"
$userPepperB = getUserSpecificPepper($userB);  // "def456..." (KHÁC với user A!)
$peppered = hash_hmac('sha256', "mypassword123", $userPepperB);  // "uvw012..." (KHÁC!)
Hash::check($peppered, $storedHash);  // ❌ FALSE - KHÔNG MATCH!
```

**Kết quả:** ❌ **KHÔNG THỂ LOGIN** vì:
- User Pepper B ≠ User Pepper A
- Hash được tạo với User Pepper B ≠ Hash được tạo với User Pepper A
- Verify sẽ fail

---

## So sánh: Trước vs Sau

### ❌ Trước (Global Pepper)

```
User A: password "mypassword123"
  → Global Pepper: "secret"
  → hash_hmac("mypassword123", "secret") → "abc123..."
  → Hash::make("abc123...") → "$2y$10$xyz..."

User B: password "mypassword123"
  → Global Pepper: "secret" (CÙNG)
  → hash_hmac("mypassword123", "secret") → "abc123..." (CÙNG)
  → Hash::make("abc123...") → "$2y$10$xyz..." (CÓ THỂ CÙNG hoặc khác do salt)

Copy hash từ A sang B → ✅ CÓ THỂ LOGIN
```

### ✅ Sau (User-Specific Pepper)

```
User A: password "mypassword123"
  → User Pepper A: HMAC("1|usera@example.com", "secret") → "pepperA123"
  → hash_hmac("mypassword123", "pepperA123") → "abc123..."
  → Hash::make("abc123...") → "$2y$10$xyz..."

User B: password "mypassword123"
  → User Pepper B: HMAC("2|userb@example.com", "secret") → "pepperB456" (KHÁC!)
  → hash_hmac("mypassword123", "pepperB456") → "def789..." (KHÁC!)
  → Hash::make("def789...") → "$2y$10$uvw..." (KHÁC!)

Copy hash từ A sang B → ❌ KHÔNG THỂ LOGIN
```

---

## Lưu ý quan trọng

### 1. User ID hoặc Email thay đổi

Nếu user ID hoặc email thay đổi, password sẽ **KHÔNG CÒN HOẠT ĐỘNG** vì:
- User Pepper thay đổi
- Hash cũ không match với pepper mới

**Giải pháp:** User phải reset password sau khi thay đổi ID/email.

### 2. Migration từ Global Pepper

Nếu hệ thống đang dùng global pepper và muốn migrate sang user-specific pepper:

1. **Option 1:** Force tất cả users reset password (an toàn nhất)
2. **Option 2:** Support cả 2 methods trong thời gian transition:
   - Thử user-specific pepper trước
   - Nếu fail, thử global pepper (legacy)
   - Nếu legacy match, tự động re-hash với user-specific pepper

### 3. Backward Compatibility

Code hiện tại vẫn support:
- Users không có pepper (nếu `AUTH_PASSWORD_PEPPER` không set)
- Users với global pepper cũ (nếu cần migration)

---

## Kết luận

### ✅ Bảo mật đã được cải thiện

1. **Mỗi user có pepper riêng** → Hash không thể copy giữa users
2. **Ngay cả khi hacker có DB access** → Không thể copy hash để login user khác
3. **Ngay cả khi hacker biết password của user A** → Không thể dùng để login user B

### ✅ Phòng chống tấn công

- ✅ **Password Reuse Attack:** Không thể copy hash
- ✅ **Account Takeover:** Không thể dùng hash của user khác
- ✅ **Privilege Escalation:** Không thể dùng hash của admin để login user thường

---

## Test case

```php
// User A
$userA = User::create([
    'email' => 'usera@example.com',
    'password' => 'mypassword123'
]);
$hashA = $userA->password;  // "$2y$10$abc..."

// Copy hash sang user B
$userB = User::create([
    'email' => 'userb@example.com',
    'password' => 'different_password'
]);
$userB->password = $hashA;  // Copy hash
$userB->save();

// Thử login user B với password của user A
Auth::attempt([
    'email' => 'userb@example.com',
    'password' => 'mypassword123'  // Password của user A
]);  // ❌ FAIL - KHÔNG THỂ LOGIN
```

**Kết quả:** ✅ **KHÔNG THỂ LOGIN** - Bảo mật đã được đảm bảo!

