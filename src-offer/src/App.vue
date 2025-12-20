<script setup>
import { ref } from 'vue';
import { usePost } from './composables/post.js';

const validAddress = ref(false),
    type = ref(),
    address = ref(),
    floor = ref(),
    delivery = ref(),
    profileValues = ref(),
    payments = ref(),
    count = ref(1),
    infoOrder = ref({}),
    answerSave = ref(),
    notRightAddress = ref(true),
    totalAmount = ref(0),
    namefloor = ref('Подъём не нужен');

let activeSession = null;

const YaPay = window.YaPay;
const paymentData = {
    // Для отладки нужно явно указать `SANDBOX` окружение,
    // для продакшена параметр можно убрать или указать `PRODUCTION`
    // env: YaPay.PaymentEnv.Sandbox,

    // Версия 4 указывает на тип оплаты сервисом Яндекс Пэй
    // Пользователь производит оплату на форме Яндекс Пэй,
    // и мерчанту возвращается только результат проведения оплаты
    version: 4,
    currencyCode: YaPay.CurrencyCode.Rub,

    merchantId: '5ef67561-d34e-45e2-bb25-8320a4653548',
    availablePaymentMethods: ['SPLIT', 'CARD'],
};

const updateFloor = (value) => {
    floor.value = value;
    address.value.floor = value;
};

const nameValue = (value) =>{
    namefloor.value = value
}
const updateRightAddress = (value) => {
    if (value === 0) {
        if (validAddress.value == true) {
            notRightAddress.value = false;
            return;
        }
    }
    notRightAddress.value = !value;
};

const updateValidAddress = (value) => {
    validAddress.value = value;
};

const updateTypes = (value) => {
    type.value = value;
};
const updateAddress = (value) => {
    address.value = value;
};

const updateDelivery = (value) => {
    delivery.value = value;
};

const updateAmount = (value) => {
    totalAmount.value = value;
};

const updatePayments = (value) => {
    payments.value = value || { id: 10 };

    async function onPayButtonClick() {
        /* Создание заказа... */
    }

    if (payments.value.id == 16) {
        Object.assign(paymentData, { totalAmount: totalAmount.value });
        const YaPay = window.YaPay;

        // Создаем платежную сессию
        YaPay.createSession(paymentData, {
            onPayButtonClick: onPayButtonClick,
        })
            .then(function (paymentSession) {
                activeSession = paymentSession;
                paymentSession.mountWidget(document.querySelector('.split-div'), {
                    widgetType: YaPay.WidgetType.BnplPreview,
                    buttonTheme: YaPay.ButtonTheme.Black,
                    bnplSelected: false,
                    borderRadius: 8,
                });
            })
            .catch(function (err) {
                console.log('createSession error:', err);
            });
    } else {
        if (activeSession) {
            activeSession.destroy();
            activeSession = null;
        }
    }
};

const updateValuesProfile = (values) => {
    profileValues.value = values;
    sendInfo();
};

const floorIsEmpty = () => {
    const floorEl = document.getElementsByName('PROPS[FLOOR]');
    if (floorEl.length == 0) return false;

    if (floorEl[0].value.length == 0) {
        return true;
    }
    return false;
};

const sendInfo = async () => {
    if (profileValues.value && (validAddress.value || delivery.value.delivery) && !floorIsEmpty()) {
        infoOrder.value = {
            DELIVERY_ID: delivery.value.id,
            PAYMENT_ID: payments.value.id,
            COMMENT: null,
            CUSTOM_PRICE_DELIVERY: delivery.value.price,
            ELEVATOR: delivery.value.elevator,
            FLOOR: floor.value,
            LIFTING: namefloor.value,
        };
        if (delivery.value.type) {
            if (!delivery.value.type.additionalOptions)
                Object.assign(infoOrder.value, { OWN_DELIVERY_INFO: delivery.value.type.title });
            else
                Object.assign(infoOrder.value, {
                    OWN_DELIVERY_INFO: delivery.value.type.title,
                    ELEVATOR: delivery.value.elevator,
                });
        }
        if (delivery.value.delivery)
            Object.assign(infoOrder.value, {
                PUNKT_SAM_ID: delivery.value.id,
                PUNKT_SAM: delivery.value.delivery.address,
            });
        else
            Object.assign(infoOrder.value, {
                CITY: address.value.address.data.city,
                //address.value.address.unrestricted_value
                ADDRESS:
                    address.value.address.data.city +
                    ', ' +
                    address.value.address.data.street_with_type +
                    ', ' +
                    address.value.address.data.house +
                    (address.value.address.data.block
                        ? ' ' + address.value.address.data?.block_type + ' ' + address.value.address.data.block
                        : ''),
                //address.value.address.unrestricted_value
                COMPANY_ADR:
                    address.value.address.data.city +
                    ', ' +
                    address.value.address.data.street_with_type +
                    ', ' +
                    address.value.address.data.house +
                    (address.value.address.data.block
                        ? ' ' + address.value.address.data?.block_type + ' ' + address.value.address.data.block
                        : ''),
                FLOOR: floor.value,
                APARTMENT: address.value.apartment,
            });

        Object.assign(infoOrder.value, profileValues.value);

        let form_data = new FormData();
        for (let key in infoOrder.value) {
            form_data.append(key, infoOrder.value[key]);
        }
        await usePost(`save.php`, form_data, answerSave);
    } else {
        if (floorIsEmpty()) {
            window.scrollTo(0, 2000);
        } else {
            window.scrollTo(0, 500);
        }
        infoOrder.value = {};
    }
};

const mtsPixel = (op) => {
    sfmb = document.createElement('iframe');
    sfmb.style = 'position:absolute;top:-2000px;left:0px;frameborder:0px';
    sfmb.src = 'https://sm.rtb.mts.ru/s?p=' + op + '&random=' + Math.random();
    document.body.appendChild(sfmb);
};
</script>

<template>
    <div class="container bg-container">
        <div class="text-4xl my-8 text-black">Оформление заказа</div>
        <Types class="mb-6 lg:mb-8" @update="updateTypes" />
        <div class="flex flex-col lg:grid grid-cols-3 xl:grid-cols-4 gap-6 sm:!pb-10 !pb-20">
            <div class="flex flex-col gap-6 col-start-1 col-end-3 xl:col-start-1 xl:col-end-4">
                <Section title="Данные покупателя">
                    <Profile :count="count" :type="type" @updateValuesProfile="updateValuesProfile" />
                </Section>
                <Section :class="{ hidden: delivery && delivery.delivery }" title="Адрес доставки">
                    <DeliveryMap />
                    <Address
                        :count="count"
                        :type="type"
                        @updateValidAddress="updateValidAddress"
                        @updateAddress="updateAddress"
                        @updateRightAddress="updateRightAddress" />
                </Section>

                <Section title="Способ подъёма">
                    <Lifting @updateFloor="updateFloor"  @nameValue="nameValue"/>
                </Section>

                <Section title="Способ доставки">
                    <Delivery
                        v-if="address"
                        :key="address.address"
                        :address="address.address"
                        :floor="address.floor"
                        @updateDelivery="updateDelivery"
                        @updateRightAddress="updateRightAddress" />
                </Section>
                <Section title="Способ оплаты">
                    <Payments @updatePayments="updatePayments" />
                </Section>
                <div class="hidden flex-col items-center gap-4 lg:!flex lg:!visible">
                    <button
                        @click="count++, mtsPixel('tL0GSZGkyHF7pEk=')"
                        class="j-order-button w-1/3 !py-5 text-sm text-white bg-green rounded-md"
                        :disabled="notRightAddress">
                        Оформить заказ
                    </button>
                    <span class="w-1/3 text-center">
                        Нажимая кнопку "Оформить заказ", я даю
                        <a href="/include/licenses_detail.php" target="_blank" class="text-green cursor-pointer"
                            >согласие на обработку персональных данных</a
                        >, с условиями
                        <a href="/include/offer_detail.php" target="_blank" class="text-green cursor-pointer"
                            >публичной оферты</a
                        >
                        ознакомился и согласен
                    </span>
                </div>
            </div>
            <div class="col-start-3 col-end-4 xl:col-start-4 xl:cols-end-5">
                <FormYourOrder :delivery="delivery" :address="address" @updateAmount="updateAmount" />
            </div>
            <div class="flex flex-col items-center gap-4 lg:hidden">
                <button
                    @click="count++, mtsPixel('tL0GSZGkyHF7pEk=')"
                    class="j-order-button w-full !py-5 text-sm text-white bg-green rounded-md"
                    :disabled="notRightAddress">
                    Оформить заказ
                </button>
                <span class="w-full text-center"
                    >Нажимая кнопку "Оформить заказ", я даю
                    <a href="/include/licenses_detail.php" target="_blank" class="text-green cursor-pointer"
                        >согласие на обработку персональных данных</a
                    >, с условиями
                    <a href="/include/offer_detail.php" target="_blank" class="text-green cursor-pointer"
                        >публичной оферты</a
                    >
                    ознакомился и согласен
                </span>
            </div>
        </div>
    </div>
    <ErrorSave :answerSave="answerSave" />
</template>

<style scoped></style>
