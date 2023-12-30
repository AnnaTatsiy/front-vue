<script setup>
import {ref} from 'vue'
import {useAuthStore} from "../store/auth.js";

const authStore = useAuthStore()

const form = ref({
    email: '',
    password: ''
})
</script>

<template>
    <div>
        <div class="row-sm p-3 container-fluid-login">
            <main class="form-signing m-auto">
                <form @submit.prevent="authStore.login(form)">
                    <h1 class="h3 mb-3 fw-normal">Пожалуйста войдите</h1>

                    <!--       {errors &&
                           <Alert variant={"danger"} className={"font-size-alert"}>Некорректный логин или пароль</Alert>
                           } -->

                    <div class="form-floating">
                        <input id="emailInput"
                               type="email"
                               class="form-control"
                               placeholder="name@example.com"
                               required="required"
                               v-model="form.email"/>

                        <label for="emailInput">Электронная почта</label>
                    </div>

                    <div class="form-floating mt-2">
                        <input
                            type="password"
                            class="form-control"
                            placeholder="password"
                            required="required"
                            v-model="form.password"/>
                        <label for="floatingPassword">Пароль</label>
                    </div>

                    <button class="btn btn-primary w-100 py-2 mt-3" type="submit">Войти</button>
                </form>
            </main>
        </div>
    </div>
</template>

<style scoped>

</style>
