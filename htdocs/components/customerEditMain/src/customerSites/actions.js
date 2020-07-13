import {
    ADD_CONTACT_TO_SITE,
    ADD_SITE,
    CHANGE_DEFAULT_DELIVERY_CONTACT,
    CHANGE_DEFAULT_INVOICE_CONTACT,
    FETCH_CONTACTS_FAILURE,
    FETCH_CONTACTS_REQUEST,
    FETCH_CONTACTS_SUCCESS,
    FETCH_SITES_FAILURE,
    FETCH_SITES_REQUEST,
    INITIALIZE_CUSTOMER,
    SET_DEFAULT_DELIVERY_SITE,
    SET_DEFAULT_INVOICE_SITE,
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

export function setDefaultInvoiceSite(siteNo) {
    return {type: SET_DEFAULT_INVOICE_SITE, siteNo};
}

export function setDefaultDeliverySite(siteNo) {
    return {type: SET_DEFAULT_DELIVERY_SITE, siteNo};
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
    return {type: FETCH_SITES_REQUEST}
}

export function receiveSites(customerId, sites) {
    return {type: FETCH_CONTACTS_SUCCESS, customerId, sites}
}

export function failedRequestSites(response) {
    return {type: FETCH_SITES_FAILURE, response}
}

export function changeDefaultInvoiceContact(contactId) {
    return {type: CHANGE_DEFAULT_INVOICE_CONTACT, contactId}
}

export function changeDefaultDeliveryContact(contactId) {
    return {type: CHANGE_DEFAULT_DELIVERY_CONTACT, contactId}
}

export function addContactToSite(siteNo) {
    return {type: ADD_CONTACT_TO_SITE, siteNo}
}

export function fetchSites(customerId) {
    return dispatch => {
        dispatch(requestSites(customerId))
        return fetch(`?action=getSites&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveSites(customerId, json)))
    }
}

export function fetchContacts(customerId) {
    return dispatch => {
        dispatch(requestContacts(customerId))
        return fetch(`?action=getSites&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveContacts(customerId, json)))
    }
}


export function initializeCustomer(customerId, defaultInvoice, defaultDelivery) {
    return {type: INITIALIZE_CUSTOMER, customerId, defaultInvoice, defaultDelivery}
}