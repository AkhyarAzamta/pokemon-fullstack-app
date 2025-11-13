import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Toast, { type PluginOptions } from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import './assets/main.css' // Import file CSS

const app = createApp(App)

const toastOptions: PluginOptions = {
  transition: 'Vue-Toastification__bounce',
  maxToasts: 20,
  newestOnTop: true,
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false
}

app.use(router)
app.use(Toast, toastOptions)

app.mount('#app')