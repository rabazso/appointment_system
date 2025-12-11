import { defineStore } from "pinia"
import axiosClient from "@utils/http.mjs"

export const useUserStore = defineStore('user', {
    state: () => ({user:null}),
    actions: {
        fetchUser(){
            return axiosClient.get('/api/user').then(({data}) => {
                console.log(data)
                this.user = data
            })
        }
    }
})