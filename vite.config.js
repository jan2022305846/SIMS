import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Suppress deprecation warnings from Bootstrap
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions']
            }
        }
    },
    build: {
        // Suppress warnings during build
        terserOptions: {
            compress: {
                warnings: false
            }
        }
    }
});
