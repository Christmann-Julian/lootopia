import { defineConfig } from 'vite'
import { reactRouter } from '@react-router/dev/vite';

// https://vite.dev/config/
export default defineConfig({
  plugins: [reactRouter()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    watch: {
      usePolling: true,
    },
  },
})
