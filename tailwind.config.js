/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/Views/**/*.php', './public/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        neo: {
          yellow: '#FFDE4D',
          cyan: '#06B6D4',
          orange: '#F97316',
          green: '#22C55E',
          pink: '#EC4899',
          lime: '#A3E635',
          violet: '#8B5CF6',
          red: '#EF4444',
          white: '#F4F2EE',
          black: '#000000',
        },
        cyan: {
          300: '#67E8F9',
        },
      },
      fontFamily: {
        heading: ['"Space Grotesk"', 'Inter', 'sans-serif'],
        body: ['Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        neo: '4px 4px 0px 0px rgba(0,0,0,1)',
        'neo-lg': '6px 6px 0px 0px rgba(0,0,0,1)',
        'neo-sm': '2px 2px 0px 0px rgba(0,0,0,1)',
      },
      borderWidth: {
        '3': '3px',
        '4': '4px',
      },
    },
  },
  plugins: [],
};
