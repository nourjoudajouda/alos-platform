import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

const input = [
  'resources/css/app.css',
  'resources/assets/css/demo.css',
  'resources/js/app.js',
  'resources/assets/vendor/fonts/iconify/iconify.css',
  'resources/assets/vendor/libs/node-waves/node-waves.scss',
  'resources/assets/vendor/libs/pickr/pickr-themes.scss',
  'resources/assets/vendor/scss/core.scss',
  'resources/assets/vendor/scss/pages/front-page.scss',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
  'resources/assets/vendor/libs/jquery/jquery.js',
  'resources/assets/vendor/libs/popper/popper.js',
  'resources/assets/vendor/js/bootstrap.js',
  'resources/assets/vendor/js/helpers.js',
  'resources/assets/vendor/js/template-customizer.js',
  'resources/assets/js/config.js',
  'resources/assets/vendor/libs/node-waves/node-waves.js',
  'resources/assets/vendor/libs/pickr/pickr.js',
  'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  'resources/assets/vendor/libs/hammer/hammer.js',
  'resources/assets/vendor/js/menu.js',
  'resources/assets/js/main.js',
];

export default defineConfig({
  plugins: [laravel({ input, refresh: true })],
  resolve: { alias: { '@': path.resolve(__dirname, 'resources') } },
  json: { stringify: true },
  build: { commonjsOptions: { include: [/node_modules/] } },
  css: {
    preprocessorOptions: {
      scss: {
        loadPaths: [path.resolve(__dirname, 'node_modules')],
        api: 'modern-compiler',
      },
    },
  },
});
