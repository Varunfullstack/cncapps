import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APILeadStatusTypes extends APIMain { 
   getAll(){
    return this.get(`${ApiUrls.LeadStatusTypes}leadStatusTypes`);
    }
}