<script setup>
const props = defineProps({
    customer: Object
});
</script>

<template>
    <tr>
        <td data-label='Паспорт'>{{customer.passport}}</td>
        <td data-label='Клиент'>{{customer.name.slice(0, 1)}}. {{customer.patronymic.slice(0, 1)}}. {{customer.surname}}</td>
        <td data-label='Телефон'>{{customer.number}}</td>
        <td data-label='Эл. почта'>{{customer.user.email}}</td>
        <td data-label='Место проживания'>{{customer.registration}}</td>
       <!-- <td data-label={''}>
            <Button variant={"primary"} className={"mb-3 btn-sm"} onClick={() => onClick(customer.id)}>
            <PencilFill/></Button>
        </td> -->
    </tr>
</template>

<style scoped>

</style>
