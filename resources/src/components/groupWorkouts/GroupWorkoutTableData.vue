<script setup>
const props = defineProps({
    workout: Object
});
</script>

<template>
    <tr>
        <td data-label='День'>{{workout.schedule.day.title}}</td>
        <td data-label='Время'>{{workout.schedule.time_begin.slice(0, 5)}}</td>
        <td data-label='Тренер'>{{workout.schedule.coach.name.slice(0, 1)}}. {{workout.schedule.coach.patronymic.slice(0, 1)}}. {{workout.schedule.coach.surname}}</td>
        <td data-label='Тип'>{{workout.schedule.workout_type.title}}</td>
        <td data-label='Зал'>{{workout.schedule.gym.title}}</td>
        <!--     <td data-label={'Отмена'}><FormCheck onClick={() => onClick(workout.id)} checked={workout.cancelled === 1} disabled={workout.cancelled === 1}/></td> -->
        <td data-label='Дата проведения'>{{new Date(workout.event).toLocaleDateString()}}</td>
           <!--       <td data-label={'Узнать детали...'} className={(size.width) > 1200 ? "text-md-center mb-3" : 'mb-3'}>
              <NavLink className={"fs-3"} to={`/group-workouts/selected-by-id?id=${workout.id}`}
                         placeholder={"Показать детали..."}>
                <InfoSquareFill/>
                </NavLink>
                </td> -->
         </tr>
     </template>

