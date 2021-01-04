import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIKPIReport extends APIMain { 
    getSRFixed(startDate,endDate){
        return this.get(`${ApiUrls.KPIReport}SRFixed&from=${startDate}&to=${endDate}`);
    }
}