import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APICustomerFeedback extends APIMain {
    getCustomerFeedback(from=null,to=null,customerID=null,engineerID=null) {
        return this.get(`${ApiUrls.CustomerFeedback}search&from=${from}&to=${to}&customerID=${customerID}&engineerID=${engineerID}`);
    }
}