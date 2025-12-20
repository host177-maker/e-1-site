import { createApp } from 'vue';
import './assets/index.css';
import App from './App.vue';
import { createPinia } from 'pinia';

import Types from './components/Types.vue';
import Profile from './components/Profile.vue';
import Map from './components/Map.vue';
import DeliveryMap from './components/DeliveryMap.vue';
import Address from './components/Address.vue';
import Section from './components/ui/Section.vue';
import Delivery from './components/Delivery.vue';
import Lifting from './components/Lifting.vue';
import Payments from './components/Payments.vue';
import FormYourOrder from './components/FormYourOrder.vue';
import ErrorSave from './components/ui/ErrorSave.vue';

const pinia = createPinia();

createApp(App)
    .component('Types', Types)
    .component('Profile', Profile)
    .component('DeliveryMap', DeliveryMap)
    .component('Address', Address)
    .component('Section', Section)
    .component('Delivery', Delivery)
    .component('Lifting', Lifting)
    .component('Map', Map)
    .component('Payments', Payments)
    .component('FormYourOrder', FormYourOrder)
    .component('ErrorSave', ErrorSave)
    .use(pinia)
    .mount('#app');
