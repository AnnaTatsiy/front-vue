import {defineStore} from "pinia";
import api from "../api/axios.js";

export const useLimitedPriceListStore = defineStore('priceList', {
    state: () => ({
        prices: [],
        totalPage: 1
    }),

    getters: {

    },

    actions:{
        async getPrices(number){
            const data = await api.get('/api/limited-price-lists/all', {params: {page: number}})
            this.prices = data.data.data

            this.totalPage = data.data.last_page
        }
    }
})
