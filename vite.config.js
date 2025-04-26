import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
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
