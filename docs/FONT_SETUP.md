# Font Setup Guide - Inter Font (Self-Hosted)

## 📊 So sánh: Google Fonts vs Self-Hosted

### Google Fonts (Hiện tại)
**Ưu điểm:**
- ✅ Dễ setup, không cần quản lý files
- ✅ CDN nhanh, có cache
- ✅ Tự động tối ưu cho từng browser

**Nhược điểm:**
- ❌ Phụ thuộc vào Google (privacy, downtime)
- ❌ Thêm 1 external request (~200-300ms)
- ❌ Có thể bị block ở một số quốc gia
- ❌ Không control được caching hoàn toàn

### Self-Hosted (Đề xuất) ⭐
**Ưu điểm:**
- ✅ **Nhanh hơn**: Không cần external request, load từ cùng domain (~50-100ms)
- ✅ **Privacy tốt hơn**: Không gửi data đến Google
- ✅ **Kiểm soát tốt hơn**: Control caching, compression
- ✅ **Offline support**: Hoạt động khi không có internet
- ✅ **Tối ưu bundle**: Chỉ load weights cần thiết (300, 400, 500, 600, 700)
- ✅ **Không phụ thuộc**: Không lo Google downtime

**Nhược điểm:**
- ❌ Cần quản lý files (nhưng rất đơn giản)
- ❌ Tốn storage (~123KB total - rất nhỏ)

## 🎯 Kết luận: **Self-Hosted tốt hơn** cho production

**Lý do:**
- Nhanh hơn 2-3x (không cần external request)
- Privacy tốt hơn
- Kiểm soát tốt hơn
- Storage cost không đáng kể (~123KB)

---

## 📁 Cấu trúc lưu trữ (Monorepo)

### Phương án đã chọn: Per-App Fonts

```
baoson-platform-core/
├── apps/
│   ├── control-plane/              # Laravel Backend
│   │   ├── public/
│   │   │   └── fonts/
│   │   │       └── inter/
│   │   │           ├── Inter-Light.woff2      (~20KB)
│   │   │           ├── Inter-Regular.woff2    (~25KB)
│   │   │           ├── Inter-Medium.woff2     (~25KB)
│   │   │           ├── Inter-SemiBold.woff2   (~26KB)
│   │   │           └── Inter-Bold.woff2       (~27KB)
│   │   │           Total: ~123KB
│   │   │
│   │   └── resources/
│   │       └── css/
│   │           ├── fonts.css       # @font-face declarations
│   │           └── app.css         # Import fonts.css
│   │
│   └── frontend/                    # Frontend App (sẽ có sau)
│       ├── public/
│       │   └── fonts/
│       │       └── inter/          # Copy từ control-plane
│       └── src/
│           └── styles/
│               └── fonts.css       # Hoặc import từ shared package
```

**Tại sao chọn phương án này:**
- ✅ Đơn giản, không cần symlink
- ✅ Mỗi app độc lập, dễ deploy
- ✅ Vite tự động copy fonts từ `public/` khi build
- ✅ Dễ maintain và debug

---

## 🚀 Implementation Steps

### Bước 1: Download Fonts

**Option A: Dùng PowerShell Script (Windows)**
```powershell
cd apps/control-plane
.\download-fonts.ps1
```

**Option B: Download thủ công**
1. Truy cập: https://fonts.google.com/specimen/Inter
2. Click "Download family"
3. Giải nén file
4. Copy các file `.woff2` vào `apps/control-plane/public/fonts/inter/`
5. Chỉ cần weights: 300, 400, 500, 600, 700

**Option C: Dùng Google Fonts Helper**
1. Truy cập: https://gwfh.mranftl.com/fonts/inter
2. Chọn weights: 300, 400, 500, 600, 700
3. Download và copy vào `public/fonts/inter/`

### Bước 2: Verify Files

Sau khi download, kiểm tra:
```
apps/control-plane/public/fonts/inter/
├── Inter-Light.woff2
├── Inter-Regular.woff2
├── Inter-Medium.woff2
├── Inter-SemiBold.woff2
└── Inter-Bold.woff2
```

### Bước 3: Build Assets

```bash
cd apps/control-plane
pnpm run build
# hoặc
npm run build
```

Vite sẽ tự động:
- Copy fonts từ `public/fonts/` vào `public/build/fonts/`
- Bundle CSS với @font-face declarations

### Bước 4: Test

1. Start dev server: `pnpm run dev`
2. Mở browser DevTools → Network tab
3. Kiểm tra fonts load từ `/fonts/inter/` (không phải Google)
4. Verify font hiển thị đúng

---

## 📝 Files đã tạo

### 1. `resources/css/fonts.css`
- Chứa @font-face declarations cho tất cả weights
- Sử dụng `font-display: swap` để tối ưu performance

### 2. `resources/css/app.css`
- Import `fonts.css` ở đầu file
- Font sẽ load trước khi Tailwind CSS

### 3. `resources/views/app.blade.php`
- Đã xóa Google Fonts link
- Font load từ CSS

### 4. `download-fonts.ps1`
- Script tự động download fonts từ Google Fonts
- Chỉ download nếu file chưa tồn tại

---

## 🎨 Sử dụng cho Frontend (sau này)

### Option 1: Copy fonts
```bash
# Copy fonts từ control-plane sang frontend
cp -r apps/control-plane/public/fonts apps/frontend/public/
```

### Option 2: Shared Package (Nếu cần)
```
packages/
└── shared-assets/
    └── fonts/
        └── inter/
            └── *.woff2
```

Sau đó import trong frontend:
```css
@import '@shared-assets/fonts/inter/fonts.css';
```

### Option 3: CDN riêng (Production)
- Upload fonts lên CDN (Cloudflare, AWS S3, etc.)
- Update URL trong `fonts.css`

---

## ⚡ Performance Benefits

### Metrics so sánh:

| Metric | Google Fonts | Self-Hosted |
|--------|-------------|-------------|
| **DNS Lookup** | ~20-50ms | 0ms (cùng domain) |
| **Connection** | ~50-100ms | 0ms (cùng domain) |
| **Download** | ~100-150ms | ~50-100ms |
| **Total** | ~200-300ms | ~50-100ms |
| **Privacy** | ❌ Gửi data đến Google | ✅ Không gửi data |
| **Offline** | ❌ Không hoạt động | ✅ Hoạt động |

**Kết quả: Self-hosted nhanh hơn 2-3x**

---

## 🔧 Troubleshooting

### Font không load?
1. Kiểm tra files có trong `public/fonts/inter/`
2. Kiểm tra path trong `fonts.css` đúng không
3. Clear browser cache
4. Check browser console cho errors

### Font hiển thị sai?
1. Verify font files không bị corrupt
2. Check @font-face declarations đúng format
3. Verify font-weight trong CSS match với file names

### Build không copy fonts?
- Vite tự động copy từ `public/` khi build
- Nếu không, check `vite.config.ts` có config đúng không

---

## 📚 References

- Inter Font: https://fonts.google.com/specimen/Inter
- Google Fonts Helper: https://gwfh.mranftl.com/fonts/inter
- WOFF2 Format: https://caniuse.com/woff2
- Font Display: https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display

---

## ✅ Checklist

- [ ] Download fonts (dùng script hoặc thủ công)
- [ ] Verify files trong `public/fonts/inter/`
- [ ] Build assets: `pnpm run build`
- [ ] Test fonts load đúng
- [ ] Verify không còn request đến Google Fonts
- [ ] Check performance improvement
