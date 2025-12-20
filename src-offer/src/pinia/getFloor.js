import { defineStore } from 'pinia';
import { ref } from 'vue';

const riseFloor = ref(0);

export const useFloor = defineStore('riseFloor', () => {
    return {
        riseFloor,
    };
});
