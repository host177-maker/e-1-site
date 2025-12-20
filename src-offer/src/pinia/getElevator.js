import { defineStore } from 'pinia';
import { ref } from 'vue';

const checkedElevator = ref(false);

export const useElevator = defineStore('checkedElevator', () => {
    return {
        checkedElevator,
    };
});
