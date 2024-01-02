import './bootstrap';
import {createApp, markRaw} from 'vue'
import { createPinia } from 'pinia'
import App from '../src/App.vue'
import router from "../src/router/router.js";
import components from '../src/components/UI'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap/dist/js/bootstrap.js'

const store = createPinia()

store.use(({store}) => {
    store.router = markRaw(router)
})

const app = createApp(App);

components.forEach(component=>{
    app.component(component.name, component)
})

app.use(store).use(router).mount("#app")


