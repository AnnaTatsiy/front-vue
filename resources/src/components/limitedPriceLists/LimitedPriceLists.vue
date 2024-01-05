<script setup>

import NotFountAlert from "../UI/NotFountAlert.vue";
import LimitedPriceListTableData from "./LimitedPriceListTableData.vue";

const props = defineProps({
    prices: Array
});
</script>

<template>

    <!--
    <div className={"row mt-3 mb-3 max-width-form-control"}>
    <div className="form-floating">
        <input
            className="form-control"
            type={"text"} value={searchValue}
            list="datalistOptions" id="exampleDataList"
            placeholder="Type to search..."
            onChange={(e) => {
        setSearchValue(e.target.value);
        }}
        name={"passport"}/>
        <datalist id="datalistOptions">
            {dataList.map((item) => (
            <CoachOption key={item.id} coach={item}/>
                ))}
        </datalist>
        <label htmlFor="exampleDataList" className="font-size-alert form-label p-3 text-secondary">ФИО или серия-номер
            паспорта:</label>
    </div>
    </div> -->

    <table class="table" v-if="prices">
        <thead>
        <tr>
            <th>ФИО тренера</th>
            <th>Кол-во тренировок</th>
            <th>Стоимость</th>
        </tr>
        </thead>
        <tbody>
        <limited-price-list-table-data v-for="price in prices" :key="price.id" :price="price"/>
        </tbody>
    </table>

    <not-fount-alert v-else/>
</template>

<style scoped>

</style>
