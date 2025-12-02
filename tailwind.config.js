import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Nunito Sans"', 'sans-serif'],
            },

            colors: {
                primary: "#1E0021",
                secondary: "#6F00BC",
                accent: "#00C8FF",
                card: "#28002B",
            },
            backgroundImage: {
                navgradient: "linear-gradient(to right, #00C8FF, #6F00BC)",
            },
        },
    },

    plugins: [forms],
};


