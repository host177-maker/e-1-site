<script setup>
import { loadYmap } from 'vue-yandex-maps';
import { ref, watch, onMounted } from 'vue';
import { storeToRefs } from 'pinia';

import { getRightAddress } from '../pinia/getRightAddress';

const { rightAddress } = storeToRefs(getRightAddress());
const emits = defineEmits(['updateRightAddress', 'pingRightAddress']);

const settings = {
    apiKey: '51e35aa4-fa5e-432e-a7c6-e5e71105ec3a',
    lang: 'ru_RU',
    coordorder: 'longlat',
    enterprise: false,
    version: '2.1',
};

const onPing = async (event) => {
    // console.log('onPing', event.detail.success);
    rightAddress.value = event.detail.success;
    emits('updateRightAddress', rightAddress.value);
};

onMounted(async () => {
    await loadYmap(settings);
    yandexMapDeliveryLoad('yaMap');
});
</script>

<template>
    <div id="yaMap" class="map" @ping="onPing"></div>
</template>
