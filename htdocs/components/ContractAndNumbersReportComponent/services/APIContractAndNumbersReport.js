import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIContractAndNumbersReport extends APIMain { 
   getReport(){
    return this.get(`${ApiUrls.ContractAndNumbersReport}contracts`)
    } 
}