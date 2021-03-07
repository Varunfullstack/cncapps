import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APILeadStatusTypes extends APIMain { 
   getAllTypes(){
    return this.get(`${ApiUrls.LeadStatusTypes}leadStatusTypes`)
    }
    addType(body){
        return this.postJson(`${ApiUrls.LeadStatusTypes}leadStatusTypes`,body);
    }
    updateType(body){
        return this.put(`${ApiUrls.LeadStatusTypes}leadStatusTypes`,body);
    }
    deleteType(id){        
        return this.delete(`${ApiUrls.LeadStatusTypes}leadStatusTypes&&id=${id}`);
    }
}