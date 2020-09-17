class SVCLogService{
    baseURL = "LogServiceRequest.php?action=";
    standardText="StandardText.php?action=";
    searchCustomers(q) {
        return fetch(`${this.baseURL}searchCustomers&q=${q}`).then(res => res.json());
    } 
    getCustomerSR(customerId,contactId){
        return fetch(`${this.baseURL}getCustomerSR&customerId=${customerId}&contactId=${contactId}`)
        .then(res => res.json());

    }
    getCustomerSites(customerId){        
        return fetch(`${this.baseURL}getCustomerSites&customerId=${customerId}`)
        .then(res => res.json());
    }
    getCustomerAssets(customerId){        
        return fetch(`${this.baseURL}getCustomerAssets&customerId=${customerId}`)
        .then(res => res.json());
    }
    getCheckList(){
        return fetch(`${this.standardText}getList`)
        .then(res => res.json());
    }
}
export default SVCLogService;