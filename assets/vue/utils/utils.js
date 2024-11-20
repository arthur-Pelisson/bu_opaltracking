import moment from "moment";
import {DateFormat} from "./constants";


export function getQuantityFormatted(qty) {
    return parseInt(qty).toString();
}

export function getQuantity(qty) {
    return parseInt(qty);
}

export function getDateFormatted(date) {
    if(date === null || date === undefined || date === '') {
        return null;
    }
    return moment(date).format(DateFormat);
}

export function getDateTimeFormatted(date) {
    if(date === null) {
        return null;
    }
    return moment(date).format(DateFormat + ' H:mm:ss');
}

export function downloadFile(fileName, content) {
    const url = window.URL.createObjectURL(new Blob([content]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
}

