export const ADD_SITE = 'ADD_SITE';
export const SET_VISIBILITY_FILTER = 'SET_VISIBILITY_FILTER';
export const SET_DEFAULT_INVOICE_SITE = 'SET_DEFAULT_INVOICE_SITE';
export const SET_DEFAULT_DELIVERY_SITE = 'SET_DEFAULT_DELIVERY_SITE';
export const FETCH_CONTACTS_REQUEST = 'FETCH_CONTACTS_REQUEST';
export const FETCH_CONTACTS_FAILURE = 'FETCH_CONTACTS_FAILURE';
export const FETCH_CONTACTS_SUCCESS = 'FETCH_CONTACTS_SUCCESS';

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