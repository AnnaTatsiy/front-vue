<script setup>
import {useWindowSize} from '@vueuse/core'
import CoachesList from "../coaches/CoachesList.vue";
import LimitedPriceLists from "../limitedPriceLists/LimitedPriceLists.vue";
import CoachesPage from "../coaches/CoachesPage.vue";
import LimitedPriceListsPage from "../limitedPriceLists/LimitedPriceListsPage.vue";

const {width} = useWindowSize()

</script>

<template>
        <ul class="nav" v-bind:class="{ 'nav-tabs': width > 770, 'nav-pills': width < 770 }" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="first-tab" data-bs-toggle="tab" data-bs-target="#first-tab-pane" type="button" role="tab" aria-controls="first-tab-pane" aria-selected="true">Тренеры</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="second-tab" data-bs-toggle="tab" data-bs-target="#second-tab-pane" type="button" role="tab" aria-controls="second-tab-pane" aria-selected="false">Прайс на тренировки</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="first-tab-pane" role="tabpanel" aria-labelledby="first-tab" tabindex="0"><coaches-page/></div>
            <div class="tab-pane fade" id="second-tab-pane" role="tabpanel" aria-labelledby="second-tab" tabindex="0"><limited-price-lists-page/></div>
        </div>
</template>

<style scoped>

</style>
