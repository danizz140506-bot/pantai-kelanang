import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                display: ['Cormorant Garamond', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Warm espresso surfaces (darkest → lightest)
                espresso: {
                    950: '#140C0A', // page background
                    900: '#1C110E',
                    850: '#241512', // card surface
                    800: '#2C1B16',
                    700: '#3A2520', // hairline borders
                    600: '#4D332A',
                },
                // Ember orange accent
                ember: {
                    DEFAULT: '#F0851F',
                    400: '#F6A14A',
                    500: '#F0851F',
                    600: '#DE7414',
                },
                cream: {
                    DEFAULT: '#F4ECE6', // primary text
                    muted: '#B6A097',   // secondary text
                    faint: '#7C675E',   // tertiary / footnote
                },
                rosewood: {
                    bg: '#3A1A18',      // error surface
                    border: '#7A2E2A',  // error border
                    text: '#EBA59C',    // error text
                },
            },
            boxShadow: {
                card: '0 24px 60px -20px rgba(0, 0, 0, 0.75)',
            },
        },
    },

    plugins: [forms],
};
