<script setup>
import { VueDadata } from "vue-dadata"
import "vue-dadata/dist/style.css"
import {
  ref
} from "vue"
import { watch, defineEmits, defineProps } from 'vue'

const props = defineProps(['init'])
const emits = defineEmits(['updateValue'])

const token = import.meta.env.VITE_DADATA
const query = ref()
const suggestion = ref()

watch(suggestion, () => {
  if(suggestion) {
    emits('updateValue', {
      company: suggestion.value.data.name.short_with_opf,
      inn: suggestion.value.data.inn,
      name: suggestion.value.data.management.name,
      phones: suggestion.value.data.phones,
      emails: suggestion.value.data.emails
    })
  }
})
</script>

<template>
  <div class="flex flex-col gap-4 lg:max-w-lg">
    <span class="flex gap-4 items-center">
      <span
        class="text-gray-light text-base leading-6"
        v-if="init.title"
      >
        {{ init.title }}
        <span
          class="text-red"
          v-if="init.isRequired"
        >
          *
        </span>
      </span>
    </span>
    <VueDadata
      autocomplete="off"
      :placeholder="init.placeholder"
      :classes="{
        input : 'w-full placeholder:text-gray-light border border-border rounded p-3 bg-gray-medium lg:p-4'
      }"
      v-model="query"
      v-model:suggestion="suggestion"
      :token="token"
      :debounce="300"
      :url="'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party'"
    />
  </div>
</template>
