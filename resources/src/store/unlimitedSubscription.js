import {defineStore} from "pinia";
import api from "../api/axios.js";

export const useUnlimitedSubscriptionStore = defineStore('unlimitedSubscription', {
    state: () => ({
        subscriptions: [],
        totalPage: 1
    }),

    getters: {

    },

    actions:{
        async getSubscriptions(number){
            const data = await api.get('/api/unlimited-subscriptions/all', {params: {page: number}})
            this.subscriptions = data.data.data

            this.totalPage = data.data.last_page
        }
    }
})
