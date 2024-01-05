<script setup>
import CoachTableData from "./CoachTableData.vue";
import NotFountAlert from "../UI/NotFountAlert.vue";

const props = defineProps({
        coaches: Array
});

</script>

<template>
    <div>

        <table class="table" v-if="coaches">
            <thead>
            <tr>
                <th>Паспорт</th>
                <th>Тренер</th>
                <th>Телефон</th>
                <th>Эл. почта</th>
                <th>Прописка</th>
                <!--       {!isHidden && <th className={"text-danger"}>Уволить</th>} -->
                <th>Редактировать</th>
                <th>Тренировки</th>
            </tr>
            </thead>
            <tbody>
                <coach-table-data v-for="coach in coaches" :coach="coach" :key="coach.id"/>
            <!--    {viewCoaches.map((item) => (
                <CoachTableData key={item.id} coach={item} onClick={onClickForFormEdit} isHidden={isHidden}/>
                    ))} -->
            </tbody>
        </table>

        <not-fount-alert v-else/>

        <!--
    <CoachFormModal
        show={formModalShow}
        add={isAddForm ? 'true' : null}
    coach={coach.current}
    onHide={() => setFormModalShow(false)}
    /> -->
        </div>
</template>

<style scoped>

</style>
