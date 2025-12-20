import { defineStore } from 'pinia';
import { ref } from 'vue';

const assemblyPathPriceSumm = ref(0);

export const useAssembly = defineStore('assemblyPathPriceSumm', () => {
    return {
        assemblyPathPriceSumm,
    };
});
