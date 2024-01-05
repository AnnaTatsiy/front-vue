<script setup>
import {storeToRefs} from "pinia";
import {onMounted, ref, watch} from "vue";
import {useSignUpPersonalWorkoutStore} from "../../store/signUpPersonalWorkout.js";
import MyPaginate from "../MyPaginate.vue";
import SignUpPersonalWorkoutsList from "./SignUpPersonalWorkoutsList.vue";

const signStore = useSignUpPersonalWorkoutStore()

const {signs, totalPage} =  storeToRefs(signStore)

let currPage = ref(1)

onMounted(() => {
    signStore.getSign(1)
})

watch(currPage, () => {
    signStore.getSign(currPage.value)
})

const changePage = (page) => {
    currPage.value = page
}
</script>

<template>
    <sign-up-personal-workouts-list :signs="signs"/>
    <my-paginate :total-page="totalPage" :curr-page="currPage" @change="changePage"/>
</template>

<style scoped>

</style>
