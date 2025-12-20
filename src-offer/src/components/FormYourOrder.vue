<script setup>
import { ref, defineProps, watch, onBeforeMount, watchEffect, computed, onMounted } from 'vue';
import { Switch } from '@headlessui/vue';
import { useGet } from '../composables/get.js';
import Loader from './ui/Loader.vue';
import { storeToRefs } from 'pinia';
import { useLoading } from '../pinia/loading';
import { useFloor } from '../pinia/getFloor';
import { useAssembly } from '../pinia/getAssembly';
import { useElevator } from '../pinia/getElevator';

const { riseFloor } = storeToRefs(useFloor());
const { assemblyPathPriceSumm } = storeToRefs(useAssembly());
const { checkedElevator } = storeToRefs(useElevator());

const { globalLoading } = storeToRefs(useLoading());
const props = defineProps(['delivery', 'address', 'path']);
const emits = defineEmits(['updateAmount']);

const services = ref(false),
    goodsCount = ref(null),
    discount = ref(null),
    totalGoods = ref(null),
    total = ref(null),
    assembly = ref(null),
    floorPrice = ref(0),
    elevatorPrice = ref(0),
    address = ref(null),
    deliveryId = ref(null),
    checkedPromo = ref(false),
    promo = ref(),
    successPromo = ref(),
    promoError = ref(false),
    cities = ref(),
    place = ref({}),
    pendingCities = ref(true),
    pending = ref(false),
    infoPrice = ref(),
    actualCity = computed(() => {
        return cities.value?.data.length > 0 && address.value
            ? cities.value.data.find((city) => city.title === address.value.data.city)
            : null;
    });

let YaPay = null;

const getPromo = async () => {
    await useGet(
        `promo/${promo.value}`,
        pending,
        successPromo,
        Object.assign({ path_km: props.path, is_ajax: 'Y' }, place.value ? place.value : null)
    ).then(() => {
        if (successPromo.value.status.accepted) {
            goodsCount.value = successPromo.value.data.goodsCount;
            discount.value = successPromo.value.data.discount;
            totalGoods.value = successPromo.value.data.totalGoods;
            total.value = successPromo.value.data.total;
            assembly.value = successPromo.value.data.assembly;
        }
    });
    globalLoading.value = true;
};

const getCities = async () => {
    await useGet('get-cities', pending, cities, { is_ajax: 'Y' });
};

const getInfoOrder = async () => {
    globalLoading.value = false;
    await useGet(
        `get-info`,
        pendingCities,
        infoPrice,
        Object.assign({ path_km: props.path, is_ajax: 'Y' }, place.value ? place.value : null)
    ).then(() => {
        goodsCount.value = infoPrice.value.data.goodsCount;
        discount.value = infoPrice.value.data.discount;
        totalGoods.value = infoPrice.value.data.totalGoods;
        total.value = infoPrice.value.data.total;
        services.value = infoPrice.value.data.services;
        assembly.value = infoPrice.value.data.assembly;
        floorPrice.value = infoPrice.value.data.riseFloor;
        elevatorPrice.value = infoPrice.value.data.elevatorPrice;

        emits('updateAmount', infoPrice.value.data.total);

        getPromo();

        if (!YaPay) {
            YaPay = window.YaPay;
            YaPay.mountBadge(document.querySelector('#yapay-badge'), {
                type: 'cashback',
                amount: total.value,
                size: 'm',
                theme: 'light',
                align: 'left',
                color: 'primary',
                merchantId: '5ef67561-d34e-45e2-bb25-8320a4653548',
            });
        }
    });
};

watch(
    () => [deliveryId.value, address.value],
    (newId, newCity) => {
        if (props.delivery && actualCity.value && actualCity.value.code) {
            place.value = { delivery_id: props.delivery.id, city: actualCity.value.code };
            getInfoOrder();
        } else if (props.delivery && props.address?.address?.data?.region) {
            place.value = {
                delivery_id: props.delivery.id,
                region: props.address.address.data.region + ' ' + props.address.address.data.region_type_full,
            };
            getInfoOrder();
        }
    }
);

watchEffect(() => {
    address.value = props.address?.address;
    deliveryId.value = props.delivery?.id;
});
onBeforeMount(() => {
    getCities();
});
</script>

<template>
    <div v-if="!pendingCities"
        class="sticky top-20 flex flex-col gap-6 h-auto p-4 rounded-lg bg-white text-dark-gray transition-all">
        <div class="flex justify-between items-end">
            <div class="text-3xl">Ваш заказ</div>
            <a href="/basket" class="text-sm text-green uppercase leading-6 cursor-pointer">(изменить)</a>
        </div>
        <div v-if="infoPrice && infoPrice.data">
            <div class="flex justify-between">
                <div v-if="services" class="flex">Товары и услуги ({{ goodsCount }})</div>
                <div v-else class="flex">Товаров ({{ goodsCount }})</div>
                <div>{{ totalGoods }} руб.</div>
            </div>
            <div v-if="discount && discount > 0" class="flex justify-between">
                <div>Скидка</div>
                <div class="text-red">- {{ discount }} руб.</div>
            </div>
            <!-- <div v-if="assembly && assembly > 0" class="flex justify-between">
                <div>Из них сборка</div>
                <div>{{ assembly }} руб.</div>
            </div> -->
            <div v-if="delivery && delivery.price" class="flex justify-between">
                <div>Доставка</div>
                <div>{{ delivery.price }} руб.</div>
            </div>
            <div v-if="checkedElevator && elevatorPrice" class="flex justify-between">
                <div>Из них подъем на лифте</div>
                <div>{{ Number(elevatorPrice) }} руб.</div>
            </div>
            <div v-if="riseFloor && floorPrice" class="flex justify-between">
                <div>Из них подъем на этаж</div>
                <div>{{ Number(riseFloor) * Number(floorPrice) }} руб.</div>
            </div>
            <div v-if="assemblyPathPriceSumm > 0" class="flex justify-between">
                <div>Выезд сборщика</div>
                <div>{{ assemblyPathPriceSumm }} руб.</div>
            </div>
        </div>
        <div class="border-b border-border"></div>
        <div v-if="infoPrice && infoPrice.data"
            class="flex flex-col justify-between text-2xl font-medium 2xl:font-normal 2xl:text-3xl">
            <div>Итого:</div>
            <div v-if="delivery && delivery.price">
                {{ Number(total) + Number(delivery.price) + Number(assemblyPathPriceSumm) }} руб.</div>
            <div v-else>{{ Number(total) + Number(assemblyPathPriceSumm) }} руб.</div>
        </div>
        <div class="flex gap-4 items-center">
            <Switch as="template" v-model="checkedPromo">
                <button
                    class="relative bg-border inline-flex h-[24px] w-[44px] shrink-0 cursor-pointer rounded-full border-[3px] border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
                    :class="{ 'bg-green': checkedPromo }">
                    <span aria-hidden="true" :class="checkedPromo ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-[18px] w-[18px] transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
                </button>
            </Switch>
            <div class="font-bold">У меня есть промокод</div>
        </div>
        <div v-if="checkedPromo" class="flex w-full h-11 text-sm">
            <input v-model="promo" class="w-full h-full pl-2 py-2 outline-none rounded-l-lg bg-gray-medium"
                placeholder="Введите промокод" type="text" />
            <button @click="getPromo(), (promoError = true)" class="h-full px-2 rounded-r-lg bg-green text-white">
                Применить
            </button>
        </div>
        <div v-if="promoError && checkedPromo">
            <div v-if="successPromo.status.accepted" class="text-green">{{ successPromo.status.description }}</div>
            <div v-else class="text-red">{{ successPromo.status.description }}</div>
        </div>
    </div>
    <div class="flex justify-start items-center sticky top-20" v-else-if="pendingCities">
        <Loader class="w-14 h-14" />
    </div>
</template>

<style scoped></style>
