import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// https://vitejs.dev/config/
export default defineConfig({
    server: {
        proxy: {
            '/api': {
                target: process.env.VITE_URL_TO_BASKET_LIST,
                changeOrigin: true,
                secure: false,
                rewrite: (path) => path//.replace(/^\/api/, ''),
            },
            '/save': {
                target: process.env.VITE_DEMO_LINK,
                changeOrigin: true,
                secure: false,
                rewrite: (path) => path, //.replace(/^\/save/, ""),
            },
        },
    },
    plugins: [vue()],
    build: {
        sourcemap: 'inline',
        outDir: '../public/order/assets',
        rollupOptions: {
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
    },
});
