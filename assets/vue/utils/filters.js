import Vue from 'vue';
import moment from "moment";
import * as Utils from "./utils";

Vue.prototype.moment = moment;

Vue.filter('formatDate', (date) => {
    return Utils.getDateFormatted(date);
});

Vue.filter('formatDateTime', (date) => {
    return Utils.getDateTimeFormatted(date);
});

Vue.filter('fromFormatDate', (date) => {
    return moment(date).fromNow();
});

Vue.filter('formatQuantity', (qty) => {
    if(qty !== '' && qty !== undefined && qty !== null) {
        return parseInt(qty);
    }
    return null;
});

export default class Filters {
}