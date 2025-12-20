# Font Warnings trong Vite Build

## Tại sao có warning?

Khi build, Vite hiển thị warnings:
```
/fonts/Inter-roman.woff2 referenced in /fonts/Inter-roman.woff2 didn't resolve at build time, it will remain unchanged to be resolved at runtime
```

## Đây có phải lỗi không?

**KHÔNG!** Đây chỉ là **warning**, không phải error. Fonts sẽ hoạt động bình thường.

## Giải thích

### 1. Cách Vite hoạt động
- **Vite chỉ process files trong `resources/` folder**
- Files trong `public/` được serve trực tiếp bởi web server (Laravel), không qua Vite

### 2. Tại sao Vite không resolve được?
- Fonts nằm trong `public/fonts/` (không phải `resources/`)
- CSS reference fonts bằng absolute path: `url('/fonts/Inter-roman.woff2')`
- Vite không thể resolve tại build time vì file không nằm trong `resources/`

### 3. Tại sao vẫn hoạt động?
- **Tại runtime**: Browser request `/fonts/Inter-roman.woff2`
- **Laravel serve**: File được serve từ `public/fonts/Inter-roman.woff2`
- **Kết quả**: Fonts load thành công! ✅

## Cấu trúc

```
apps/control-plane/
├── public/
│   └── fonts/              ← Files được serve trực tiếp bởi Laravel
│       ├── Inter-roman.woff2
│       └── ...
│
└── resources/
    └── css/
        └── fonts.css       ← Vite process file này, nhưng không thể resolve fonts trong public/
```

## Có cần fix không?

**Không cần!** Đây là expected behavior. Fonts sẽ hoạt động bình thường.

Nếu muốn suppress warnings (tùy chọn), đã config trong `vite.config.ts`:
```typescript
build: {
    rollupOptions: {
        onwarn(warning, warn) {
            if (warning.message.includes("didn't resolve at build time")) {
                return; // Ignore
            }
            warn(warning);
        },
    },
}
```

## Verify fonts hoạt động

1. **Build thành công**: `pnpm run build` (có warnings nhưng không fail)
2. **Dev server**: `pnpm dev` - fonts load bình thường
3. **Browser DevTools**:
   - Network tab: Thấy requests đến `/fonts/Inter-roman.woff2` ✅
   - Fonts tab: Fonts load thành công ✅

## Kết luận

- ✅ Fonts đã đúng vị trí (`public/fonts/`)
- ✅ CSS đã reference đúng (`url('/fonts/...')`)
- ✅ Warnings là bình thường, không ảnh hưởng
- ✅ Fonts sẽ hoạt động tại runtime

**Không cần lo lắng về warnings này!** 🎉

