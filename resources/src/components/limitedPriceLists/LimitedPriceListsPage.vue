<script setup>

import MyPaginate from "../MyPaginate.vue";
import {onMounted, ref, watch} from "vue";
import {storeToRefs} from "pinia";
import {useLimitedPriceListStore} from "../../store/limitedPriceList.js";
import LimitedPriceLists from "./LimitedPriceLists.vue";

const pricesStore = useLimitedPriceListStore()

const {prices, totalPage} =  storeToRefs(pricesStore)

let currPage = ref(1)

onMounted(() => {
    pricesStore.getPrices(1)
})

watch(currPage, () => {
    pricesStore.getPrices(currPage.value)
})

const changePage = (page) => {
    currPage.value = page
}
</script>

<template>
    <limited-price-lists :prices="prices"/>
    <my-paginate :total-page="totalPage" :curr-page="currPage" @change="changePage"/>
</template>

<style scoped>

</style>
