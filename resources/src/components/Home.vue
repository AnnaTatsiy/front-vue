<script setup>
import {onMounted} from "vue";
import {useAuthStore} from "../store/auth.js";

const authStore = useAuthStore()

onMounted(async () => {
    await authStore.getUser()
})

</script>

<template>
<div v-if="authStore.user">
    <p>{{authStore.user.name}}</p>
</div>
</template>

<style scoped>

</style>
