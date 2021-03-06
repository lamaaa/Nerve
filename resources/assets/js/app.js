
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import VueRouter from 'vue-router';
import WarningConfigs from './pages/WarningConfigs.vue';
import ShSzStockQuotes from './pages/ShSzStockQuotes.vue';
import OwnStocks from './pages/OwnStocks.vue';
import PersonalInformation from './pages/PersonalInformation.vue';
import PasswordChange from './pages/PasswordChange.vue';
import WeChatBind from './pages/WeChatBind.vue';
import WarningRecords from './pages/WarningRecords.vue';


Vue.use(ElementUI);
Vue.use(VueRouter);
Vue.use(WarningConfigs);
Vue.use(ShSzStockQuotes);
Vue.use(OwnStocks);
Vue.use(PersonalInformation);
Vue.use(PasswordChange);
Vue.use(WeChatBind);
Vue.use(WarningConfigs);
Vue.component('nerve', require('./components/Nerve.vue'));


const routes = [
    { path: '/', redirect: 'own-stocks' },
    { path: '/warning-configs', component: WarningConfigs },
    { path: '/sh-sz-stocks', component: ShSzStockQuotes },
    { path: '/own-stocks', component: OwnStocks },
    { path: '/personal-information', component: PersonalInformation },
    { path: '/password-change', component: PasswordChange },
    { path: '/wechat-bind', component: WeChatBind },
    { path: '/warning-records', component: WarningRecords },
];

const router = new VueRouter({
    routes
});

const app = new Vue({
    el: '#app',
    router
});
