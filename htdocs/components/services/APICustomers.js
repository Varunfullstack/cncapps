import {sort} from "../utils/utils";
import APIMain from "./APIMain";
import ApiUrls from "./ApiUrls";


class APICustomers extends APIMain {
    searchCustomers(q) {
        return fetch(`${ApiUrls.Customer}searchCustomers&q=${q}`).then(res => res.json());
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
}

export default APICustomers;