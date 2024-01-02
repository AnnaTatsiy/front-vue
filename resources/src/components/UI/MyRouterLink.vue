<script>
export default {
    name : 'my-router-link'
}

</script>

<template>
    <router-link class="link-item" to><slot></slot></router-link>
</template>

<style scoped>

.link-item {
    font-weight: 500;
    color: white;
    opacity: 50%;
    display: inline-block;
    text-decoration: none;
    margin-left: 10px;
    margin-right: 10px;
}

.link-item:hover {
    color: white;
}

.active {
    color: white;
    opacity: 100%;
}

</style>
