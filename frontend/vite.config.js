// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/api': {
        target: 'http://backend:7001',
        changeOrigin: true,
        rewrite: path => path.replace(/^\/api/, '')
      }
    },
    port: 7000,
    host: true
  },
})
