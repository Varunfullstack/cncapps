import {sort} from "../utils/utils";
import APIMain from "./APIMain";
import ApiUrls from "./ApiUrls";


class APICustomers extends APIMain {
    searchCustomers(q) {
        return fetch(`${ApiUrls.Customer}searchCustomers&q=${q}`).then(res => res.json());
    }
    
    getCustomerData(customerId)
    {
        return this.get(`${ApiUrls.Customer}getCustomer&&customerID=${customerId}`).then(res=>res.data);
    }

    getCustomerSR(customerId) {
        return fetch(`${ApiUrls.Customer}getCustomerSR&customerId=${customerId}`)
            .then(res => res.json());

    }

    getCustomerSites(customerId) {
        return fetch(`${ApiUrls.Customer}getCustomerSites&customerId=${customerId}`)
            .then(res => res.json());
    }

    getCustomerAssets(customerId) {
        return fetch(`${ApiUrls.Customer}getCustomerAssets&customerId=${customerId}`)
            .then(res => res.json());
    }

    getCustomerContacts(customerId) {
        return fetch(`${ApiUrls.Customer}contacts&customerID=${customerId}`)
            .then(res => res.json());
    }

    getCustomerProjects(customerId) {
        return fetch(`${ApiUrls.Customer}projects&customerID=${customerId}`)
            .then(res => res.json())
            ;
    }

    getCustomerContracts(customerId, contractCustomerItemID, linkedToSalesOrder) {
        return fetch(`${ApiUrls.Customer}contracts&customerId=${customerId}&contractCustomerItemID=${contractCustomerItemID}&linkedToSalesOrder=${linkedToSalesOrder}`).then(res => res.json());
    }

    getCustomerHaveOpenSR() {
        return fetch(`${ApiUrls.Customer}getCustomersHaveOpenSR`).then(res => res.json()).then(customers => sort(customers, "name"));
    }

    getCustomerTypes() {
        return this.get(`${ApiUrls.Customer}getCustomerTypes`)
        .then(res=>res.data.map(item=>{
            return {id:item.cty_ctypeno,name:item.cty_desc}
        }));
    }

    getCustomerSectors() {
        return this.get(`${ApiUrls.Customer}getSectors`)
        .then(res=>res.data.map(item=>{
            return {id:item.sec_sectorno,name:item.sec_desc}
        }));
    }

    updateCustomer(data)
    {
        return this.post(`${ApiUrls.Customer}updateCustomer`,data,true);
    }

    getPortalCustomerDocuments(customerId) {
        return fetch(`${ApiUrls.Customer}getPortalCustomerDocuments&customerID=${customerId}`)
        .then(res => res.json())
        ;
    }

    getCustomerSites(customerId) {
        return fetch(`${ApiUrls.Customer}getCustomerSites&customerId=${customerId}`)
        .then(res => res.json())
        ;
    }

    addCustomerSite(data)
    {
        return this.post(`${ApiUrls.Customer}addSite`,data,true);
    }

    updateCustomerSite(data)
    {
        return this.post(`${ApiUrls.Customer}updateSite`,data,true);
    }

    deleteCustomerSite(data)
    {
        return this.post(`${ApiUrls.Customer}deleteSite`,data,true);
    }

    getCustomerContact(customerId) 
    {
        return fetch(`${ApiUrls.Customer}contacts&customerID=${customerId}`)
        .then(res => res.json())
        ;
    }

    addCustomerContact(data) 
    {
        return this.post(`${ApiUrls.Customer}addContact`,data,true);
    }

    updateCustomerContact(data) 
    {
        return this.post(`${ApiUrls.Customer}updateContact`,data,true);
    }

    getCustomerOrders(customerId) 
    {
        return fetch(`${ApiUrls.Customer}getCustomerOrders&customerId=${customerId}`)
        .then(res => res.json())
        ;
    }
}

export default APICustomers;