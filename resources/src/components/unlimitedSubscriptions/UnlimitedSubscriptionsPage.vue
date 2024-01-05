<script setup>

import {useUnlimitedSubscriptionStore} from "../../store/unlimitedSubscription.js";
import {storeToRefs} from "pinia";
import {onMounted, ref, watch} from "vue";
import MyPaginate from "../MyPaginate.vue";
import UnlimitedSubscriptionsList from "./UnlimitedSubscriptionsList.vue";

const subscriptionsStore = useUnlimitedSubscriptionStore()

const {subscriptions, totalPage} =  storeToRefs(subscriptionsStore)

let currPage = ref(1)

onMounted(() => {
    subscriptionsStore.getSubscriptions(1)
})

watch(currPage, () => {
    subscriptionsStore.getSubscriptions(currPage.value)
})

const changePage = (page) => {
    currPage.value = page
}

</script>

<template>
    <unlimited-subscriptions-list :subscriptions="subscriptions"/>
    <my-paginate :total-page="totalPage" :curr-page="currPage" @change="changePage"/>
</template>

<style scoped>

</style>
