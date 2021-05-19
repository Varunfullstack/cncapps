import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIRootCause extends APIMain { 
    getAllTypes(){
    return this.get(`${ApiUrls.RootCause}rootCause`)
    }
    addType(body){
        return this.postJson(`${ApiUrls.RootCause}rootCause`,body);
    }
    updateType(body){
        return this.put(`${ApiUrls.RootCause}rootCause`,body);
    }
    deleteType(id){        
        return this.delete(`${ApiUrls.RootCause}rootCause&&rootCauseID=${id}`);
    }
   
}