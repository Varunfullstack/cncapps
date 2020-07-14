import {
    ADD_CONTACT_TO_SITE,
    ADD_SITE,
    CHANGE_DELIVER_SITE_NO,
    CHANGE_INVOICE_SITE_NO,
    FETCH_CONTACTS_FAILURE,
    FETCH_CONTACTS_REQUEST,
    FETCH_CONTACTS_SUCCESS,
    FETCH_SITES_FAILURE,
    FETCH_SITES_REQUEST,
    FETCH_SITES_SUCCESS,
    INITIALIZE_CUSTOMER,
    SET_VISIBILITY_FILTER
} from "./actionTypes";

export const VisibilityFilterOptions = {
    SHOW_ALL: 'SHOW_ALL',
    SHOW_ACTIVE: 'SHOW_ACTIVE'
}

export function addSite(customerId) {
    return {type: ADD_SITE, customerId};
}

export function setVisibilityFilter(filter) {
    return {type: SET_VISIBILITY_FILTER, filter};
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


export function initializeCustomer(customerId, invoiceSiteNo, deliverSiteNo) {
    return {type: INITIALIZE_CUSTOMER, customerId, invoiceSiteNo, deliverSiteNo}
}