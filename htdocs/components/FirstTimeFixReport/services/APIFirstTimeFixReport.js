import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIFirstTimeFixReport extends APIMain {
//    search(payload){        
//        return this.post(`${ApiUrls.FirstTimeFixReport}fetchData&`,payload).then(res => res.json());;
//    }
   search(startDate,endDate,customerID,engineerID){
    return this.get(`${ApiUrls.FirstTimeFixReport}fetchData&startDate=${startDate}&endDate=${endDate}&engineerID=${engineerID}&customerID=${customerID}`);
    }
}