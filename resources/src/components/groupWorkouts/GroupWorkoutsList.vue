<script setup>
import NotFountAlert from "../UI/NotFountAlert.vue";
import GroupWorkoutTableData from "./GroupWorkoutTableData.vue";

const props = defineProps({
    workouts: Array
});
</script>

<template>
    <!--
    <div className={"d-flex justify-content-end mt-3 mb-2"}>
    <Button variant={"secondary"} className={"me-2 btn-sm"} onClick={() => dispatch(getGroupWorkouts(page))}>Сброс</Button>
    <Button variant={"primary"} className={"btn-sm"}  onClick={() => setFilterModalShow(true)}>Открыть фильтр</Button>
    </div> -->

    <table class="table" v-if="workouts">
        <thead>
        <tr>
            <th>День</th>
            <th>Время</th>
            <th>Тренер</th>
            <th>Тип</th>
            <th>Зал</th>
            <th>Отмена</th>
            <th>Дата проведения</th>
            <th class="text-primary">Узнать детали...</th>
        </tr>
        </thead>
        <tbody>
        <!-- onClick={onClickForFormEdit} -->
        <group-workout-table-data v-for="workout in workouts"  :key="workout.id" :workout="workout"/>
        </tbody>
    </table>

    <not-fount-alert v-else/>

    <!--
<GroupWorkoutModalForm
    show={formModalShow}
    workout={workout.current}
    onHide={() => setFormModalShow(false)}
/>

<GroupWorkoutsFilter
    show={filterModalShow}
    dataListCustomers = {dataListCustomers}
    dataListCoaches = {dataListCoaches}
    onHide={() => setFilterModalShow(false)}
/> -->
</template>

<style scoped>

</style>
