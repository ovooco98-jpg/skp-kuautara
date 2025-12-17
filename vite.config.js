import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        // Optimasi untuk production
        cssCodeSplit: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs'],
                },
            },
        },
        // Minify untuk production
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Hapus console.log di production
            },
        },
        // Chunk size warning limit
        chunkSizeWarningLimit: 1000,
    },
});
