import {defineStore} from "pinia";
import api from "../api/axios.js";

export const useGroupWorkoutStore = defineStore('groupWorkout', {
    state: () => ({
        workouts: [],
        totalPage: 1
    }),

    getters: {

    },

    actions:{
        async getWorkouts(number){
            const data = await api.get('/api/group-workouts/all', {params: {page: number}})
            this.workouts = data.data.data

            this.totalPage = data.data.last_page
        }
    }
})
