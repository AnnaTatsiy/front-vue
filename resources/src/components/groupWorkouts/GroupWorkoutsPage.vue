<script setup>
import {storeToRefs} from "pinia";
import {onMounted, ref, watch} from "vue";
import {useGroupWorkoutStore} from "../../store/groupWorkout.js";
import MyPaginate from "../MyPaginate.vue";
import GroupWorkoutsList from "./GroupWorkoutsList.vue";

const workoutsStore = useGroupWorkoutStore()

const {workouts, totalPage} =  storeToRefs(workoutsStore)

let currPage = ref(1)

onMounted(() => {
    workoutsStore.getWorkouts(1)
})

watch(currPage, () => {
    workoutsStore.getWorkouts(currPage.value)
})

const changePage = (page) => {
    currPage.value = page
}
</script>

<template>
    <group-workouts-list :workouts="workouts"/>
    <my-paginate :total-page="totalPage" :curr-page="currPage" @change="changePage"/>
</template>

<style scoped>

</style>
