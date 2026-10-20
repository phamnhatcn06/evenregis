# Tailwind CSS tĩnh — trang Đại hội

`tailwind.min.css` được **build sẵn** bằng Tailwind CLI, thay cho Play CDN runtime
(`cdn.tailwindcss.com` / `tailwind.min.js`) vì CDN runtime không dùng được cho production.

## Build lại khi thêm/sửa class trong view

Cần Tailwind **v3** standalone CLI (không cần Node). Tải:
`tailwindcss-windows-x64.exe` từ release v3.4.17 của tailwindlabs/tailwindcss.

Chạy từ thư mục gốc dự án:

```
tailwindcss.exe -c public/vendor/tailwind/tailwind.config.js \
  -i public/vendor/tailwind/tailwind-input.css \
  -o public/vendor/tailwind/tailwind.min.css --minify
```

`content` trong `tailwind.config.js` quét:
- `protected/modules/frontend/views/**/*.php`
- `public/daihoi/**/*.js`

Cấu hình theme (màu `brand.*`, font Inter) đã bake sẵn — trùng với
`daihoi-config.js` cũ (đã xoá).
