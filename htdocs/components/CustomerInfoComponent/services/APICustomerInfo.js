import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APICustomerInfo extends APIMain {
  get24HourSupportCustomers() {
    return fetch(`${ApiUrls.CustomerInfo}supportCustomers`).then((res) => res.json());
  }  
  getCallOutYears(){
    return fetch(`${ApiUrls.CustomerInfo}callOutYears`).then((res) => res.json());
  }
  getOutOfHours(from,to){
    return fetch(`${ApiUrls.CustomerInfo}outOfHours&from=${from}&to=${to}`).then((res) => res.json());
  }
  getSpecialAttention(){
    return fetch(`${ApiUrls.CustomerInfo}specialAttention`).then((res) => res.json());
  }
  searchContactAudit(filter){
    return this.post(`${ApiUrls.CustomerInfo}searchContactAudit`,filter).then((res) => res.json());
  }
}
