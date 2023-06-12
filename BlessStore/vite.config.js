import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const webPort = 3001; //网页开放端口

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',
    // hmr: {
    //   clientPort: 7465
    // },
    port: webPort //开放端口
  }
})
