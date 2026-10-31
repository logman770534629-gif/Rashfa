/** @type {import('tailwindcss').Config} */
export default {
  content: ['./resources/**/*.blade.php', './resources/**/*.js'],
  theme: {
    extend: {
      colors: { ink: '#050505', forest: '#0E3B2E', gold: '#D4A24C', lime: '#C6F135', cream: '#F4F0E8' },
      fontFamily: { display: ['"Noto Kufi Arabic"', 'sans-serif'], body: ['"IBM Plex Sans Arabic"', 'sans-serif'] },
    },
  },
  plugins: [],
};
