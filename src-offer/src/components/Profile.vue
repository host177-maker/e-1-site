<script setup>
import { ref, watch, defineProps, watchEffect,  defineEmits } from 'vue'
import { useGet } from "../composables/get.js"
import CompanyDadata from './CompanyDadata.vue'
import CustomInput from './CustomInput.vue'
import Loader from "./ui/Loader.vue"

const props = defineProps(['type', 'count'])
const emits = defineEmits(['updateValuesProfile'])

const
  infoCompany = ref(null),
  type = ref(),
  pending = ref(false),
  profile = ref(),
  valuesObject = ref({}),
  countValid = ref(0),
  tryCountValid = ref(0),
  comment = ref(null)
  

const getProfile = (typeValue) => {
  useGet(`get-personal/${ typeValue }`, pending, profile, { is_ajax: 'Y' }).then(() => {
      initObject()
  })
}

//Нужно придумать оптимальное решение 
const initObject = () => {
  const oldObject = [...profile.value.data.inputs]
  valuesObject.value = {}
  oldObject.forEach((input) => {
      if (input.isRequired) valuesObject.value[input.code] = null
  })
}

//Нужно придумать оптимальное решение 
const onValidate = ({valid, code, value}) => {
  valuesObject.value[code] = value
  countValid.value++
  if(valid){
    tryCountValid.value++
  }
  if (countValid.value === Object.keys(valuesObject.value).length) {
    if (tryCountValid.value === Object.keys(valuesObject.value).length) emits('updateValuesProfile', {...valuesObject.value, ...{ "COMMENT": comment.value }})
    else emits('updateValuesProfile', null)
    countValid.value = 0
    tryCountValid.value = 0 
  }
}

const updateValue = (value) => {
  infoCompany.value = value
}

watchEffect(() => {
  type.value = props.type
})

watch(type, async newType => {
  await getProfile(newType)
})
</script>

<template>
  <div
    v-if="profile && profile.data && !pending"
    class="flex flex-col gap-4 lg:max-w-lg"
    :class="{ 'animate-pulse pointer-events-none' : pending }"
  >
    <div
      v-for="input in profile.data.inputs"
      :key="input"
      class="flex items-center gap-4 w-full"
    >
      <CompanyDadata
        v-if="input.type === 'dadata'"
        @updateValue="updateValue"
        :init="input"
        class="w-full"
      />
      <CustomInput
        v-else
        :count="count"
        :infoCompany="infoCompany"
        :init="input"
        :pending="pending"
        class="w-full"
        @onValidate="onValidate"
      />
    </div>
    <div class="relative">
      <label class="flex flex-col gap-2">
        <span class="flex gap-4 items-center">
          <span class="text-gray-light text-base leading-6 font-normal">
            Комментарии к заказу
          </span>
        </span>
        <textarea v-model="comment" class="resize-none px-3 py-2 font-normal" placeholder="Особенности, которые стоит знать специалисту"></textarea>
      </label>
    </div>
  </div>
  <div v-else class="flex justify-start items-center">
    <Loader class="w-14 h-14"/>
  </div>
</template>