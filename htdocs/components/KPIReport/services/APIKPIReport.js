import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIKPIReport extends APIMain { 
    getSRFixed(startDate,endDate,customerID){
        return this.get(`${ApiUrls.KPIReport}SRFixed&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }
    getPriorityRaised(startDate,endDate,customerID){
        return this.get(`${ApiUrls.KPIReport}priorityRaised&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }
    getServiceRequestsRaisedByContract(startDate, endDate, customerID){
        return this.get(`${ApiUrls.KPIReport}serviceRequestsRaisedByContract&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }
    getQuotationConversion(startDate,endDate,customerID){
        return this.get(`${ApiUrls.KPIReport}quotationConversion&from=${startDate}&to=${endDate}&customerID=${customerID}`);
    }
}