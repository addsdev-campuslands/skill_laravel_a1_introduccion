import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
      host: '0.0.0.0',
      cors: true, // ✅ activa CORS
      headers: {
        'Access-Control-Allow-Origin': '*', // o especifica tu origen si prefieres
      },
    },
    plugins: [
      laravel({
        input: [
          'resources/css/app.css',
          'resources/js/app.jsx',
        ],
        refresh: true,
      }),
      react(),
    ],
  });
  