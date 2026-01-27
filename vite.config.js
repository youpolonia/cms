import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  publicDir: false,
  server: {
    host: '0.0.0.0',
    port: 5173
  },
  build: {
    outDir: 'assets/css/tb4/dist',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        entryFileNames: 'tb4.bundle.js',
        chunkFileNames: 'tb4.[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'tb4.bundle.css';
          }
          return 'assets/[name].[ext]';
        }
      }
    }
  }
})
