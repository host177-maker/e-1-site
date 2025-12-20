import { defineStore } from 'pinia'
import { ref } from 'vue'

const globalLoading = ref(false)

export const useLoading = defineStore('loading', () => {
    return {
        globalLoading
    }
})
