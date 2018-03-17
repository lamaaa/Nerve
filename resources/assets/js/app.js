
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
import WarningConfig from './pages/WarningConfig.vue';
import ShSzStockQuotes from './pages/ShSzStockQuotes.vue';

Vue.use(ElementUI);
Vue.use(VueRouter);
Vue.use(WarningConfig);
Vue.use(ShSzStockQuotes);
Vue.component('nerve', require('./components/Nerve.vue'));


const routes = [
    { path: '/warning-config', component: WarningConfig },
    { path: '/sh-sz-stock', component: ShSzStockQuotes}
]

const router = new VueRouter({
    routes
})
const app = new Vue({
    el: '#app',
    router
});
