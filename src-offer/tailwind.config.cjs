/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx,vue}",
    "./assets/**/*.scss",
    "./assets/**/*.css",
  ],
  theme: {
    extend: {
      spacing : {
        '150' : '37.5rem'
      },
      fontSize : ({theme}) => ({
        'xxs' : theme('spacing[2.5]'),
        '2.5xl' : theme('spacing.7')
      }),
      maxWidth : ({theme}) => ({
        'xxs' : theme('spacing.52')
      }),
      minWidth : ({theme}) => ({
        '6' : theme('spacing.6')
      }),
      maxHeight : ({theme}) => ({
        '150' : theme('spacing.150')
      }),
      colors : {
        'dark-gray' : '#262626',
        'gray-light' : '#A4A8B2',
        'border' : '#E8E9EB',
        'red' : '#FF3639',
        'green' : '#62BB46',
        'gray-medium' : '#FAFAFA',
        'container' : '#E5E5E5',
        'fixed' : '#3A3F44'
      }
    },
  },
  plugins: [],
}
