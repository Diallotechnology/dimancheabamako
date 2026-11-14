import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            // app.ts : back-office Inertia ; shop.* : vitrine Livewire (layout `shop`).
            input: ['resources/js/app.ts', 'resources/css/shop.css', 'resources/js/shop.ts'],
            ssr: 'resources/js/ssr.js',
            refresh: true,
            fonts: [
                bunny('Hanken Grotesk', { weights: [400, 500, 600, 700] }),
                bunny('League Spartan', { weights: [500, 600, 700] }),
                // Texte courant de la vitrine (repris du thème d'origine).
                bunny('Lato', { weights: [400, 700] }),
            ],
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder(),
    ],
});
