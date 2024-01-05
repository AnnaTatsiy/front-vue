import {defineStore} from "pinia";
import api from "../api/axios.js";

export const useSignUpPersonalWorkoutStore = defineStore('signUpPersonalWorkout', {
    state: () => ({
        signs: [],
        totalPage: 1
    }),

    getters: {

    },

    actions:{
        async getSign(number){
            const data = await api.get('/api/sign-up-personal-workouts/all', {params: {page: number}})
            this.signs = data.data.data

            this.totalPage = data.data.last_page
        }
    }
})
