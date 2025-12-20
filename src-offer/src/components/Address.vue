<script setup>
import { defineProps, ref, watch, watchEffect, defineEmits } from 'vue';
import { useGet } from '../composables/get.js';
import { VueDadata } from 'vue-dadata';
import 'vue-dadata/dist/style.css';
import ky from 'ky';
import CustomInput from './CustomInput.vue';
import Loader from './ui/Loader.vue';
import { storeToRefs } from 'pinia';
import { useFloor } from '../pinia/getFloor';
import { useElevator } from '../pinia/getElevator';
import { getRightAddress } from '../pinia/getRightAddress';

const { rightAddress } = storeToRefs(getRightAddress());
const { checkedElevator } = storeToRefs(useElevator());
const { riseFloor } = storeToRefs(useFloor());

const url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';

const props = defineProps(['type', 'count', 'onChange']);
const emits = defineEmits(['updateAddress', 'updateValidAddress', 'input', 'updateRightAddress']);
const address = ref(),
    path = ref(0),
    pending = ref(false),
    apartment = ref(),
    type = ref(),
    query = ref(),
    suggestion = ref(),
    suggestionError = ref(null),
    token = import.meta.env.VITE_DADATA;

let lastTimeRequest = new Date();

const getAddress = async (typeValue) => {
    suggestionError.value = null;
    rightAddress.value = false;
    emits('updateRightAddress', rightAddress.value);

    await useGet(`get-address/${typeValue}`, pending, address, { is_ajax: 'Y' }).then(async () => {
        if (address.value.data.address) {
            await getDefaultAddress(address.value.data.address, query, suggestion);
        } else if (address.value.data.city) {
            await getDefaultAddress(address.value.data.city, query, suggestion);
        } else {
            query.value = null;
            suggestion.value = null;
        }
        apartment.value = address.value.data.flat;
        if (!address.value.data.address && !address.value.data.city)
            emits('updateAddress', { address: address.value, floor: riseFloor.value, apartment: apartment.value });
    });
};

const getDefaultAddress = async (address, query, suggestion) => {
    await ky
        .post(url, {
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                Authorization: `Token ${token}`,
            },
            body: JSON.stringify({
                count: 1,
                query: address,
            }),
        })
        .json()
        .then((response) => {
            // query.value = response.suggestions[0].value;
            suggestion.value = response.suggestions[0];
        });
};

const suggestionChange = (event) => {
    document.getElementById('yaMap').dispatchEvent(
        new CustomEvent(
            'pong',
            {
                detail: { address: event.target.value },
            },
            { bubbles: true }
        )
    );
    query.value = event.target.value;
    rightAddress.value = false;
    emits('updateRightAddress', rightAddress.value);
};

watchEffect(() => {
    type.value = props.type;
});

watch(type, async (newType) => {
    if (newType.value != type.value) {
        await getAddress(newType);
    }
});

watch(
    () => props.count,
    () => {
        if (!suggestion.value || !query.value || !rightAddress.value)
            suggestionError.value = 'Введите город, улицу и дом';
    }
);

watch([suggestion, apartment, rightAddress], ([newSuggestion, newApartment, newRightAddress]) => {
    if (apartment.value != newApartment) apartment.value = newApartment;
    if (suggestion.value != newSuggestion) suggestion.value = newSuggestion;
    if (rightAddress.value != newRightAddress) rightAddress.value = newRightAddress;

    if (rightAddress.value) {
        emits('updateValidAddress', true);
        emits('updateRightAddress', rightAddress.value);
    }

    if (newSuggestion) {
        let fullAddress = {
            address: suggestion.value,
            floor: riseFloor.value,
            apartment: apartment.value,
        };
        emits('updateAddress', fullAddress);
    } else emits('updateAddress', { address: null, floor: null, apartment: null });
    if (newSuggestion) {
        suggestionError.value =
            rightAddress.value === false
                ? 'Ведите адрес из доступного региона'
                : newSuggestion.data.city === null && newSuggestion.data.region_with_type === null
                ? 'Введите город, улицу и дом'
                : newSuggestion.data.street === null && newSuggestion.data.settlement === null
                ? 'Введите улицу и дом'
                : newSuggestion.data.house === null
                ? 'Введите номер дома'
                : null;
    }
});

watch(query, async (newQuery) => {
    const now = new Date();

    if (newQuery.length < 5 || now.getTime() - lastTimeRequest.getTime() <= 2000) return;
    lastTimeRequest = new Date();

    await getDefaultAddress(newQuery, query, suggestion);
    if (!suggestion.value?.data.address) return;

    let fullAddress = {
        address: suggestion.value.data.address,
        floor: riseFloor.value,
        apartment: apartment.value,
    };

    emits('updateAddress', fullAddress);
    emits('updateDelivery', checkedElevator.value);
});
</script>

<template>
    <input id="path" type="hidden" v-model="path" />

    <div class="flex flex-col gap-2 lg:gap-4" v-if="address && !pending">
        <!-- Основной адрес -->
        <label v-if="address?.data && address.data.hasOwnProperty('address')" class="flex flex-col gap-2 relative">
            <span class="flex gap-4 items-center">
                <span class="text-gray-light text-base leading-6">
                    Населенный пункт, улица, дом
                    <span class="text-red"> * </span>
                </span>
            </span>
            <VueDadata
                :classes="{
                    input: 'j-delivery-address-input w-full placeholder:text-gray-light border border-border rounded p-3 bg-gray-medium font-normal lg:p-4',
                }"
                v-model="query"
                v-model:suggestion="suggestion"
                :token="token"
                :onChange="suggestionChange" />
            <span v-if="suggestionError" class="text-red text-xs absolute bottom-0 translate-y-full font-normal">
                {{ suggestionError }}
            </span>
        </label>
        <div class="lg:grid lg:grid-cols-2 gap-8">
            <CustomInput
                v-model="apartment"
                @input="apartment = Number($event.target.value) <= 1000 ? Number($event.target.value) : 1000"
                :init="{
                    type: 'number',
                    isRequired: false,
                    title: 'Квартира или офис',
                    name: 'PROPS[APARTMENT]',
                    maxlength: 5,
                }" />
        </div>
    </div>
    <div class="flex justify-start items-center" v-else-if="pending">
        <Loader class="w-14 h-14" />
    </div>
</template>
