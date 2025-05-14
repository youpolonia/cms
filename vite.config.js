import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5174,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
    },
    define: {
        'import.meta.env.VITE_SENTRY_DSN': JSON.stringify(process.env.SENTRY_DSN),
        'import.meta.env.VITE_SENTRY_ENVIRONMENT': JSON.stringify(process.env.SENTRY_ENVIRONMENT)
    },
    plugins: [
        vue(),
        react({
            jsxRuntime: 'classic',
            babel: {
                plugins: [
                    ['@babel/plugin-transform-react-jsx', { runtime: 'classic' }]
                ]
            }
        }),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/themes/default/assets/css/app.css',
                'resources/themes/default/assets/js/app.js',
                'node_modules/prismjs/themes/prism.css',
                'node_modules/prismjs/components/prism-core.min.js',
                'node_modules/prismjs/components/prism-markup.min.js',
                'node_modules/prismjs/components/prism-clike.min.js',
                'node_modules/prismjs/components/prism-javascript.min.js',
                'node_modules/prismjs/components/prism-php.min.js',
                'node_modules/prismjs/components/prism-css.min.js',
                'node_modules/prismjs/plugins/line-numbers/prism-line-numbers.css',
                'node_modules/prismjs/plugins/line-numbers/prism-line-numbers.min.js'
            ],
            refresh: true,
        }),
    ],
});
