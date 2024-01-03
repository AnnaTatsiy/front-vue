import {defineStore} from "pinia";
import api from "../api/axios.js";

export const useCustomerStore = defineStore('customer', {
    state: () => ({
        customers: [],
        datalist:[],
        totalPage: 1
    }),

    getters: {

    },

    actions:{
        async getCustomers(number){
            const data = await api.get('/api/customers/all', {params: {page: number}})
            this.customers = data.data.data

            this.totalPage = data.data.last_page
        },

        async getDataList(){
            const data = await api.get('/api/customers/get-all')
            this.datalist = data.data
        },
    }
})
