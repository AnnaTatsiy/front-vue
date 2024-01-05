<script setup>
const props = defineProps({
    price: Object
});
</script>

<template>
    <tr>
        <td data-label='ФИО тренера'>{{price.coach.surname}} {{price.coach.name}} {{price.coach.patronymic}}</td>
        <td data-label='Кол-во тренировок'>{{price.amount_workout}}</td>
        <td class='mb-3' data-label='Стоимость'>{{price.price}}</td>
    </tr>
</template>
