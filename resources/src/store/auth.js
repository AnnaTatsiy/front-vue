import {defineStore} from "pinia";
import api from '../api/axios.js'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        authUser: null
    }),

    getters: {
        user: (state) => state.authUser
    },

    actions:{
        async getUser(){
            const data = await api.get('/user')
            this.authUser = data.data
        },

        async login(data){
            await api.post('/login', {
                email: data.email,
                password: data.password
            });

            await this.router.push('/')
        }
    }
})
