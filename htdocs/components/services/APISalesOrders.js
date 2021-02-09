import APIMain from "./APIMain";
import ApiUrls from "./ApiUrls";
class APISalesOrders extends APIMain {
    getCustomerInitialSalesOrders(customerID) {
        return fetch(`${ApiUrls.SalesOrder}customerInitialSalesOrders&customerID=${customerID}`).then(res => res.json());
    }
 
}

export default APISalesOrders;