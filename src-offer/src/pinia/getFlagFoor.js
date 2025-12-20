import { defineStore } from 'pinia'
import { ref } from 'vue'

const hideRiseFloor = ref(true)

export const useFlag = defineStore('getFlagFoor', () => {
    return {
        hideRiseFloor
    }
})
