<script setup>
import SignUpPersonalWorkoutTableData from "./SignUpPersonalWorkoutTableData.vue";
import NotFountAlert from "../UI/NotFountAlert.vue";

const props = defineProps({
    signs: Array
});
</script>

<template>

    <!--
    <div className={"d-flex justify-content-end mt-3 mb-2"}>
    <Button variant={"secondary"} className={"me-2 btn-sm"} onClick={() => dispatch(getSignUpPersonalWorkouts(page))}>Сброс</Button>
    <Button variant={"primary"} className={"btn-sm"} onClick={() => setFilterModalShow(true)}>Открыть фильтр</Button>
    </div> -->

    <table class="table" v-if="signs">
        <thead>
        <tr>
            <th>Дата</th>
            <th>Время</th>
            <th>Серия-номер тренера</th>
            <th>Тренер</th>
            <th>Серия-номер клиента</th>
            <th>Клиент</th>
        </tr>
        </thead>
        <tbody>
        <sign-up-personal-workout-table-data v-for="sign in signs"  :key="sign.id" :workout="sign"/>
        </tbody>
    </table>

    <not-fount-alert v-else/>

    <!--
<SignUpPersonalWorkoutsFilter
    show={filterModalShow}
    dataListCustomers = {dataListCustomers}
    dataListCoaches = {dataListCoaches}
    onHide={() => setFilterModalShow(false)}
/> -->
</template>

<style scoped>

</style>
