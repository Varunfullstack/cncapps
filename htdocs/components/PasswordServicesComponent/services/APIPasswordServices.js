import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIPasswordServices extends APIMain { 
   getAllServices(){
    return this.get(`${ApiUrls.PasswordServices}passwordServices`)
    }
    addType(body){
        return this.postJson(`${ApiUrls.PasswordServices}passwordServices`,body);
    }
    saveService(body){
        return this.put(`${ApiUrls.PasswordServices}passwordServices`,body);
    }
    deleteService(id){        
        return this.delete(`${ApiUrls.PasswordServices}passwordServices&&passwordServiceID=${id}`);
    }
    
}