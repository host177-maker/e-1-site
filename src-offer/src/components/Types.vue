<script setup>
import { onMounted, ref, watch, defineEmits } from 'vue';
import { useGet } from '../composables/get.js';
import Loader from './ui/Loader.vue';
import { storeToRefs } from 'pinia';
import { useLoading } from '../pinia/loading';

const { globalLoading } = storeToRefs(useLoading());

const pending = ref(false),
    types = ref(),
    typesValue = ref(),
    emits = defineEmits(['update']),
    getTypes = () => {
        useGet('get-types', pending, types, { is_ajax: 'Y' }).then(() => {
            typesValue.value = types.value.data[0].id;
        });
    };

watch(typesValue, (newTypes) => {
    emits('update', newTypes);
});

onMounted(() => {
    getTypes();
});
</script>

<template>
    <div v-if="!pending" class="flex flex-col gap-2 lg:flex-row lg:gap-4">
        <label
            class="flex bg-white duration-300 hover:border-green gap-3 items-center rounded-lg px-4 py-2 border hover:cursor-pointer w-full lg:max-w-xxs"
            :class="{
                'border-green': typesValue === type.id,
                'border-border': typesValue !== type.id,
            }"
            v-for="type in types?.data">
            <span
                class="w-6 h-6 min-w-6 rounded-full"
                :class="{
                    'border-8 border-green': typesValue === type.id,
                    'border border-border': typesValue !== type.id,
                }" />
            <input
                hidden
                type="radio"
                :checked="typesValue === type.id"
                :value="type.id"
                v-model="typesValue"
                :disabled="!globalLoading" />
            <span class="text-base text-dark-gray leading-6">
                {{ type.title }}
            </span>
        </label>
    </div>
    <div v-else class="flex justify-start items-center">
        <Loader class="w-14 h-14" />
    </div>
</template>

<style scoped></style>
