<template>
    <div v-if="!pendingDeliveries && !pendingCities">
        <TabGroup as="div" class="flex flex-col gap-6 lg:gap-12" :defaultIndex="0" :selectedIndex="selectedDelivery"
                  @change="changeDelivery">
            <TabList as="div" class="flex gap-4 flex-wrap">
                <Tab v-slot="{ selected }" as="label" v-for="delivery in deliveries?.data"
                     class="flex bg-white duration-300 hover:border-green gap-3 items-center rounded-lg p-3 border hover:cursor-pointer w-full lg:w-auto lg:p-4 lg:rounded-sm"
                     :class="{
        'border-green': delivery.id === checkedDelivery.id,
        'border-border': delivery.id !== checkedDelivery.id,
    }">
                    <span class="w-6 h-6 min-w-6 rounded-full" :class="{
        'border-8 border-green': selected,
        'border border-border': !selected,
    }"/>
                    <input hidden type="radio" :checked="selected" :value="delivery.id" v-model="deliveriesValue"/>
                    <span class="text-xl text-dark-gray leading-6">
                        {{ delivery.title }}
                    </span>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel as="div" class="flex flex-col gap-10" v-for="delivery in deliveries?.data">
                    <div v-if="delivery.deliveries">
                        <h5 class="text-2.5xl text-dark-gray mb-8">Выберите пункт самовывоза</h5>
                        <div class="flex flex-col gap-2 mb-5">
                            <div
                                class="py-7 !px-5 gap-2 flex flex-col justify-between border-border border max-w-2xl rounded-sm sm:px-10 sm:flex-row"
                                v-for="delivery in delivery.deliveries"
                                :class="{ 'border-green': pickup === delivery.id }">
                                <div class="flex flex-col gap-1">
                                    <p class="text-xl text-dark-gray" v-if="delivery.address">
                                        {{ delivery.address }}
                                    </p>
                                    <p class="text-base text-gray-light" v-if="delivery.schedule">
                                        {{ delivery.schedule }}
                                    </p>
                                    <a class="text-base text-gray-light" v-if="delivery.phone"
                                       :href="`tel:${delivery.phone}`">{{ delivery.phone }}</a>
                                </div>
                                <button @click="pickup = delivery.id"
                                        class="uppercase text-xxs duration-300 flex justify-center py-2 px-5 border border-green rounded-sm h-fit"
                                        :class="{
        'text-green bg-white': pickup !== delivery.id,
        'text-white bg-green': pickup === delivery.id,
    }">
                                    <template v-if="pickup === delivery.id"> Выбран</template>
                                    <template v-else> Выбрать</template>
                                </button>
                            </div>
                        </div>
                        <button
                            class="uppercase w-full text-xxs text-green px-4 py-2 border border-green rounded-sm sm:w-auto"
                            @click="setIsOpen(true)">
                            Пункты самовывоза на карте
                        </button>
                        <TransitionRoot v-if="address" :show="mapModal" as="template" enter="duration-300 ease-in"
                                        enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in"
                                        leave-from="opacity-100" leave-to="opacity-0">
                            <Dialog @close="setIsOpen" class="relative z-50">
                                <div class="bg-fixed/70 fixed inset-0 flex items-center justify-center p-4">
                                    <DialogPanel class="w-full max-w-4xl relative rounded bg-white p-4 sm:p-12">
                                        <Map v-model="pickup" :address="address" :is-open="mapModal"
                                             :code="delivery.code" :deliveries="delivery.deliveries"/>
                                        <Close class="absolute top-4 right-4 hover:cursor-pointer"
                                               @click="setIsOpen(false)"/>
                                    </DialogPanel>
                                </div>
                            </Dialog>
                        </TransitionRoot>
                    </div>
                    <div v-if="delivery && delivery.deliveryTime">{{ delivery.deliveryTime }}</div>
                    <div v-if="price">
                        <p class="price-text">Доставка {{ price }} руб.</p>
                    </div>
                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
    <div v-else class="flex justify-start items-center">
        <Loader class="w-14 h-14"/>
    </div>
</template>

<script setup>
import {TabGroup, TabList, Tab, TabPanels, TabPanel, Dialog, DialogPanel, TransitionRoot} from '@headlessui/vue';
import {watchEffect, ref, defineProps, defineEmits, watch, computed, onBeforeMount, onMounted} from 'vue';
import {useGet} from '../composables/get.js';
import Loader from '../components/ui/Loader.vue';
import Map from './Map.vue';
import Close from './ui/Close.vue';
import {storeToRefs} from 'pinia';
import {useFloor} from '../pinia/getFloor';
import {useElevator} from '../pinia/getElevator';
import {useAssembly} from '../pinia/getAssembly';

const {assemblyPathPriceSumm} = storeToRefs(useAssembly());
const {checkedElevator} = storeToRefs(useElevator());
const {riseFloor} = storeToRefs(useFloor());

const props = defineProps(['address', 'path']);
const emits = defineEmits(['updateDelivery', 'updateRightAddress']);

const fullInfoActiveDelivery = ref({}),
    price = ref(null),
    cities = ref(),
    pickup = ref(),
    address = ref(),
    typeValue = ref(),
    deliveriesValue = ref(),
    mapModal = ref(false),
    actualCity = computed(() => {
        return cities.value?.data.length > 0 && address.value
            ? cities.value.data.find((city) => city.title === address.value.data.city)
            : null;
    }),
    place = ref({}),
    pendingCities = ref(false),
    pendingDeliveries = ref(false),
    deliveries = ref(null),
    selectedDelivery = ref(0),
    changeDelivery = (index) => {
        selectedDelivery.value = index;
        emits('updateRightAddress', index);
    },
    checkedDelivery = computed(() => {
        sendInfoDeliveries(deliveries.value?.data[selectedDelivery.value], checkedElevator.value);
        return deliveries.value?.data[selectedDelivery.value];
    });

function setIsOpen(value) {
    mapModal.value = value;
}

const getCities = async () => {
    await useGet('get-cities', pendingCities, cities, {is_ajax: 'Y'});
};

const setDefaultValues = () => {
    deliveries.value.data.forEach((deliveryItem) => {
        if (deliveryItem.types) typeValue.value = deliveryItem.types[0];
    });
};

const getDeliveries = async () => {
    await useGet(
        `get-deliveries`,
        pendingDeliveries,
        deliveries,
        Object.assign({path_km: props.path, is_ajax: 'Y'}, place.value ? place.value : null)
    ).then(() => {
        setDefaultValues();
    });
};

watchEffect(() => {
    if (props.address) {
        address.value = props.address;
        getDeliveries();
    }
});

watch(actualCity, () => {
    if (actualCity.value) {
        place.value = {city: actualCity.value.code};
        getDeliveries();
    } else if (props.address && props.address.data.region) {
        place.value = {region: props.address.data.region + ' ' + props.address.data.region_type_full};
        getDeliveries();
    } else getDeliveries();
});

watch(riseFloor, () => {
    sendInfoDeliveries(deliveries.value?.data[selectedDelivery.value], checkedElevator.value);
});

watch(selectedDelivery, () => {
    sendInfoDeliveries(deliveries.value?.data[selectedDelivery.value], false);
});

const previousPrice = ref(0);

const calculatingThePrice = (delivery) => {
    if (delivery.types) {
        if (typeValue.value && checkedElevator.value) {
            price.value =
                Number(delivery.price) +
                Number(delivery.path_price_sum) +
                Number(typeValue.value.additionalOptions[0].additionalPrice);
        } else if (typeValue.value && riseFloor.value > 0 && !checkedElevator.value) {
            price.value =
                Number(delivery.price) +
                Number(delivery.path_price_sum) +
                riseFloor.value * Number(typeValue.value.additionalPrice);
        } else if (typeValue.value && !checkedElevator.value && riseFloor.value == 0) {
            price.value = Number(delivery.price) + Number(delivery.path_price_sum);
        } else {
            price.value = 0;
        }
    } else price.value = 0;

    if (Number(delivery.path_price_sum) > 0) {
        let calcPrice = parseInt(price.value / 250) * 250;
        if (calcPrice > 0) {
            price.value = calcPrice + 250;
            previousPrice.value = price.value;
        } else {
            price.value = previousPrice.value;
        }
    }

    if (Number(delivery.assembly_path_price_summ) > 0) {
        assemblyPathPriceSumm.value = Number(delivery.assembly_path_price_summ);
        price.value += Number(delivery.assembly_path_price_summ);
    }

};

const sendInfoDeliveries = (delivery, elevator) => {
    calculatingThePrice(delivery);
    fullInfoActiveDelivery.value = {};
    fullInfoActiveDelivery.value = {
        id: delivery.id,
        price: price.value,
    };
    if (delivery.types) {
        if (typeValue.value && typeValue.value.additionalOptions)
            Object.assign(fullInfoActiveDelivery.value, {type: typeValue.value, elevator: elevator});
        else Object.assign(fullInfoActiveDelivery.value, {type: typeValue.value});
    } else if (delivery.deliveries)
        Object.assign(fullInfoActiveDelivery.value, {
            delivery: delivery.deliveries.find((el) => el.id === pickup.value) || null,
        });

    emits('updateDelivery', fullInfoActiveDelivery.value);
};

onMounted(() => {
    const path = document.getElementById('path');
    props.path = parseInt(path.value);
});
onBeforeMount(() => {
    getCities();
});
</script>

<style lang="scss">
.price-text {
    @apply text-2xl sm:text-2.5xl;

    span {
        @apply text-green;
    }
}
</style>
