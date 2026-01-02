/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./app/View/Components/**/*.php",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                // Vuexy Primary - Purple
                primary: {
                    50: '#f3f2ff',
                    100: '#e9e8ff',
                    200: '#d5d4ff',
                    300: '#b4b1ff',
                    400: '#8d85ff',
                    500: '#7367f0', // Main Vuexy Primary
                    600: '#5a4cd9',
                    700: '#4a3db5',
                    800: '#3d3494',
                    900: '#352f79',
                    950: '#1f1b4b',
                },
                // Vuexy Secondary - Gray
                secondary: {
                    50: '#f8f8f9',
                    100: '#f1f1f3',
                    200: '#e6e6e9',
                    300: '#d3d3d9',
                    400: '#a8a8b3',
                    500: '#82868b', // Main Vuexy Secondary
                    600: '#6e7074',
                    700: '#5d5e61',
                    800: '#4e4f52',
                    900: '#434446',
                    950: '#2a2a2c',
                },
                // Vuexy Success - Green
                success: {
                    50: '#edfcf3',
                    100: '#d3f8e1',
                    200: '#aaf0c8',
                    300: '#73e2a8',
                    400: '#3bcf84',
                    500: '#28c76f', // Main Vuexy Success
                    600: '#14a357',
                    700: '#108248',
                    800: '#10673b',
                    900: '#0e5432',
                    950: '#052f1b',
                },
                // Vuexy Danger - Red
                danger: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ea5455', // Main Vuexy Danger
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                    950: '#450a0a',
                },
                // Vuexy Warning - Orange
                warning: {
                    50: '#fff8eb',
                    100: '#ffecc6',
                    200: '#ffd688',
                    300: '#ffba4a',
                    400: '#ffa620',
                    500: '#ff9f43', // Main Vuexy Warning
                    600: '#e27a06',
                    700: '#bb5808',
                    800: '#98440e',
                    900: '#7c380f',
                    950: '#481b03',
                },
                // Vuexy Info - Cyan
                info: {
                    50: '#ecfeff',
                    100: '#cffafe',
                    200: '#a5f3fc',
                    300: '#67e8f9',
                    400: '#22d3ee',
                    500: '#00cfe8', // Main Vuexy Info
                    600: '#0891b2',
                    700: '#0e7490',
                    800: '#155e75',
                    900: '#164e63',
                    950: '#083344',
                },
                // Dark theme backgrounds
                dark: {
                    50: '#4b4b4b',
                    100: '#3a3a3c',
                    200: '#2f3349', // Card background dark
                    300: '#282a42', // Skin bordered card dark
                    400: '#25293c', // Sidebar dark
                    500: '#232333', // Body background dark
                    600: '#1e1e2d',
                    700: '#191927',
                    800: '#14141f',
                    900: '#0f0f17',
                    950: '#0a0a0f',
                },
                // Light backgrounds
                light: {
                    50: '#ffffff',
                    100: '#f8f7fa', // Body background light
                    200: '#f4f4f5',
                    300: '#ebebec',
                    400: '#dfdfe0',
                    500: '#d4d4d5',
                    600: '#a3a3a4',
                    700: '#737374',
                    800: '#5e5e5f',
                    900: '#464647',
                },
            },
            fontFamily: {
                sans: ['Public Sans', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
                mono: ['JetBrains Mono', 'Fira Code', 'monospace'],
            },
            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
            },
            borderRadius: {
                'vuexy': '0.375rem', // 6px - Vuexy default
                'vuexy-lg': '0.5rem', // 8px
                'vuexy-xl': '0.625rem', // 10px
            },
            boxShadow: {
                'vuexy': '0 0.25rem 1.125rem rgba(75, 70, 92, 0.1)',
                'vuexy-lg': '0 0.375rem 1.5rem rgba(75, 70, 92, 0.15)',
                'vuexy-card': '0 2px 6px rgba(75, 70, 92, 0.08)',
                'vuexy-dropdown': '0 0.25rem 1rem rgba(75, 70, 92, 0.22)',
                'dark-vuexy': '0 0.25rem 1.125rem rgba(0, 0, 0, 0.4)',
                'dark-vuexy-card': '0 2px 6px rgba(0, 0, 0, 0.3)',
            },
            animation: {
                'fade-in': 'fadeIn 0.3s ease-out',
                'fade-in-up': 'fadeInUp 0.4s ease-out',
                'fade-in-down': 'fadeInDown 0.4s ease-out',
                'slide-in-right': 'slideInRight 0.3s ease-out',
                'slide-in-left': 'slideInLeft 0.3s ease-out',
                'scale-in': 'scaleIn 0.2s ease-out',
                'pulse-soft': 'pulseSoft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                fadeInDown: {
                    '0%': { opacity: '0', transform: 'translateY(-10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideInRight: {
                    '0%': { opacity: '0', transform: 'translateX(10px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                slideInLeft: {
                    '0%': { opacity: '0', transform: 'translateX(-10px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                scaleIn: {
                    '0%': { opacity: '0', transform: 'scale(0.95)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
            },
            spacing: {
                '4.5': '1.125rem',
                '13': '3.25rem',
                '15': '3.75rem',
                '18': '4.5rem',
                '22': '5.5rem',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
        require('@tailwindcss/typography'),
    ],
};
