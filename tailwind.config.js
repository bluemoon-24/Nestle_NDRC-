/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.php",
    "./app/Views/**/*.php",
    "./src/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        nestle: {
          blue: '#0050A1',     // Nestle Blue
          brown: '#7B4B2A',    // Nestle Brown/Coffee
          light: '#F4F1ED',    // Warm light background
          dark: '#002E5D',
          accent: '#A67C52'    // Tan/Gold accent
        },
        surface: {
          light: '#FDFCFB',    // Very light warm gray
          card: '#FFFFFF',
          border: '#EAE2D6'
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        'premium': '0 10px 30px -5px rgba(0, 80, 161, 0.1), 0 4px 12px -5px rgba(0, 80, 161, 0.05)',
      },
      borderRadius: {
        'xl': '1rem',
        '2xl': '1.5rem',
      }
    },
  },
  plugins: [],
}
