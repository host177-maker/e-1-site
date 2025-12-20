<script setup>
import { ref, defineProps, watch } from 'vue';
import { Dialog, DialogPanel, TransitionRoot } from '@headlessui/vue'

const props = defineProps(['answerSave'])

const answerSave = ref(false)
const message = ref()

watch(() => props.answerSave, () => {
    message.value = props.answerSave.message
    answerSave.value = true
})

const setIsOpen = () => {
    answerSave.value = false
}
</script>

<template>
    <div>
        <TransitionRoot
        :show="answerSave"
        as="template"
        enter="duration-300 ease-in"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
        >
        <Dialog
            @close="setIsOpen"
            class="relative z-50"
        >
            <div class="bg-fixed/70 fixed inset-0 flex items-center justify-center p-4">
            <DialogPanel v-if="message" class="w-full mx-6 h-auto flex items-center justify-center text-xl relative rounded bg-white text-black font-bold !p-6 lg:!p-12 lg:!w-1/3 lg:!mx-0">
                <div v-html="message"/>
                <div @click="setIsOpen" class="absolute right-0 top-0">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 8L8 16M8.00001 8L16 16" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </DialogPanel>
            </div>
        </Dialog>
        </TransitionRoot>
    </div>
</template>