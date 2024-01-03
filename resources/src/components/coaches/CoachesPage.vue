<script setup>
import CoachesList from "./CoachesList.vue";
import MyPaginate from "../MyPaginate.vue";

import {useCoachStore} from "../../store/coach.js";
import {storeToRefs} from "pinia";
import {onMounted, ref, watch} from "vue";

const coachStore = useCoachStore()

const {coaches, totalPage, datalist, coachByPassport} = storeToRefs(coachStore)

let currPage = ref(1)
let searchValue = ref('')

let arr = ref([])

onMounted(() => {
    coachStore.getCoaches(1)
    coachStore.getDataList()
})

watch(currPage, () => {
    coachStore.getCoaches(currPage.value)
})

watch(searchValue, () => {
    arr = (searchValue.value.length !== 0) ? coachStore.coachByPassport : coaches
    console.log(searchValue.value)
})

const changePage = (page) => {
    currPage.value = page
}

</script>

<template>

    <div class="col">
        <div class="row mt-2">
            <div class="col max-width-form-control min-width-form-control">
                <div class="form-floating">
                    <input
                        class="form-control"
                        type="text"
                        v-model="searchValue"
                        list="datalistOptions" id="exampleDataList"
                        placeholder="Type to search..."
                        name="passport"/>
                    <!--       onChange={(e) => {
                       setSearchValue(e.target.value);
                       }} -->

                    <datalist id="datalistOptions">
                        <option v-for="coach in datalist">{{coach.surname}} {{coach.name}} {{coach.patronymic}}</option>
                    </datalist>
                    <label for="exampleDataList" class="font-size-alert form-label p-3 text-secondary">ФИО или
                        серия-номер
                        паспорта:</label>
                </div>
            </div>
            <div class="col mt-2 ms-3">

                <div class="row">

                    <!--
                    <div className="form-check">
                        <input checked={free} onClick={() => setFree(!free)} className="form-check-input"
                        type="checkbox" role="switch" id="flexSwitchCheckDefault"></input>
                        <label className="form-check-label text-dark" htmlFor="flexSwitchCheckDefault">Тренеры с
                            разрешенной продажей абонементов</label>
                    </div>

                    <div className="form-check">
                        <input checked={!isHidden} onClick={() => setHidden(!isHidden)}
                        className="form-check-input"
                        type="checkbox" role="switch" id="flexSwitchCheck"></input>
                        <label className="form-check-label text-dark" htmlFor="flexSwitchCheck">Колонка
                            увольнения тренеров</label>
                    </div> -->
                </div>
            </div>
        </div>
        </div>

        <div class="d-flex justify-content-end mt-3 mb-3">
            <!--    <button class="btn-sm success" onClick={()
               => {
               setFormModalShow(true);
               setIsAddForm(true);
               coach.current = null;
               }}>Добавить тренера</button>
           </div> -->
       </div>

       <coaches-list :coaches="arr.value"/>
       <my-paginate :total-page="totalPage" :curr-page="currPage" @change="changePage"/>
   </template>

   <style scoped>

   </style>
