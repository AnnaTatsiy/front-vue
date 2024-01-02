<template>
    <div v-if="authStore.user">
        <admin-layout/>
    </div>
    <div class="container mt-2">
        <div class="my-3 p-3 bg-body rounded shadow-sm min-height-container">
            <router-view></router-view>
        </div>
    </div>
        <my-footer v-if="authStore.user"/>
</template>

<script setup>
import AdminLayout from "./components/layouts/AdminLayout.vue";
import MyFooter from "./components/MyFooter.vue";
import {useRouter} from "vue-router";
import {onMounted} from "vue";
import {useAuthStore} from "./store/auth.js";

const authStore = useAuthStore()
const router = useRouter()

onMounted(async () => {
    try {
        await authStore.getUser()
    } catch {
        await router.push('/login');
    }
})

</script>

<style>

</style>
