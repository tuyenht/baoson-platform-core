# GLOBAL PEPPER - CẤU HÌNH

## Vị trí khai báo

### 1. File Config: `apps/control-plane/config/security.php`

```php
return [
    'password_pepper' => env('AUTH_PASSWORD_PEPPER'),
    'password_pepper_previous' => env('AUTH_PASSWORD_PEPPER_PREVIOUS'),
    'require_password_pepper' => (bool) env('AUTH_PASSWORD_PEPPER_REQUIRED', false),
];
```

**Đây là nơi định nghĩa config key `security.password_pepper`**

---

### 2. Environment Variable: `.env`

```env
# Server-side password pepper (secret)
AUTH_PASSWORD_PEPPER=your_secret_pepper_here

# Previous pepper (for rotation)
AUTH_PASSWORD_PEPPER_PREVIOUS=

# Require pepper in production
AUTH_PASSWORD_PEPPER_REQUIRED=false
```

**Đây là nơi lưu giá trị thực tế của pepper**

---

## Flow đọc giá trị

```
.env file
   ↓
AUTH_PASSWORD_PEPPER="my_secret_key_123"
   ↓
config/security.php
   ↓
'password_pepper' => env('AUTH_PASSWORD_PEPPER')
   ↓
Code sử dụng
   ↓
config('security.password_pepper')  // Returns "my_secret_key_123"
```

---

## Cách sử dụng trong code

### Trong User Model:
```php
// apps/control-plane/app/Models/User.php
$globalPepper = (string) (config('security.password_pepper') ?: '');
```

### Trong PepperedUserProvider:
```php
// apps/control-plane/app/Auth/PepperedUserProvider.php
$globalPepper = $this->pepper;  // Được inject từ AppServiceProvider
```

### Trong AppServiceProvider:
```php
// apps/control-plane/app/Providers/AppServiceProvider.php
Auth::provider('peppered', function ($app, array $config) {
    return new PepperedUserProvider(
        $app['hash'],
        $config['model'],
        (string) ($app['config']->get('security.password_pepper') ?: ''),  // ← Đọc từ config
        (string) ($app['config']->get('security.password_pepper_previous') ?: '')
    );
});
```

---

## Cách set giá trị

### 1. Trong `.env` file (khuyến nghị)

```env
# Tạo một secret key ngẫu nhiên (32-64 ký tự)
AUTH_PASSWORD_PEPPER=your_random_secret_key_here_32_to_64_chars
```

**Lưu ý:**
- ✅ **KHÔNG** commit `.env` vào git
- ✅ Lưu trong secret manager (AWS Secrets Manager, HashiCorp Vault, etc.)
- ✅ Mỗi môi trường (dev/staging/prod) nên có pepper khác nhau

### 2. Generate random pepper

```powershell
# PowerShell: Generate random 64-char string
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 64 | ForEach-Object {[char]$_})

# Hoặc dùng OpenSSL
openssl rand -hex 32
```

### 3. Set trong `.env`

```env
AUTH_PASSWORD_PEPPER=aBc123XyZ456...  # 64 ký tự random
```

---

## Các biến liên quan

### `AUTH_PASSWORD_PEPPER`
- **Mục đích:** Global pepper cho tất cả users
- **Vị trí:** `.env` file
- **Config key:** `security.password_pepper`
- **Bắt buộc:** Không (nhưng khuyến nghị cho production)

### `AUTH_PASSWORD_PEPPER_PREVIOUS`
- **Mục đích:** Pepper cũ (để rotation không downtime)
- **Vị trí:** `.env` file
- **Config key:** `security.password_pepper_previous`
- **Bắt buộc:** Không

### `AUTH_PASSWORD_PEPPER_REQUIRED`
- **Mục đích:** Fail-fast nếu thiếu pepper
- **Vị trí:** `.env` file
- **Config key:** `security.require_password_pepper`
- **Giá trị mặc định:** `false`
- **Khuyến nghị:** `true` cho production

---

## Kiểm tra pepper đã được set chưa

### Command line:
```powershell
cd apps/control-plane
php artisan tinker
>>> config('security.password_pepper')
# Nếu có giá trị → pepper đã được set
# Nếu null/empty → pepper chưa được set
```

### Hoặc check `.env`:
```powershell
Select-String -Path .env -Pattern "AUTH_PASSWORD_PEPPER="
```

---

## Lưu ý bảo mật

### ✅ Nên làm:
1. **Tạo pepper ngẫu nhiên** (32-64 ký tự)
2. **Lưu trong secret manager** (không commit vào git)
3. **Mỗi môi trường có pepper riêng** (dev/staging/prod)
4. **Set `AUTH_PASSWORD_PEPPER_REQUIRED=true`** trong production

### ❌ Không nên:
1. **Commit pepper vào git** (ngay cả trong `.env.example`)
2. **Dùng pepper giống nhau** cho tất cả môi trường
3. **Dùng pepper dễ đoán** (như "password", "secret", etc.)
4. **Share pepper** giữa các projects

---

## Ví dụ cấu hình

### Development (.env)
```env
# Optional - có thể để trống cho dev
AUTH_PASSWORD_PEPPER=
AUTH_PASSWORD_PEPPER_REQUIRED=false
```

### Production (.env hoặc secret manager)
```env
# Bắt buộc - phải có pepper
AUTH_PASSWORD_PEPPER=aBc123XyZ456DeF789GhI012JkL345MnO678PqR901StU234VwX567YzA890
AUTH_PASSWORD_PEPPER_REQUIRED=true
```

---

## Tóm tắt

**Global pepper được khai báo ở:**

1. **Config file:** `apps/control-plane/config/security.php`
   - Key: `'password_pepper'`
   - Đọc từ: `env('AUTH_PASSWORD_PEPPER')`

2. **Environment variable:** `.env`
   - Key: `AUTH_PASSWORD_PEPPER`
   - Giá trị: Secret key (32-64 ký tự random)

3. **Sử dụng trong code:**
   - `config('security.password_pepper')` → Trả về giá trị từ `.env`

