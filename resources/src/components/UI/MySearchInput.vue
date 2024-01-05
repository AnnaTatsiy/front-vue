<script setup>
const props = defineProps({
    searchValue: String,
    datalist: Array,

});

</script>

<template>
    <div class="col max-width-form-control min-width-form-control">
        <div class="form-floating">
            <input
                class="form-control"
                type="text"
                :value="searchValue"
                @change="$emit('update:searchValue', $event.target.value)"
                list="datalistOptions" id="exampleDataList"
                placeholder="Type to search..."/>
            <datalist id="datalistOptions">
                <option v-for="item in datalist" :value="item.passport">{{item.surname}} {{item.name}} {{item.patronymic}}</option>
            </datalist>
            <label for="exampleDataList" class="font-size-alert form-label p-3 text-secondary">ФИО или
                серия-номер
                паспорта:</label>
        </div>
    </div>
</template>

<style scoped>
.max-width-form-control{
    max-width: 450px;
}

.min-width-form-control{
    min-width: 300px;
}
</style>
