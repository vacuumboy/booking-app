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
                sans: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            
            // Адаптивные размеры шрифтов (на основе CSS-переменных)
            fontSize: {
                'adaptive-xs': 'var(--font-size-xs)',
                'adaptive-sm': 'var(--font-size-sm)',
                'adaptive-base': 'var(--font-size-base)',
                'adaptive-lg': 'var(--font-size-lg)',
                'adaptive-xl': 'var(--font-size-xl)',
                'adaptive-2xl': 'var(--font-size-2xl)',
                'adaptive-3xl': 'var(--font-size-3xl)',
                'adaptive-4xl': 'var(--font-size-4xl)',
            },
            
            // Адаптивные отступы и размеры
            spacing: {
                'adaptive-xs': 'var(--spacing-xs)',
                'adaptive-sm': 'var(--spacing-sm)',
                'adaptive-base': 'var(--spacing-base)',
                'adaptive-lg': 'var(--spacing-lg)',
                'adaptive-xl': 'var(--spacing-xl)',
                'adaptive-2xl': 'var(--spacing-2xl)',
                'adaptive-3xl': 'var(--spacing-3xl)',
            },
            
            // Адаптивные высоты
            height: {
                'nav': 'var(--height-nav)',
                'header': 'var(--height-header)',
                'calendar-cell': 'var(--calendar-cell-height)',
            },
            
            // Адаптивные ширины
            width: {
                'sidebar': 'var(--width-sidebar)',
                'mobile-menu': 'var(--width-mobile-menu)',
                'calendar-time': 'var(--calendar-time-width)',
            },
            
            // Адаптивные радиусы
            borderRadius: {
                'adaptive-sm': 'var(--radius-sm)',
                'adaptive-base': 'var(--radius-base)',
                'adaptive-lg': 'var(--radius-lg)',
                'adaptive-xl': 'var(--radius-xl)',
            },
            
            // Адаптивные тени
            boxShadow: {
                'adaptive-sm': 'var(--shadow-sm)',
                'adaptive-base': 'var(--shadow-base)',
                'adaptive-lg': 'var(--shadow-lg)',
                'adaptive-xl': 'var(--shadow-xl)',
            },
            
            // Адаптивные цвета (используем CSS-переменные)
            colors: {
                'adaptive': {
                    primary: 'var(--color-primary)',
                    'primary-hover': 'var(--color-primary-hover)',
                    secondary: 'var(--color-secondary)',
                    success: 'var(--color-success)',
                    warning: 'var(--color-warning)',
                    danger: 'var(--color-danger)',
                    background: 'var(--color-background)',
                    surface: 'var(--color-surface)',
                    text: 'var(--color-text)',
                    'text-secondary': 'var(--color-text-secondary)',
                    border: 'var(--color-border)',
                }
            },
            
            // Адаптивные сетки
            gridTemplateColumns: {
                'adaptive-mobile': 'var(--grid-columns-mobile)',
                'adaptive-tablet': 'var(--grid-columns-tablet)',
                'adaptive-desktop': 'var(--grid-columns-desktop)',
                'calendar': 'var(--calendar-time-width) repeat(var(--masters-count, 1), 1fr)',
            },
            
            // Адаптивные gaps
            gap: {
                'adaptive': 'var(--grid-gap)',
                'calendar': 'var(--calendar-gap)',
            },
            
            // Адаптивные точки останова (в rem для масштабируемости)
            screens: {
                'xs': '20rem',      // 320px
                'sm': '30rem',      // 480px  
                'md': '48rem',      // 768px
                'lg': '64rem',      // 1024px
                'xl': '80rem',      // 1280px
                '2xl': '96rem',     // 1536px
                '3xl': '120rem',    // 1920px
            },
            
            // Адаптивные максимальные ширины
            maxWidth: {
                'adaptive-container': '90rem',
                'adaptive-modal': '32rem',
                'adaptive-card': '28rem',
            },
            
            // Адаптивные переходы
            transitionDuration: {
                'adaptive': '300ms',
            },
            
            // Backdrop blur для адаптивности
            backdropBlur: {
                'adaptive-xs': '0.125rem',
                'adaptive-sm': '0.25rem',
                'adaptive-md': '0.5rem',
                'adaptive-lg': '0.75rem',
                'adaptive-xl': '1rem',
            },
            
            // Z-индексы для слоев
            zIndex: {
                'nav': '50',
                'modal': '100',
                'tooltip': '200',
                'dropdown': '300',
            },
            
            // Адаптивные анимации
            animation: {
                'fade-in': 'fadeIn 0.3s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'slide-down': 'slideDown 0.3s ease-out',
                'scale-in': 'scaleIn 0.2s ease-out',
            },
            
            // Keyframes для анимаций
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(1rem)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                slideDown: {
                    '0%': { transform: 'translateY(-1rem)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                scaleIn: {
                    '0%': { transform: 'scale(0.9)', opacity: '0' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                },
            },
            
            // Адаптивные минимальные высоты
            minHeight: {
                'touch': '2.75rem',
                'calendar-cell': 'var(--calendar-cell-height)',
            },
            
            // Адаптивные минимальные ширины
            minWidth: {
                'touch': '2.75rem',
                'button': '5rem',
            },
        },
    },

    plugins: [
        forms,
        
        // Плагин для адаптивных утилит
        function({ addUtilities, theme }) {
            const newUtilities = {
                // Адаптивные контейнеры
                '.container-adaptive': {
                    'max-width': theme('maxWidth.adaptive-container'),
                    'margin': '0 auto',
                    'padding': '0 var(--spacing-base)',
                    '@media (max-width: 48rem)': {
                        'padding': '0 var(--spacing-sm)',
                    },
                },
                
                // Адаптивные сетки
                '.grid-adaptive': {
                    'display': 'grid',
                    'gap': 'var(--grid-gap)',
                    'grid-template-columns': 'var(--grid-columns-mobile)',
                    'align-items': 'stretch',
                    '@media (min-width: 48rem)': {
                        'grid-template-columns': 'var(--grid-columns-tablet)',
                    },
                    '@media (min-width: 64rem)': {
                        'grid-template-columns': 'var(--grid-columns-desktop)',
                    },
                },
                
                // Адаптивные флексы
                '.flex-adaptive': {
                    'display': 'flex',
                    'gap': 'var(--spacing-base)',
                    'flex-wrap': 'wrap',
                    '@media (max-width: 48rem)': {
                        'flex-direction': 'column',
                        'gap': 'var(--spacing-sm)',
                    },
                },
                
                // Адаптивные кнопки
                '.btn-adaptive': {
                    'display': 'inline-flex',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'padding': 'var(--spacing-sm) var(--spacing-base)',
                    'font-size': 'var(--font-size-sm)',
                    'font-weight': '600',
                    'border-radius': 'var(--radius-base)',
                    'transition': 'all 0.3s ease',
                    'min-height': '2.75rem',
                    'min-width': '5rem',
                    '@media (max-width: 48rem)': {
                        'width': '100%',
                        'padding': 'var(--spacing-base) var(--spacing-lg)',
                        'font-size': 'var(--font-size-base)',
                    },
                },
                
                // Адаптивные карточки
                '.card-adaptive': {
                    'background-color': 'var(--color-surface)',
                    'border-radius': 'var(--radius-base)',
                    'box-shadow': 'var(--shadow-base)',
                    'padding': 'var(--spacing-lg)',
                    'transition': 'all 0.3s ease',
                    'display': 'flex',
                    'flex-direction': 'column',
                    'height': '100%',
                    '&:hover': {
                        'box-shadow': 'var(--shadow-lg)',
                        'transform': 'translateY(-0.125rem)',
                    },
                },
                
                // Адаптивные формы
                '.form-adaptive': {
                    'background-color': 'var(--color-surface)',
                    'padding': 'var(--spacing-xl)',
                    'border-radius': 'var(--radius-lg)',
                    'box-shadow': 'var(--shadow-base)',
                },
                
                // Адаптивные инпуты
                '.input-adaptive': {
                    'width': '100%',
                    'padding': 'var(--spacing-sm) var(--spacing-base)',
                    'font-size': 'var(--font-size-base)',
                    'border': '0.0625rem solid var(--color-border)',
                    'border-radius': 'var(--radius-base)',
                    'background-color': 'var(--color-surface)',
                    'color': 'var(--color-text)',
                    'transition': 'all 0.3s ease',
                    '&:focus': {
                        'outline': 'none',
                        'border-color': 'var(--color-primary)',
                        'box-shadow': '0 0 0 0.1875rem rgba(59, 130, 246, 0.1)',
                    },
                    '@media (max-width: 48rem)': {
                        'font-size': '1rem', // Предотвращает зум на iOS
                    },
                },
                
                // Адаптивные модальные окна
                '.modal-adaptive': {
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'right': '0',
                    'bottom': '0',
                    'background-color': 'rgba(0, 0, 0, 0.5)',
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'z-index': '100',
                    'padding': 'var(--spacing-base)',
                    '@media (max-width: 48rem)': {
                        'padding': 'var(--spacing-sm)',
                    },
                },
                
                // Адаптивные таблицы
                '.table-adaptive': {
                    'background-color': 'var(--color-surface)',
                    'border-radius': 'var(--radius-lg)',
                    'box-shadow': 'var(--shadow-base)',
                    'overflow': 'hidden',
                    '@media (max-width: 48rem)': {
                        'overflow-x': 'auto',
                        '-webkit-overflow-scrolling': 'touch',
                    },
                },
                
                // Скрытие/показ элементов
                '.hide-mobile': {
                    '@media (max-width: 48rem)': {
                        'display': 'none',
                    },
                },
                '.show-mobile': {
                    'display': 'none',
                    '@media (max-width: 48rem)': {
                        'display': 'block',
                    },
                },
                '.hide-tablet': {
                    '@media (min-width: 48rem) and (max-width: 64rem)': {
                        'display': 'none',
                    },
                },
                '.show-tablet': {
                    'display': 'none',
                    '@media (min-width: 48rem) and (max-width: 64rem)': {
                        'display': 'block',
                    },
                },
                '.hide-desktop': {
                    '@media (min-width: 64rem)': {
                        'display': 'none',
                    },
                },
                '.show-desktop': {
                    'display': 'none',
                    '@media (min-width: 64rem)': {
                        'display': 'block',
                    },
                },
                
                // Адаптивная типографика
                '.text-adaptive-xs': {
                    'font-size': 'var(--font-size-xs)',
                    'line-height': '1.4',
                },
                '.text-adaptive-sm': {
                    'font-size': 'var(--font-size-sm)',
                    'line-height': '1.5',
                },
                '.text-adaptive-base': {
                    'font-size': 'var(--font-size-base)',
                    'line-height': '1.6',
                },
                '.text-adaptive-lg': {
                    'font-size': 'var(--font-size-lg)',
                    'line-height': '1.6',
                },
                '.text-adaptive-xl': {
                    'font-size': 'var(--font-size-xl)',
                    'line-height': '1.5',
                },
                '.text-adaptive-2xl': {
                    'font-size': 'var(--font-size-2xl)',
                    'line-height': '1.4',
                },
                '.text-adaptive-3xl': {
                    'font-size': 'var(--font-size-3xl)',
                    'line-height': '1.3',
                },
                '.text-adaptive-4xl': {
                    'font-size': 'var(--font-size-4xl)',
                    'line-height': '1.2',
                },
            };
            
            addUtilities(newUtilities);
        },
    ],
};
