import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Points a Tailwind color family at the CSS custom properties defined in
// resources/css/app.css (:root for light, .dark for dark) instead of static
// hex values - every existing `bg-gray-50` / `text-blue-600` / `bg-green-100`
// class across the app keeps working completely unchanged, but what it
// actually renders as is now controlled by which of those two blocks is in
// scope. That's what makes dark mode (and the admin-configurable theme
// color, which overrides --color-primary-* specifically) reach every page
// without editing the ~75 view files that use these classes directly.
const SHADES = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
function cssVarColor(name) {
    return Object.fromEntries(SHADES.map((shade) => [shade, `var(--color-${name}-${shade})`]));
}

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                'brand': {
                    '50': '#eff6ff',
                    '100': '#dbeafe',
                    '200': '#bfdbfe',
                    '300': '#93c5fd',
                    '400': '#60a5fa',
                    '500': '#3b82f6',
                    '600': '#2563eb',
                    '700': '#1d4ed8',
                    '800': '#1e40af',
                    '900': '#1e3a8a',
                },
                white: 'var(--color-white)',
                black: 'var(--color-black)',
                gray: cssVarColor('gray'),
                // 'blue' is the app's accent color everywhere (buttons,
                // links, focus rings) - remapped to the "primary" variable
                // slot, which is what Settings > General > Theme Color
                // actually overrides. See layouts/partials/theme-style.blade.php.
                blue: cssVarColor('primary'),
                green: cssVarColor('green'),
                red: cssVarColor('red'),
                yellow: cssVarColor('yellow'),
                orange: cssVarColor('orange'),
                purple: cssVarColor('purple'),
                indigo: cssVarColor('indigo'),
                amber: cssVarColor('amber'),
            },
            fontFamily: {
                'sans': ['Inter', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06)',
                'card-hover': '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)',
            },
        },
    },

    plugins: [require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
