import {OutOfDateError} from "./OutOfDateError";

export function updateSite(customerId, siteNo, fieldValueMap, lastUpdatedDateTime) {
    return updateInServer('?action=updateSite', {customerId, siteNo, fieldValueMap, lastUpdatedDateTime});
}

export function updateCustomer(customerID, fieldValueMap, lastUpdatedDateTime) {
    return updateInServer('?action=updateCustomer', {customerID, ...fieldValueMap, lastUpdatedDateTime});
}

export function updateNote(noteId, fieldValueMap, lastUpdatedDateTime) {
    return updateInServer('?action=updateCustomerNote', {noteId, ...fieldValueMap, lastUpdatedDateTime});
}

function updateInServer(url, values) {
    return fetch(url,
        {
            method: 'POST',
            body: JSON.stringify(values)
        }
    )
        .then(res => res.json())
        .then(json => {
            if (json.status !== 'ok') {
                if (json.extraData && +json.extraData.errorCode === 1002) {
                    throw new OutOfDateError(json.message, json.lastUpdatedDateTime);
                }

                throw new Error(json.message);
            }
            return json.lastUpdatedDateTime;
        })
}