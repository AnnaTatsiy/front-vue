import {createRouter, createWebHistory} from "vue-router";
import Home from  '../components/Home.vue'
import Login from  '../components/Login.vue'
import ForCustomers from "../components/controlledTabs/ForCustomers.vue";
import ForCoaches from "../components/controlledTabs/ForCoaches.vue";
import ForWorkouts from "../components/controlledTabs/ForWorkouts.vue";

const router = createRouter({
    routes: [
        {
            path: '/',
            name: 'Home',
            component: Home
        },
        {
            path: '/login',
            name: 'Login',
            component: Login
        },
        {
            path: '/customers',
            name: 'Customers',
            component: ForCustomers
        },
        {
            path: '/coaches',
            name: 'Coaches',
            component: ForCoaches
        },
        {
            path: '/workouts',
            name: 'Workouts',
            component: ForWorkouts
        }
    ],
    history: createWebHistory(),
    linkActiveClass: 'active'
})

export default router;
