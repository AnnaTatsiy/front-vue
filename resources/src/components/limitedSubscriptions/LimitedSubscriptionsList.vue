<script setup>
import LimitedSubscriptionTableData from "./LimitedSubscriptionTableData.vue";
import NotFountAlert from "../UI/NotFountAlert.vue";

const props = defineProps({
    subscriptions: Array
});
</script>

<template>

    <!--
    <Col>
        <div className={"row mt-2 max-width-form-control min-width-form-control"}>
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
                <CustomerOption key={item.id} customer={item}/>
                    ))}
            </datalist>
            <label htmlFor="exampleDataList" className="font-size-alert form-label p-3 text-secondary">ФИО или серия-номер
                паспорта клиента:</label>
        </div>
        </div>

        <div className={"d-flex justify-content-end mt-3 mb-2"}>
        <Button variant={"success"} className={"btn-sm"} onClick={() => {
        setFormModalShow(true);
        }}>Оформить тренировки</Button>

        </div>

    </Col> -->

    <table class="table mt-3" v-if="subscriptions">
        <thead>
        <tr>
            <th>ФИО клиента</th>
            <th>ФИО тренера</th>
            <th>Кол-во тренировок</th>
            <th>Дата открытия</th>
        </tr>
        </thead>
        <tbody>
        <limited-subscription-table-data v-for="sub in subscriptions"  :key="sub.id" :sub="sub"/>
        </tbody>
    </table>

    <not-fount-alert v-else/>
<!--
<LimitedSubscriptionFormModal
    show={formModalShow}
    customers = {dataList}
    coaches = {coaches}
    onHide={() => setFormModalShow(false)}
/> -->
</template>

<style scoped>

</style>
