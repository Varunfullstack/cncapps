import {OutOfDateError} from "./OutOfDateError";

export function updateCustomer(customerID, fieldValueMap, lastUpdatedDateTime) {
    return fetch('?action=updateCustomer',
        {
            method: 'POST',
            body: JSON.stringify({customerID, ...fieldValueMap, lastUpdatedDateTime})
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