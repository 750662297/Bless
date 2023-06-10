import { createApp } from 'vue'

import Antd from 'ant-design-vue';
import App from './App.vue'
import 'ant-design-vue/dist/antd.css';
import { createPinia } from 'pinia';

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.use(Antd).mount('#app')
