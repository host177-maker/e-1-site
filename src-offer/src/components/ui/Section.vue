<script setup>
import {
  defineProps
} from 'vue'
defineProps(['title'])

</script>

<template>
  <div
    class="p-7 bg-white border rounded-lg border-border lg:p-12"
  >
    <h3
      class="text-2.5xl text-dark-gray mb-2 lg:mb-8"
    >
      {{ title }}
    </h3>
    <div
      class="w-full flex flex-col gap-4"
    >
      <slot/>
    </div>
  </div>
</template>
