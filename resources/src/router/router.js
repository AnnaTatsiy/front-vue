import {createRouter, createWebHistory} from "vue-router";
import Home from  '../components/Home.vue'
import Login from  '../components/Login.vue'

const router = createRouter({
    routes: [
        {
            path: '/',
            component: Home
        },
        {
            path: '/login',
            component: Login
        }
    ],
    history: createWebHistory(),
    linkActiveClass: 'active'
})

export default router;
