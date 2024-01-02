<script setup>
import {useAuthStore} from "../../store/auth.js";
import MyRouterLink from "../UI/MyRouterLink.vue";

const authStore = useAuthStore()
</script>

<template>
    <div>
        <!--Потеряли адаптивность -->
            <nav class="navbar navbar-expand-lg bg-dark navbar-dark bg-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">SportClub</a>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <my-router-link to="/login">Расписание</my-router-link>
                            </li>
                            <li class="nav-item">
                                <my-router-link to="/customers">Клиенты</my-router-link>
                            </li>
                            <li class="nav-item">
                                <my-router-link to="/coaches">Тренеры</my-router-link>
                            </li>
                            <li class="nav-item">
                                 <my-router-link to="/workouts">Тренировки</my-router-link>
                            </li>

                        </ul>
                        <div class="d-flex justify-content-end">
                            <div class="d-lg-flex col-lg-3 justify-content-lg-end">
                                <button @click="authStore.logout" class="btn btn-primary">Выход</button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
    </div>
</template>

<style scoped>

</style>
