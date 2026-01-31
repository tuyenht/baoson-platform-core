# PASSWORD HASH COPY - PHÂN TÍCH BẢO MẬT

## Câu trả lời: **CÓ THỂ, NHƯNG RẤT NGUY HIỂM**

Nếu bạn copy hash password từ user A sang user B, bạn **CÓ THỂ** login user B bằng password của user A. Đây là một **lỗ hổng bảo mật nghiêm trọng**.

---

## Kịch bản

### User A
- Email: `usera@example.com`
- Password: `"mypassword123"` (bạn đã biết)
- Hash trong DB: `"$2y$10$abcdefghijklmnopqrstuvwxyz..."`

### User B
- Email: `userb@example.com`
- Password: `"unknown"` (bạn không biết)
- Hash trong DB: `"$2y$10$xyz123..."`

### Hành động
```sql
-- Copy hash từ user A sang user B
UPDATE users SET password = '$2y$10$abcdefghijklmnopqrstuvwxyz...' 
WHERE email = 'userb@example.com';
```

### Kết quả
✅ **CÓ THỂ** login user B với password `"mypassword123"` (password của user A)

---

## Tại sao có thể làm được?

### 1. Hash là Deterministic (với cùng input)
```
Password: "mypassword123"
   ↓
Pepper (nếu có): "secret_pepper"
   ↓
hash_hmac('sha256', "mypassword123", "secret_pepper") → "abc123..."
   ↓
Hash::make("abc123...") → "$2y$10$abcdef..."
```

**Nếu cùng pepper và cùng password → cùng hash**

### 2. Bcrypt Verify chỉ cần Hash
```php
// Khi login user B
$plainPassword = "mypassword123";  // Password của user A
$storedHash = "$2y$10$abcdef...";  // Hash đã copy từ user A

Hash::check($plainPassword, $storedHash);  // ✅ TRUE
```

**Bcrypt verify không cần biết user nào, chỉ cần hash match**

### 3. Pepper là Global (nếu có)
- Pepper áp dụng cho TẤT CẢ users
- Nếu user A và user B dùng cùng pepper → cùng password → cùng hash

---

## Demo thực tế

### Test case

```php
// User A
$userA = User::create([
    'email' => 'usera@example.com',
    'password' => 'mypassword123'
]);
// Hash: "$2y$10$abc..."

// Copy hash sang user B
$userB = User::create([
    'email' => 'userb@example.com',
    'password' => 'different_password'  // Password khác
]);
$userB->password = $userA->password;  // Copy hash
$userB->save();

// Login user B với password của user A
Auth::attempt([
    'email' => 'userb@example.com',
    'password' => 'mypassword123'  // Password của user A
]);  // ✅ SUCCESS
```

---

## Tại sao đây là lỗ hổng bảo mật?

### 1. Password Reuse Attack
- Attacker biết password của user A
- Copy hash sang user B
- Có thể login user B mà không cần biết password của user B

### 2. Privilege Escalation
- Nếu user A có quyền cao (saasOwner)
- Attacker copy hash sang user B (có quyền thấp)
- Có thể login user B với quyền của user A (nếu hệ thống không check role)

### 3. Account Takeover
- Attacker có thể chiếm quyền user B
- Không cần biết password gốc của user B
- Chỉ cần biết password của user A

---

## Cách phòng chống

### 1. ✅ Salt per User (Đã có sẵn)
Bcrypt tự động tạo salt riêng cho mỗi password:
```php
// User A: password "mypassword123"
Hash::make("mypassword123") → "$2y$10$abc123..."

// User B: cùng password "mypassword123"
Hash::make("mypassword123") → "$2y$10$xyz789..."  // Hash KHÁC
```

**Nhưng:** Nếu copy hash từ A sang B, vẫn verify được vì hash chứa salt.

### 2. ✅ User-specific Pepper (Nên implement)
Thay vì global pepper, dùng pepper per user:
```php
$userPepper = hash_hmac('sha256', $user->id . $user->email, $globalPepper);
$toHash = hash_hmac('sha256', $plainPassword, $userPepper);
```

**Kết quả:** Hash của user A không thể dùng cho user B (vì pepper khác).

### 3. ✅ Password History Check
Không cho phép user set password đã dùng trước đó:
```php
// Check password history
if (in_array($newPasswordHash, $user->passwordHistory)) {
    throw new Exception('Password đã được sử dụng trước đó');
}
```

### 4. ✅ Audit Log
Log mọi thay đổi password:
```php
Audit::record('user.password.changed', [
    'user_id' => $user->id,
    'changed_by' => Auth::id(),
]);
```

### 5. ✅ Access Control
Chỉ admin/saasOwner mới có thể reset password:
```php
// Chỉ saasOwner/saasAdmin mới được reset password
if (!Auth::user()->hasRole(['saasOwner', 'saasAdmin'])) {
    abort(403);
}
```

---

## Kết luận

### Về mặt kỹ thuật
- ✅ **CÓ THỂ** copy hash password từ user A sang user B
- ✅ **CÓ THỂ** login user B với password của user A
- ✅ Đây là hành vi **NGUY HIỂM** và **KHÔNG NÊN** làm

### Về mặt bảo mật
- ❌ Đây là **lỗ hổng bảo mật nghiêm trọng**
- ❌ Có thể dẫn đến **account takeover**
- ❌ Có thể dẫn đến **privilege escalation**

### Khuyến nghị
1. **KHÔNG BAO GIỜ** copy hash password giữa các users
2. **KHÔNG BAO GIỜ** cho phép user tự set hash password
3. **LUÔN** reset password qua flow chính thức (reset token)
4. **LUÔN** audit log mọi thay đổi password
5. **NÊN** implement user-specific pepper để tăng bảo mật

---

## Best Practices

### ✅ Đúng
```php
// Reset password qua flow chính thức
$user->password = $newPassword;  // Hash tự động
$user->save();
```

### ❌ Sai
```php
// Copy hash trực tiếp
$userB->password = $userA->password;  // NGUY HIỂM!
$userB->save();
```

---

**TÓM LẠI:** Về mặt kỹ thuật có thể làm được, nhưng đây là hành vi nguy hiểm và không nên thực hiện. Hệ thống nên có cơ chế phòng chống để ngăn chặn hành vi này.

