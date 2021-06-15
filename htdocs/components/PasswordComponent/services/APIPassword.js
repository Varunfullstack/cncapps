import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIPassword extends APIMain { 
   getAllPasswords(customerId,showArchived,showHigherLevel){
    return this.get(`${ApiUrls.Password}passwords&customerId=${customerId}&showArchived=${showArchived}&showHigherLevel=${showHigherLevel}`)
    }
    getServices(customerId,passwordId){
        return this.get(`${ApiUrls.Password}services&customerId=${customerId}&passwordId=${passwordId==null?'':passwordId}`)

    }
    addPassword(body){
        return this.postJson(`${ApiUrls.Password}passwords`,body);
    }
    updatePassword(body){
        return this.postJson(`${ApiUrls.Password}passwords`,body);
    }
    archivePassword(id){        
        return this.delete(`${ApiUrls.Password}passwords&&passwordID=${id}`);
    }
}