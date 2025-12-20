<script setup>
import * as yup from 'yup';
import { useField } from 'vee-validate';
import { mask as vMask, tokens } from 'vue-the-mask';
import Loader from './ui/Loader.vue';
import { onMounted, watch, defineEmits, defineProps } from 'vue';

const props = defineProps(['modelValue' ? 'modelValue' : null, 'init', 'pending', 'infoCompany', 'count']);
const emits = defineEmits(['onValidate']);

const schema = {
    email: yup
        .string('Введите поле в правильном формате')
        .matches(
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
            'Введите поле в правильном формате'
        )
        .required('Заполните данное поле')
        .email('Введите поле в правильном формате'),
    text: yup.string('Введите поле в правильном формате').required('Заполните данное поле'),
    tel: yup
        .string('Введите поле в правильном формате')
        .matches(/(?:\+|\d)[\d\-\(\) ]{16,}\d/g, 'Введите корректный номер телефона')
        .required('Заполните данное поле'),
    number: yup
        .number('Введите поле в правильном формате')
        .typeError('Введите поле в правильном формате')
        .required('Заполните данное поле'),
};

const {
    value: inputValue,
    errorMessage,
    handleChange,
    meta,
    validate,
} = useField(props.init.code, schema[props.init.type], { initialValue: props.init.default });

//функция для фалидации полетй типа number(Что бы нельзя было вводить дробные и отрицательные)
const validNumber = (event) => {
    return (event.charCode != 8 && event.charCode == 0) || (event.charCode >= 48 && event.charCode <= 57);
};

watch(
    () => props.modelValue,
    () => {
        if (props.modelValue) emits('update:modelValue', inputValue.value);
    }
);

watch(
    () => props.infoCompany,
    () => {
        if (props.init.code === 'COMPANY') inputValue.value = props.infoCompany.company;
        if (props.init.code === 'INN') inputValue.value = props.infoCompany.inn;
        if (props.init.code === 'CONTACT_PERSON') inputValue.value = props.infoCompany.name;
        if (props.init.code === 'PHONE') inputValue.value = props.infoCompany.phones;
        if (props.init.code === 'EMAIL') inputValue.value = props.infoCompany.emails;
    }
);

watch(
    () => props.count,
    () => {
        checkValid();
    }
);

onMounted(() => {
    tokens.N = {
        pattern: /[9]/,
        transform: (v) => v.toLocaleUpperCase(),
    };
});

const checkValid = () => {
    validate().then((res) => {
        emits('onValidate', { valid: res.valid, code: props.init.code, value: inputValue.value });
    });
};
</script>

<template>
    <div class="relative">
        <label class="flex flex-col gap-2">
            <span class="flex gap-4 items-center">
                <span v-if="init.title" class="text-gray-light text-base leading-6 font-normal">
                    {{ init.title }}
                    <span v-if="init.isRequired" class="text-red">*</span>
                </span>
                <Loader v-if="pending" />
            </span>
            <input
                v-if="init.type === 'tel'"
                v-mask="'+7 (N##) ###-##-##'"
                :tokens="tokens"
                class="placeholder:text-gray-light text-black font-normal border border-border rounded p-3 bg-gray-medium lg:p-4 phone"
                :type="init.type"
                :required="init.isRequired"
                :placeholder="init.placeholder"
                :name="init.name"
                :value="inputValue"
                @input="handleChange" />
            <input
                v-else-if="init.code === 'INN'"
                v-mask="'############'"
                :tokens="tokens"
                class="placeholder:text-gray-light text-black font-normal border border-border rounded p-3 bg-gray-medium lg:p-4 outline-none"
                :type="init.type"
                :required="init.isRequired"
                :placeholder="init.placeholder"
                :name="init.name"
                :value="inputValue"
                @input="handleChange" />
            <input
                v-else
                class="placeholder:text-gray-light text-black font-normal border border-border rounded p-3 bg-gray-medium lg:p-4 outline-none"
                :onkeypress="init.type === 'number' ? validNumber : null"
                :type="init.type"
                :required="init.isRequired"
                :placeholder="init.placeholder"
                :name="init.name"
                :value="inputValue"
                @input="handleChange" />
        </label>
        <p class="absolute text-xs text-red left-0 bottom-0 translate-y-full" v-show="errorMessage || meta.valid">
            {{ errorMessage }}
        </p>
    </div>
</template>

<style>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
}
input[type='number'] {
    -moz-appearance: textfield;
}
</style>
