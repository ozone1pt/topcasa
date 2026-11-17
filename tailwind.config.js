import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    // Force light mode always: the client wants a single, consistent look
    // regardless of the visitor's OS/browser dark-mode preference. Using the
    // 'class' strategy (instead of the default 'media' auto-detection) means
    // every `dark:` utility in the markup simply never activates, since we
    // never add a `dark` class anywhere.
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // TopCasa approved identity: Montserrat throughout (navy/green
                // brand, see the client-approved concept). Plain Tailwind
                // `slate` + `green` already match the identity's hex values
                // exactly (#0F172A, #64748B, #F1F5F9, #22C55E), so no custom
                // color palette is needed - just the stock scales.
                sans: ['Montserrat', ...defaultTheme.fontFamily.sans],
                display: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
