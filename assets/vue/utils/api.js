import axios from "axios";

// region Files
export function getETDFiles(etdId) {
    return axios.get('/' + etdId + '/files');
}

export function getETDFile(etdId, fileName) {
    return axios.get('/' + etdId + '/file/' + fileName, { responseType: 'blob' });
}

export function deleteETDFile(etdId, fileName) {
    return axios.delete('/' + etdId + '/file/' + fileName);
}

export function importETDFiles(etdId, files) {
    let formData = new FormData();
    files.forEach((file) => {
        formData.append(file.name, file);
    });
    return axios.post('/' + etdId + '/files', formData,{headers: {'Content-Type': 'multipart/form-data'}});
}
// endregion

// region ETD
export function getExportETD(etdId) {
    return axios.get('/' + etdId + '/export', { responseType: 'blob' });
}

export function getLastETDsUpdated() {
    return axios.get('/getlastetdsupdated');
}

export function closeETD(etdId, message) {
    return axios.post('/etd/' + etdId + '/close', message);
}

export function openETD(etdId, message) {
    return axios.post('/etd/' + etdId + '/open', message);
}

// endregion

// region ETDLines
export function updateETDLines(etdId, etdLines, message, counters, partialMessages) {
    const url = '/etd/' + etdId + '/etdlines/validateupdates';
    return axios.post(url, {
        etdLines: etdLines,
        message: message,
        counters: counters,
        partialMessages: partialMessages
    });
}
// endregion

// region Conversation
export function downloadConversation(etdId, etdLineId) {
    let url = '/etd/' + etdId + '/conversation/download';
    if (etdLineId !== null) {
        url = '/etd/' + etdId + '/etdlines/' + etdLineId + '/conversation/download';
    }
    return axios.get(url, { responseType: 'blob' });
}

export function getConversation(etdId, etdLineId) {
    let url = '/etd/' + etdId + '/conversation';
    if (etdLineId !== null) {
        url = '/etd/' + etdId + '/etdlines/' + etdLineId + '/conversation';
    }
    return axios.get(url);
}

export function addConversationMessage(etdId, etdLineId, message) {
    let url = '/etd/' + etdId + '/conversation/addmessage';
    if (etdLineId !== null) {
        url = '/etd/' + etdId + '/etdlines/' + etdLineId + '/conversation/addmessage';
    }
    return axios.post(url, message);
}
// endregion
