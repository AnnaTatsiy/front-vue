<script setup>
import CustomerTableData from "./CustomerTableData.vue";
import NotFountAlert from "../UI/NotFountAlert.vue";

const props = defineProps({
    customers: Array
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
            <label htmlFor="exampleDataList" className="font-size-alert form-label p-3 text-secondary">ФИО
                или серия-номер
                паспорта:</label>
        </div>
        </div>

        <div className={"d-flex justify-content-end mt-3 mb-2"}>
        <Button variant={"success"} className={"btn-sm"} onClick={() => {
        setFormModalShow(true);
        setIsAddForm(true);
        customer.current = null;
        }}>Добавить клиента</Button>

        </div>

    </Col> -->

    <table class="table" v-if="customers">
        <thead>
        <tr>
            <th>Паспорт</th>
            <th>Клиент</th>
            <th>Телефон</th>
            <th>Эл. почта</th>
            <th>Место проживания</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <customer-table-data v-for="customer in customers" :customer="customer" :key="customer.id"/>
        </tbody>
    </table>

    <not-fount-alert v-else/>

    <!--
<CustomerFormModal
    show={formModalShow}
    add={isAddForm ? 'true' : null}
customer={customer.current}
onHide={() => setFormModalShow(false)}
/> -->
</template>

<style scoped>

</style>
