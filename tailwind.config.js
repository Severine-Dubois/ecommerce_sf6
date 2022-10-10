/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.{vue,js,ts,jsx,tsx}",
    "./templates/**/*.{html,twig}",
],
safelist: [
  'text-green-700',
  'bg-green-100',
  'dark:bg-green-200',
  'dark:text-green-800'
],
  theme: {
    extend: {},
  },
  plugins: [],
}
