import {
    ADD_CONTACT_TO_SITE,
    ADD_SITE,
    CHANGE_DELIVER_SITE_NO,
    CHANGE_INVOICE_SITE_NO,
    DELETE_SITE_REQUEST,
    DELETE_SITE_SUCCESS,
    FETCH_ACCOUNT_MANAGERS,
    FETCH_ACCOUNT_MANAGERS_SUCCESS,
    FETCH_CONTACTS_FAILURE,
    FETCH_CONTACTS_REQUEST,
    FETCH_CONTACTS_SUCCESS,
    FETCH_CUSTOMER_REQUEST,
    FETCH_CUSTOMER_SUCCESS,
    FETCH_CUSTOMER_TYPES,
    FETCH_CUSTOMER_TYPES_SUCCESS,
    FETCH_LEAD_STATUSES,
    FETCH_LEAD_STATUSES_SUCCESS,
    FETCH_REVIEW_ENGINEERS,
    FETCH_REVIEW_ENGINEERS_SUCCESS,
    FETCH_SECTORS,
    FETCH_SECTORS_SUCCESS,
    FETCH_SITES_FAILURE,
    FETCH_SITES_REQUEST,
    FETCH_SITES_SUCCESS,
    REQUEST_SAVE_SITE,
    SAVE_CUSTOMER_DATA_SUCCESS,
    SAVE_SITE_SUCCESS,
    TOGGLE_VISIBILITY,
    UPDATE_CUSTOMER_VALUE,
    UPDATE_SITE
} from "./actionTypes";

export const VisibilityFilterOptions = {
    SHOW_ALL: 'SHOW_ALL',
    SHOW_ACTIVE: 'SHOW_ACTIVE'
}

export function addSite(customerId) {
    return {type: ADD_SITE, customerId};
}

export function updateSite(siteNo, data) {
    return {type: UPDATE_SITE, siteNo, data}
}


export function deleteSiteRequest(siteNo) {
    return {type: DELETE_SITE_REQUEST, siteNo}
}

export function deleteSiteSuccess(siteNo) {
    return {type: DELETE_SITE_SUCCESS, siteNo}
}

export function requestContacts(customerId) {
    return {type: FETCH_CONTACTS_REQUEST, customerId}
}

export function receiveContacts(customerId, contacts) {
    return {type: FETCH_CONTACTS_SUCCESS, customerId, contacts}
}

export function failedRequestContacts(response) {
    return {type: FETCH_CONTACTS_FAILURE, response}
}

export function requestSites(customerId) {
    return {type: FETCH_SITES_REQUEST, customerId}
}

export function receiveSites(customerId, sites) {
    return {type: FETCH_SITES_SUCCESS, customerId, sites}
}

export function requestCustomer(customerId) {
    return {type: FETCH_CUSTOMER_REQUEST, customerId}
}

export function receiveCustomer(customer) {
    return {type: FETCH_CUSTOMER_SUCCESS, customer}
}

export function requestCustomerTypes() {
    return {type: FETCH_CUSTOMER_TYPES}
}

export function receiveCustomerTypes(customerTypes) {
    return {type: FETCH_CUSTOMER_TYPES_SUCCESS, customerTypes}
}

export function requestLeadStatuses() {
    return {type: FETCH_LEAD_STATUSES}
}

export function receiveLeadStatuses(leadStatuses) {
    return {type: FETCH_LEAD_STATUSES_SUCCESS, leadStatuses}
}

export function requestSectors() {
    return {type: FETCH_SECTORS}
}

export function receiveSectors(sectors) {
    return {type: FETCH_SECTORS_SUCCESS, sectors}
}

export function requestAccountManagers() {
    return {type: FETCH_ACCOUNT_MANAGERS}
}

export function receiveAccountManagers(accountManagers) {
    return {type: FETCH_ACCOUNT_MANAGERS_SUCCESS, accountManagers}
}

export function requestReviewEngineers() {
    return {type: FETCH_REVIEW_ENGINEERS}
}

export function receiveReviewEngineers(reviewEngineers) {
    return {type: FETCH_REVIEW_ENGINEERS_SUCCESS, reviewEngineers}
}


export function failedRequestSites(response) {
    return {type: FETCH_SITES_FAILURE, response}
}

export function changeDeliverSiteNo(siteNo) {
    return {type: CHANGE_DELIVER_SITE_NO, siteNo}
}

export function changeInvoiceSiteNo(siteNo) {
    return {type: CHANGE_INVOICE_SITE_NO, siteNo}
}

export function addContactToSite(siteNo) {
    return {type: ADD_CONTACT_TO_SITE, siteNo}
}

export function toggleVisibility() {
    return {type: TOGGLE_VISIBILITY}
}

export function savedSiteData(siteNo) {
    return {type: SAVE_SITE_SUCCESS, siteNo}
}

export function savedCustomerData() {
    return {type: SAVE_CUSTOMER_DATA_SUCCESS}
}

export function requestSaveSite(siteNo) {
    return {type: REQUEST_SAVE_SITE, siteNo}
}

export function saveSiteSuccess(siteNo) {
    return {type: SAVE_SITE_SUCCESS, siteNo}
}

export function updateCustomerValue(field, value) {
    return {type: UPDATE_CUSTOMER_VALUE, field, value}
}

export function fetchSites(customerId) {
    return dispatch => {
        dispatch(requestSites(customerId))
        return fetch(`?action=getSites&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveSites(customerId, json.data)))
    }
}

export function fetchCustomer(customerId) {
    return dispatch => {
        dispatch(requestCustomer(customerId))
        return fetch('?action=getCustomer&customerID=' + customerId)
            .then(response => response.json())
            .then(json => dispatch(receiveCustomer(json.data)))
    }
}

export function fetchCustomerTypes() {
    return dispatch => {
        dispatch(requestCustomerTypes());
        return fetch('?action=getCustomerTypes')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveCustomerTypes(json.data.map(x => ({
                    label: x.cty_desc,
                    value: x.cty_ctypeno
                }))))
            })
    }
}

export function fetchLeadStatuses() {
    return dispatch => {
        dispatch(requestLeadStatuses());
        return fetch('?action=getLeadStatuses')
            .then(response => response.json())
            .then(json => {
                dispatch(
                    receiveLeadStatuses(json.data.map(x => ({
                            label: x.name,
                            value: x.id
                        }))
                    )
                )
            })
    }
}

export function fetchSectors() {
    return dispatch => {
        dispatch(requestSectors());
        return fetch('?action=getSectors')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveSectors(json.data.map(x => ({
                    label: x.sec_desc,
                    value: x.sec_sectorno
                }))))
            })
    }
}

export function fetchAccountManagers() {
    return dispatch => {
        dispatch(requestAccountManagers());
        return fetch('?action=getAccountManagers')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveAccountManagers(json.data.map(x => ({
                    label: x.cns_name,
                    value: x.cns_consno,
                }))))
            })
    }
}

export function fetchReviewEngineers() {
    return dispatch => {
        dispatch(requestReviewEngineers());
        return fetch('?action=getReviewEngineers')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveReviewEngineers(json.data.map(x => ({
                    label: x.cns_name,
                    value: x.cns_consno,
                }))))
            })
    }
}


export function fetchContacts(customerId) {
    return dispatch => {
        dispatch(requestContacts(customerId))
        return fetch(`?action=getContacts&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveContacts(customerId, json.data)))
    }
}

export function deleteSite(customerId, siteNo) {
    return dispatch => {
        dispatch(deleteSiteRequest(siteNo))
        return fetch(`?action=deleteSite`,
            {
                method: 'POST',
                body: JSON.stringify({
                    customerId,
                    siteNo
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'ok') {
                    throw new Error(res.message);
                }
                dispatch(deleteSiteSuccess(siteNo));
            })
    }
}

export function saveSite(site) {
    return dispatch => {
        dispatch(requestSaveSite(site.siteNo));
        return fetch('?action=updateSite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ...site,
            })
        })
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'ok') {
                    throw new Error(res.message);
                }
                dispatch(saveSiteSuccess(site.siteNo));
            })
    }
}