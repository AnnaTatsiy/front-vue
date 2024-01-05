<script setup>
import { useWindowSize } from '@vueuse/core'
import CustomersList from "../customers/CustomersList.vue";
import UnlimitedSubscriptionsList from "../unlimitedSubscriptions/UnlimitedSubscriptionsList.vue";
import LimitedSubscriptionsList from "../limitedSubscriptions/LimitedSubscriptionsList.vue";
import CustomersPage from "../customers/CustomersPage.vue";
import UnlimitedSubscriptionsPage from "../unlimitedSubscriptions/UnlimitedSubscriptionsPage.vue";
import LimitedSubscriptionsPage from "../limitedSubscriptions/LimitedSubscriptionsPage.vue";

const { width} = useWindowSize()
</script>

<template>
    <ul class="nav" v-bind:class="{ 'nav-tabs': width > 770, 'nav-pills': width < 770 }" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="first-tab" data-bs-toggle="tab" data-bs-target="#first-tab-pane" type="button" role="tab" aria-controls="first-tab-pane" aria-selected="true">Клиенты</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="second-tab" data-bs-toggle="tab" data-bs-target="#second-tab-pane" type="button" role="tab" aria-controls="second-tab-pane" aria-selected="false">Абонементы</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="third-tab" data-bs-toggle="tab" data-bs-target="#third-tab-pane" type="button" role="tab" aria-controls="third-tab-pane" aria-selected="false">Тренировки с тренерами</button>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="first-tab-pane" role="tabpanel" aria-labelledby="first-tab" tabindex="0"><customers-page/></div>
        <div class="tab-pane fade" id="second-tab-pane" role="tabpanel" aria-labelledby="second-tab" tabindex="0"><unlimited-subscriptions-page/></div>
        <div class="tab-pane fade" id="third-tab-pane" role="tabpanel" aria-labelledby="third-tab" tabindex="0"><limited-subscriptions-page/></div>
    </div>
</template>

<style scoped>

</style>
