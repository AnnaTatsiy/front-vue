import {defineStore} from "pinia";
import api from "../api/axios.js";

export const useCoachStore = defineStore('coach', {
    state: () => ({
        coaches: [],
        datalist:[],
        totalPage: 1
    }),

    getters: {
        coachByPassport() {
            return (searchValue) => this.datalist.filter(coach => coach.passport.includes(searchValue))
        }
    },

    actions:{
        async getCoaches(number){
            const data = await api.get('/api/coaches/all', {params: {page: number}})
            this.coaches = data.data.data

            this.totalPage = data.data.last_page
        },

        async getDataList(){
            const data = await api.get('/api/coaches/get-all')
            this.datalist = data.data
        },
    }
})
