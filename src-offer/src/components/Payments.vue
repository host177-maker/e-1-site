<script setup>
import { useGet } from '../composables/get.js';
import Loader from './ui/Loader.vue';
import { computed, onMounted, ref, defineEmits } from 'vue';

const emits = defineEmits(['updatePayments']);

const payments = ref(),
    pending = ref(),
    getPayments = async () => {
        await useGet('get-payments', pending, payments, { is_ajax: 'Y' });
    },
    paymentsValue = ref(),
    selectedPayment = computed(() => {
        emits(
            'updatePayments',
            payments.value?.data.find((payment) => payment?.id === paymentsValue.value)
        );
        return payments.value?.data.find((payment) => payment?.id === paymentsValue.value);
    });

onMounted(() => {
    getPayments().then(() => {
        paymentsValue.value = payments.value?.data[0].id;
    });
});
</script>

<template>
    <div v-if="!pending" class="flex gap-2 flex-col">
        <label
            class="bg-white duration-300 hover:border-green rounded-lg p-3 w-full border hover:cursor-pointer lg:w-auto lg:p-4 lg:rounded-sm"
            :class="{
                'border-green': paymentsValue === payment.id,
                'border-border': paymentsValue !== payment.id,
            }"
            v-for="payment in payments?.data">
            <div
                class="flex sm:flex-nowrap items-center gap-3 sm:whitespace-nowrap"
            >
                <span
                    class="w-6 h-6 min-w-6 rounded-full"
                    :class="{
                        'border-8 border-green': paymentsValue === payment.id,
                        'border border-border': paymentsValue !== payment.id,
                    }" />
                <input
                    hidden
                    type="radio"
                    :checked="paymentsValue === payment.id"
                    :value="payment.id"
                    v-model="paymentsValue" />
                <span class="text-xl text-dark-gray leading-6">
                    {{ payment.title }}
                </span>
                <span v-if="payment.id != 13" class="ml-auto"><img :src="payment.picture" alt="" /></span>
                <span v-else class="ml-auto" id="yapay-badge"></span>
            </div>
            <div v-if="payment.id == 16" v-show="paymentsValue === payment.id" class="split-div mt-4">
                <!--ДИВ ДЛЯ ВИДЖЕТА-->
            </div>
        </label>
    </div>

    <div v-if="payments?.data && selectedPayment">
        {{ selectedPayment.description }}
    </div>
    <div v-else class="flex justify-start items-center">
        <Loader class="w-14 h-14" />
    </div>
</template>
