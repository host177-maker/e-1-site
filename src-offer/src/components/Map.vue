<script setup>
import { YandexMap, YandexMarker, loadYmap } from 'vue-yandex-maps';
import { ref, defineProps, watchEffect, defineEmits, watch, onMounted } from 'vue';
import CustomBalloon from './ui/CustomBalloon.vue';
import Loader from './ui/Loader.vue';

const settings = {
        apiKey: '69c834a0-6bb4-4573-810d-c5df44944d8b',
        lang: 'ru_RU',
        coordorder: 'latlong',
        enterprise: true,
        version: '2.1',
    },
    pickupValue = ref(),
    isOpen = ref(false),
    isMap = ref(false);

const props = defineProps(['modelValue', 'deliveries', 'isOpen', 'address', 'code']),
    emits = defineEmits(['update:modelValue']);
const onMap = () => {
    isMap.value = true;
};

watchEffect(() => {
    pickupValue.value = props.modelValue;
    isOpen.value = props.isOpen;
});
watch(isOpen, (open) => {
    if (!open) {
        isMap.value = false;
    }
});
watch(pickupValue, () => {
    emits('update:modelValue', pickupValue.value);
});
const determinationCoord = () => {
    if (!props.deliveries.find((el) => el.id === pickupValue.value)) {
        pickupValue.value = null;
    }
    if (pickupValue.value) {
        let activeDelivery = props.deliveries.find((el) => el.id === pickupValue.value);
        return [Number(activeDelivery.coordinates.latio), Number(activeDelivery.coordinates.ratio)];
    } else if (props.address.data.geo_lat)
        return [Number(props.address.data.geo_lat), Number(props.address.data.geo_lon)];
    else return [Number(props.deliveries[0].coordinates.latio), Number(props.deliveries[0].coordinates.ratio)];
};

const isMounted = ref(false);

onMounted(async () => {
    await loadYmap(settings);
    isMounted.value = true;
});
</script>

<template>
    <div v-if="address" class="relative">
        <div v-if="!isMap" class="z-50 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
            <Loader class="w-20 h-20" />
        </div>
        <YandexMap
            v-if="isMounted"
            :coordinates="determinationCoord()"
            :zoom="code === 'STORES' ? 4 : 10"
            :settings="settings"
            class="h-150 overflow-hidden"
            @created="onMap">
            <YandexMarker
                v-for="delivery in deliveries"
                :coordinates="[delivery.coordinates.latio, delivery.coordinates.ratio]">
                <template #component>
                    <CustomBalloon v-model="pickupValue" :delivery="delivery"></CustomBalloon>
                </template>
            </YandexMarker>
        </YandexMap>
    </div>
</template>

<style lang="scss">
.h-150 {
    .ymaps-2-1-79 {
        &-map {
            height: 37.5rem !important;
        }
    }
}
.ymaps-2-1-79 {
    &-balloon {
        border: 1px solid #62bb46 !important;
        &__tail {
            &::after {
                border: 1px solid #62bb46;
            }
        }
        &__content {
            // width: 400px !important;
            @media screen and (min-width: 668px) {
                // width: 300px !important;
                height: 220px !important;
            }
        }
    }
}
.yandex-balloon {
    width: 400px !important;
    height: 220px !important;
    @media screen and (min-width: 668px) {
        width: 300px !important;
        height: 220px !important;
    }
}
</style>
