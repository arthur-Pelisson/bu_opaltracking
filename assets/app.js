/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import Vue from 'vue'
import Etds from "./vue/ETDs";
import EtdLines from "./vue/ETDLines";
import Store from './store/store';
import { BootstrapVue, IconsPlugin, LayoutPlugin, DropdownPlugin } from "bootstrap-vue";
import '@fortawesome/fontawesome-free/css/all.css'
import '@fortawesome/fontawesome-free/js/all.js'
import './styles/app.scss';
import Filters from "./vue/utils/filters";
import { PopoverPlugin } from 'bootstrap-vue'

new Vue({
    el: "#app",
    store: Store,
    components: { Etds, EtdLines, Filters },
});
Vue.use(PopoverPlugin)
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(LayoutPlugin);
Vue.use(DropdownPlugin);
