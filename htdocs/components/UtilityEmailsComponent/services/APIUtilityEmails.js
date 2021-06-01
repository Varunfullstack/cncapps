import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIUtilityEmails extends APIMain { 
    getAllEmails(){
    return this.get(`${ApiUrls.UtilityEmails}emails`)
    }
    addEmail(body){
        return this.postJson(`${ApiUrls.UtilityEmails}emails`,body);
    }
    updateEmail(body){
        return this.put(`${ApiUrls.UtilityEmails}emails`,body);
    }
    deleteEmail(id){        
        return this.delete(`${ApiUrls.UtilityEmails}emails&&id=${id}`);
    }
   
}