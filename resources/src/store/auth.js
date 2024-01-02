import {defineStore} from "pinia";
import api from '../api/axios.js'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        authUser: null,
        authErrors: null
    }),

    getters: {
        user: (state) => state.authUser,
        errors: (state) => state.authErrors
    },

    actions:{
        async getUser(){
            const data = await api.get('/api/user')
            this.authUser = data.data
        },

        async login(data){
            this.authErrors = null

            try{
                await api.post('/api/login', {
                    email: data.email,
                    password: data.password
                });

                await this.router.push('/')

            } catch (ex){
                this.authErrors = ex.response.status
            }
        },

        async logout(){
            await axios.post('/api/logout')
            this.authUser = null;

            await this.router.push('/login')
        }
    }
})
