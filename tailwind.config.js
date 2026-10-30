import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            // Палитра AutoDuel: бумага, чернила, жёлтая дорожная разметка
            colors: {
                paper: { DEFAULT: '#F3EFE6', deep: '#E9E3D5' },
                ink: { DEFAULT: '#16130F', soft: '#3A352D', muted: '#6B645A' },
                asphalt: '#1F1C18',
                signal: { DEFAULT: '#FFC21A', dark: '#E6A800' },
                line: '#D8D0BE',
                brake: '#D8341F', // ошибка / неверный ответ
                go: '#1F8A4C', // верный ответ
            },
            fontFamily: {
                sans: ['"Golos Text"', ...defaultTheme.fontFamily.sans],
                display: ['Oswald', 'Impact', ...defaultTheme.fontFamily.sans],
            },
            // Жёсткие тени без размытия - фирменный приём интерфейса
            boxShadow: {
                hard: '6px 6px 0 0 #16130F',
                'hard-sm': '3px 3px 0 0 #16130F',
            },
        },
    },
    plugins: [forms],
};
