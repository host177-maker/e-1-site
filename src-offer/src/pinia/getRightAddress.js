import { defineStore } from 'pinia';
import { ref } from 'vue';

const rightAddress = ref(false);

export const getRightAddress = defineStore('rightAddress', () => {
    return {
        rightAddress,
    };
});
