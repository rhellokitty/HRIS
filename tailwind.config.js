import defaultTheme from 'tailwindcss/defaultTheme';
import plugin from 'tailwindcss/plugin';

/**
 * HRIS Corporate — Tailwind CSS Configuration
 * Mengacu pada Design PRD §3 (Tailwind CSS Configuration Guide):
 *  - Palet "Biru Korporat" (Corporate Navy & Electric Blue) dipetakan 1:1 ke CSS variables
 *  - Borderless Glassmorphism, Zero Box-Shadow, Universal Zero-Scrollbar
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    darkMode: ['class', '[data-theme="dark"]'],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                hris: {
                    bg: 'var(--bg-app)',
                    card: 'var(--bg-card)',
                    'card-hover': 'var(--bg-card-hover)',
                    'card-solid': 'var(--bg-card-solid)',
                    surface: 'var(--bg-surface)',
                    'surface-hover': 'var(--bg-surface-hover)',
                    'surface-active': 'var(--bg-surface-active)',
                    text: 'var(--text-primary)',
                    'text-secondary': 'var(--text-secondary)',
                    'text-muted': 'var(--text-muted)',
                    'text-inverse': 'var(--text-inverse)',
                    primary: 'var(--accent-primary)',
                    'primary-hover': 'var(--accent-primary-hover)',
                    'primary-soft': 'var(--accent-primary-soft)',
                    'primary-text': 'var(--accent-primary-text)',
                    secondary: 'var(--accent-secondary)',
                    'secondary-soft': 'var(--accent-secondary-soft)',
                    success: 'var(--status-success)',
                    'success-soft': 'var(--status-success-soft)',
                    warning: 'var(--status-warning)',
                    'warning-soft': 'var(--status-warning-soft)',
                    danger: 'var(--status-danger)',
                    'danger-soft': 'var(--status-danger-soft)',
                    info: 'var(--status-info)',
                    'info-soft': 'var(--status-info-soft)',
                    grid: 'var(--chart-grid)',
                    slot: 'var(--chart-slot)',
                    dock: 'var(--bg-dock)',
                },
                // Palet statis Corporate Navy (untuk kebutuhan non-tematik)
                navy: {
                    50: '#F8FAFC',
                    100: '#E2E8F0',
                    300: '#94A3B8',
                    500: '#64748B',
                    700: '#162444',
                    800: '#131E36',
                    850: '#101A30',
                    900: '#0F1A30',
                    950: '#0A0F1D',
                },
                royal: {
                    400: '#3B82F6',
                    500: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                },
            },

            fontFamily: {
                sans: ['Poppins', '-apple-system', 'BlinkMacSystemFont', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', ...defaultTheme.fontFamily.mono],
            },

            fontSize: {
                // Skala tipografi Design PRD §4.2
                display: ['2.25rem', { lineHeight: '1.15', letterSpacing: '-0.03em', fontWeight: '700' }],
                'page-title': ['1.35rem', { lineHeight: '1.25', letterSpacing: '-0.02em', fontWeight: '700' }],
                'section': ['1.05rem', { lineHeight: '1.35', letterSpacing: '-0.01em', fontWeight: '600' }],
                'body': ['0.92rem', { lineHeight: '1.5' }],
                'body-sm': ['0.80rem', { lineHeight: '1.4' }],
                'micro': ['0.68rem', { lineHeight: '1.2', letterSpacing: '0.06em', fontWeight: '600' }],
            },

            borderRadius: {
                xs: '4px',
                sm: '8px',
                md: '12px',
                lg: '16px',
                xl: '22px',
                full: '9999px',
            },

            backdropBlur: {
                glass: '20px',
            },

            maxWidth: {
                employee: '28rem', // 448px (max-w-md strictly locked)
                admin: '1440px', // Desktop wide container
            },

            width: {
                sidebar: '260px',
            },

            spacing: {
                sidebar: '260px',
                'dock-clearance': 'max(150px, calc(120px + env(safe-area-inset-bottom)))',
            },

            transitionTimingFunction: {
                hris: 'cubic-bezier(0.4, 0, 0.2, 1)',
            },

            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(6px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'pulse-soft': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.45' },
                },
            },

            animation: {
                'fade-up': 'fade-up 0.25s cubic-bezier(0.4, 0, 0.2, 1) both',
                'pulse-soft': 'pulse-soft 1.6s ease-in-out infinite',
            },
        },
    },

    plugins: [
        // Custom Utility Plugin: Borderless Glass & No-Scrollbars (Design PRD §3)
        plugin(function ({ addUtilities }) {
            addUtilities({
                '.glass-card': {
                    background: 'var(--bg-card)',
                    'backdrop-filter': 'blur(20px)',
                    '-webkit-backdrop-filter': 'blur(20px)',
                    border: 'none',
                    'border-radius': 'var(--radius-lg)',
                    transform: 'translateZ(0)',
                    '-webkit-transform': 'translateZ(0)',
                },
                '.glass-panel': {
                    background: 'var(--bg-card)',
                    'backdrop-filter': 'blur(20px)',
                    '-webkit-backdrop-filter': 'blur(20px)',
                    border: 'none',
                    'border-radius': 'var(--radius-xl)',
                    transform: 'translateZ(0)',
                    '-webkit-transform': 'translateZ(0)',
                },
                '.glass-sidebar': {
                    background: 'var(--bg-card)',
                    'backdrop-filter': 'blur(40px)',
                    '-webkit-backdrop-filter': 'blur(40px)',
                    border: 'none',
                },
                '.glass-dock': {
                    background: 'var(--bg-dock)',
                    'backdrop-filter': 'blur(40px) saturate(160%)',
                    '-webkit-backdrop-filter': 'blur(40px) saturate(160%)',
                    border: 'none',
                    'border-radius': 'var(--radius-full)',
                },
                '.glass-surface': {
                    background: 'var(--bg-surface)',
                    border: 'none',
                    'border-radius': 'var(--radius-md)',
                },
                '.no-scrollbar': {
                    '-ms-overflow-style': 'none',
                    'scrollbar-width': 'none',
                    '&::-webkit-scrollbar': {
                        display: 'none !important',
                        width: '0 !important',
                        height: '0 !important',
                    },
                },
                '.pb-dock': {
                    'padding-bottom': 'max(150px, calc(120px + env(safe-area-inset-bottom))) !important',
                },
                '.tap-scale': {
                    transition: 'transform 0.1s ease',
                    '&:active': {
                        transform: 'scale(0.96)',
                    },
                },
            });
        }),
    ],
};
