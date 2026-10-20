/** Build tĩnh Tailwind cho trang Đại hội (thay thế Play CDN). */
module.exports = {
  darkMode: 'class',
  content: [
    'E:/eventregis/protected/modules/frontend/views/**/*.php',
    'E:/eventregis/public/daihoi/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
        display: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
      },
      colors: {
        brand: {
          50: '#eff6ff',
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          900: '#0f172a',
          navy: '#0b1329',
        },
      },
    },
  },
};
