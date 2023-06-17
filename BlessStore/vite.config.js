import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',
    // hmr: {
    //   clientPort: 7465
    // },
    port: 3001, //开放端口
  }
})
