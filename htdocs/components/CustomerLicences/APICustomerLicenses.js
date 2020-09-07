"use strict";
import APIMain from './../services/APIMain.js?v=1';

class APICustomerLicenses extends APIMain {
    baseURL = "CustomerLicenses.php?action=";

    getTechDataToken() {
        return fetch(`${this.baseURL}techDataToken`);
    }

    getProductList(page = 1) {
        return fetch(`${this.baseURL}getProductList&page=${page}`).then(res => res.json());
    }

    getAllSubscriptions(page = 1) {
        return fetch(`${this.baseURL}getAllSubscriptions&page=${page}`).then(res => res.json());
    }

    getSubscriptionsByEmail(email, page = 1) {
        return fetch(`${this.baseURL}getSubscriptionsByEmail&email=${email}&page=${page}`).then(res => res.json());
    }

    getSubscriptionsByEndCustomerId(endCustomerId, page = 1) {
        return fetch(`${this.baseURL}getSubscriptionsByEndCustomerId&endCustomerId=${endCustomerId}&page=${page}`).then(res => res.json());
    }

    getSubscriptionsByDateRange(from, to, page = 1) {
        return fetch(`${this.baseURL}getSubscriptionsByDateRange&from=${from}&to=${to}`);
    }

    searchTechDataCustomers(data) {
        return fetch(`${this.baseURL}searchTechDataCustomers`, {
            method: 'POST',
            body: JSON.stringify(data)
        }).then(res => res.json());
    }

    addTechDataCustomer(data) {
        return fetch(`${this.baseURL}addTechDataCustomer`, {
            method: 'POST',
            body: JSON.stringify(data)
        }).then(res => res.json());
    }

    updateTechDataCustomer(id, data) {
        return fetch(`${this.baseURL}updateTechDataCustomer&endCustomerId=${id}`, {
            method: 'POST',
            body: JSON.stringify(data)
        }).then(res => res.json());
    }

    getCustomerDetails(endCustomerId) {
        return fetch(`${this.baseURL}getEndCustomerById&endCustomerId=${endCustomerId}`).then(res => res.json());
    }

    //vendors
    getVendors(page = 1) {
        return fetch(`${this.baseURL}getVendors&page=${page}`).then(res => res.json());
    }

    // products
    getProductsByVendor(vendorId, page = 1) {
        return fetch(`${this.baseURL}getProductsByVendor&page=${page}&vendorId=${vendorId}`).then(res => res.json());
    }

    getProductBySKU(body) {
        return fetch(`${this.baseURL}getProductBySKU`, {
            method: 'POST',
            body: JSON.stringify(body)
        }).then(res => res.json());
    }

    getLocalProducts() {
        return fetch(`${this.baseURL}getLocalProducts`).then(res => res.json());

    }

    //get order detials
    // customers
    checkLicenseExistAtCNC(email, sku) {
        return fetch(`${this.baseURL}checkLicenseExistAtCNC&email=${email}&sku=${sku}`, {method: 'GET'}).then(res => res.json());
    }

    getOrderDetials(orderId) {
        return fetch(`${this.baseURL}getOrderDetials&orderId=${orderId}`).then(res => res.json());
    }

    addOrder(body) {
        return fetch(`${this.baseURL}addSubscription`, {
            method: 'POST',
            body: JSON.stringify(body)
        }).then(res => res.json());
    }

    updateOrder(body) {
        return fetch(`${this.baseURL}updateSubscription`, {
            method: 'POST',
            body: JSON.stringify(body)
        }).then(res => res.json());
    }

    updateSubscriptionAddOns(body) {
        return fetch(`${this.baseURL}updateSubscriptionAddOns`, {
            method: 'POST',
            body: JSON.stringify(body)
        }).then(res => res.json());
    }

    purchaseSubscriptionAddOns(body) {
        return fetch(`${this.baseURL}purchaseSubscriptionAddOns`, {
            method: 'POST',
            body: JSON.stringify(body)
        }).then(res => res.json());
    }

    getProductsPrices(products) {
        return fetch(`${this.baseURL}getProductsPrices`, {
            method: 'POST',
            body: JSON.stringify(products)
        }).then(res => res.json());
    }

    // update cnc item
    activeCncItem(customerId, sku) {
        return fetch(`${this.baseURL}activeCncItem&customerId=${customerId}&sku=${sku}`, {method: 'POST'}).then(res => res.json());

    }

    deactiveCncItemBySku(customerId, sku) {

    }

    updateCncItemSeats(customerId, sku) {

    }

    // customers
    getCustomerByEmail(email) {
        const body = {email: email};
        return fetch(`${this.baseURL}getStreamOneCustomerByEmail`, {
            method: 'POST',
            body: JSON.stringify(body)
        }).then(res => res.json());

    }

    getStreamOneCustomersLocal() {
        return fetch(`${this.baseURL}getStreamOneCustomersLocal`)
            .then(res => res.json()).then(res => {
                res.forEach(element => {
                    if (element.MsDomain) {
                        element.MsDomain = JSON.parse(element.MsDomain);
                    }
                    return element;
                });
                return res;
            });
    }
}

export default APICustomerLicenses;
