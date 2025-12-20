<template>
  <div v-if="delivery" class="flex flex-col justify-between gap-1 w-full h-full !p-2">
    <div v-if="delivery.address" class="text-xl font-bold">{{ delivery.address }}</div>
    <div v-if="delivery.schedule" class="text-gray-light text-base">{{ delivery.schedule }}</div>
    <div v-if="delivery.phone" class="text-gray-light text-base">{{ delivery.phone }}</div>
    <button
      @click="$emit('update:modelValue', delivery.id)"
      class="text-xs flex justify-center items-center !w-24 !h-8 !mt-4 border rounded-sm cursor-pointer"
      :class="{
        'text-green bg-white' : modelValue !== delivery.id,
        'text-white bg-green' : modelValue === delivery.id,
      }"
    >
      <template v-if="modelValue === delivery.id">
        Выбран
      </template>
      <template v-else>
        Выбрать
      </template>
    </button>
  </div>
</template>
<script setup>
import {
  defineProps,
  defineEmits
} from 'vue'

defineProps(['modelValue', 'delivery'])
defineEmits(['update:modelValue'])

</script>

<style scoped>

</style>