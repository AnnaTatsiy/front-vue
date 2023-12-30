import './bootstrap';
import {createApp, markRaw} from 'vue'
import { createPinia } from 'pinia'
import App from '../src/App.vue'
import router from "../src/router/router.js";
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap/dist/js/bootstrap.js'

const pinia = createPinia()
pinia.use(({store}) => {
    store.router = markRaw(router)
})

createApp(App).use(pinia).use(router).mount("#app")


