import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import ui from '@nuxt/ui/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        ui({
            router: 'inertia',
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '@/Components': '/resources/js/Components',
            '@/Layouts': '/resources/js/Layouts',
            '@/Pages': '/resources/js/Pages',
            '@/composables': '/resources/js/composables',
            '@/utils': '/resources/js/utils'
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
