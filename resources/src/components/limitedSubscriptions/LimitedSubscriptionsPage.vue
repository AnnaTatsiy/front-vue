<script setup>
import {storeToRefs} from "pinia";
import {onMounted, ref, watch} from "vue";
import MyPaginate from "../MyPaginate.vue";
import LimitedSubscriptionsList from "./LimitedSubscriptionsList.vue";
import {useLimitedSubscriptionStore} from "../../store/limitedSubscription.js";

const subscriptionsStore = useLimitedSubscriptionStore()

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
    <limited-subscriptions-list :subscriptions="subscriptions"/>
    <my-paginate :total-page="totalPage" :curr-page="currPage" @change="changePage"/>
</template>

<style scoped>

</style>
