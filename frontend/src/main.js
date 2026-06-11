import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
// import './echo'

import App from './App.vue'
import router from './router'
import { initEcho } from './echo'

if(localStorage.getItem('token')){
    initEcho()
}

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
