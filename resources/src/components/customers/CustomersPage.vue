<script setup>

import {useCustomerStore} from "../../store/customer.js";
import {storeToRefs} from "pinia";
import {onMounted, ref, watch} from "vue";
import MyPaginate from "../MyPaginate.vue";
import CustomersList from "./CustomersList.vue";

const customerStore = useCustomerStore()

const {customers, totalPage, datalist,} = storeToRefs(customerStore)

let currPage = ref(1)

onMounted(() => {
    customerStore.getCustomers(1)
    customerStore.getDataList()
})

watch(currPage, () => {
    customerStore.getCustomers(currPage.value)
})

const changePage = (page) => {
    currPage.value = page
}

</script>

<template>
    <customers-list :customers="customers"/>
    <my-paginate :total-page="totalPage" :curr-page="currPage" @change="changePage"/>
</template>

<style scoped>

</style>
