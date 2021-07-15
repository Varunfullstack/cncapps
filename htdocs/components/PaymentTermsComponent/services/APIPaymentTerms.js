import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIPaymentTerms extends APIMain { 
   getAll(){
    return this.get(`${ApiUrls.PaymentTerms}json`)
    }
    update(body){    
        return this.put(`${ApiUrls.PaymentTerms}json`,body,true);
    }
    add(body){    
        return this.post(`${ApiUrls.PaymentTerms}json`,body,true);
    }
    deleteItem(id){        
        return this.delete(`${ApiUrls.PaymentTerms}json&&id=${id}`);
    }
   
}