/**
 * Cấu hình Tailwind (Play CDN local).
 * PHẢI được nạp SAU tailwind.min.js và TRƯỚC khi Tailwind render.
 * Tách từ thẻ <script id="tailwind-config"> inline của thiết kế sang file riêng.
 */
window.tailwind = window.tailwind || {};
window.tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
        display: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif']
      },
      colors: {
        brand: {
          50: '#eff6ff',
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          900: '#0f172a',
          navy: '#0b1329'
        }
      }
    }
  }
};
