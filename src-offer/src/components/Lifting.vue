<template>
    <div>
        <TabGroup
            as="div"
            class="flex flex-col gap-6 lg:gap-12"
            :defaultIndex="0"
            :selectedIndex="selectedLifting"
            @change="changeLifting">
            <TabList as="div" class="flex gap-4 flex-wrap">
                <Tab
                    v-slot="{ selected }"
                    as="label"
                    class="flex bg-white duration-300 hover:border-green gap-3 items-center rounded-lg p-3 border hover:cursor-pointer w-full lg:w-auto lg:p-4 lg:rounded-sm"
                    :class="{
                        'border-green': selected,
                        'border-border': !selected,
                    }">
                    <span
                        class="w-6 h-6 min-w-6 rounded-full"
                        :class="{
                            'border-8 border-green': selected,
                            'border border-border': !selected,
                        }" />
                    <input hidden type="radio" :checked="selected" />
                    <span class="text-xl text-dark-gray leading-6"> Подъём не нужен</span>
                </Tab>
                <Tab
                    v-slot="{ selected }"
                    as="label"
                    class="flex bg-white duration-300 hover:border-green gap-3 items-center rounded-lg p-3 border hover:cursor-pointer w-full lg:w-auto lg:p-4 lg:rounded-sm"
                    :class="{
                        'border-green': selected,
                        'border-border': !selected,
                    }">
                    <span
                        class="w-6 h-6 min-w-6 rounded-full"
                        :class="{
                            'border-8 border-green': selected,
                            'border border-border': !selected,
                        }" />
                    <input hidden type="radio" :checked="selected" />
                    <span class="text-xl text-dark-gray leading-6"> Подъём на этаж </span>
                </Tab>
                <Tab
                    v-slot="{ selected }"
                    as="label"
                    class="flex bg-white duration-300 hover:border-green gap-3 items-center rounded-lg p-3 border hover:cursor-pointer w-full lg:w-auto lg:p-4 lg:rounded-sm"
                    :class="{
                        'border-green': selected,
                        'border-border': !selected,
                    }">
                    <span
                        class="w-6 h-6 min-w-6 rounded-full"
                        :class="{
                            'border-8 border-green': selected,
                            'border border-border': !selected,
                        }" />
                    <input hidden type="radio" :checked="selected" />
                    <span class="text-xl text-dark-gray leading-6"> Подъём на этаж на грузовом лифте</span>
                </Tab>
            </TabList>
            <TabPanels>
                <TabPanel as="div" class="flex flex-col gap-10"></TabPanel>
                <TabPanel as="div" class="flex flex-col gap-10">
                    <CustomInput
                        v-model="riseFloor"
                        @input="
                            riseFloor =
                                Number($event.target.value) < -100
                                    ? -100
                                    : Number($event.target.value) <= 200
                                    ? Number($event.target.value)
                                    : 200
                        "
                        :init="{
                            type: 'number',
                            isRequired: true,
                            placeholder: 'Введите значение',
                            title: 'Этаж',
                            default: 1,
                            name: 'PROPS[FLOOR]',
                            maxlength: 3,
                        }" />
                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
</template>

<script setup>
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import { defineProps, defineEmits, watch } from 'vue';
import CustomInput from './CustomInput.vue';
import { storeToRefs } from 'pinia';
import { useFloor } from '../pinia/getFloor';
import { useFlag } from '../pinia/getFlagFoor';
import { useElevator } from '../pinia/getElevator';

const { checkedElevator } = storeToRefs(useElevator());
const { hideRiseFloor } = storeToRefs(useFlag());
const { riseFloor } = storeToRefs(useFloor());

const props = defineProps(['selectedLifting']),
    emits = defineEmits(['updateFloor', 'updateDelivery', 'nameValue']);
    
    
const changeLifting = (index) => {
    props.selectedLifting = index;
    if (index == 0) {
        riseFloor.value = 0;
        hideRiseFloor.value = true;
        checkedElevator.value = false;
        emits('nameValue', 'Подъём не нужен');
        emits('updateDelivery', false);
    } else if (index == 1) {
        riseFloor.value = 1;
        hideRiseFloor.value = false;
        checkedElevator.value = false;
        emits('nameValue', 'Подъём на этаж');
        emits('updateDelivery', false);
    } else {
        riseFloor.value = 0;
        hideRiseFloor.value = true;
        checkedElevator.value = true;
        emits('nameValue', 'Подъём на этаж на грузовом лифте')
        emits('updateDelivery', true);
    }
};

watch(riseFloor, (newFloor) => {
    if (Number(newFloor) > 100) {
        riseFloor.value = 100;
        emits('updateFloor', riseFloor.value);
        return;
    }

    riseFloor.value = Math.abs(newFloor);
    emits('updateFloor', riseFloor.value);
});
</script>

<style lang="scss"></style>
