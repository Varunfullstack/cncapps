import {
    ADD_CONTACT_TO_SITE,
    ADD_SITE,
    CHANGE_DELIVER_SITE_NO,
    CHANGE_INVOICE_SITE_NO,
    DELETE_SITE_REQUEST,
    DELETE_SITE_SUCCESS,
    FETCH_CONTACTS_FAILURE,
    FETCH_CONTACTS_REQUEST,
    FETCH_CONTACTS_SUCCESS,
    FETCH_SITES_FAILURE,
    FETCH_SITES_REQUEST,
    FETCH_SITES_SUCCESS,
    INITIALIZE_CUSTOMER,
    REQUEST_SAVE_SITE,
    SAVE_CUSTOMER_DATA_SUCCESS,
    SAVE_SITE_SUCCESS,
    TOGGLE_VISIBILITY,
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

export function fetchSites(customerId) {
    return dispatch => {
        dispatch(requestSites(customerId))
        return fetch(`?action=getSites&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveSites(customerId, json.data)))
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
        return fetch(`?action=deleteSite&customerId=${customerId}&siteNo=${siteNo}`)
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'ok') {
                    throw new Error(res.message);
                }
                dispatch(deleteSiteSuccess(siteNo));
            })
    }
}

export function saveSite(site, deliverSiteNo, invoiceSiteNo) {
    return dispatch => {
        dispatch(requestSaveSite(site.siteNo));
        return fetch('?action=updateSite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ...site,
                deliverSiteNo,
                invoiceSiteNo,
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

export function initializeCustomer(customerId, invoiceSiteNo, deliverSiteNo) {
    return {type: INITIALIZE_CUSTOMER, customerId, invoiceSiteNo, deliverSiteNo}
}