import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIItemBillingCategory extends APIMain { 
   getAll(){
    return this.get(`${ApiUrls.ItemBillingCategory}json`)
    }
    update(body){    
        return this.put(`${ApiUrls.ItemBillingCategory}json`,body,true);
    }
    add(body){    
        return this.post(`${ApiUrls.ItemBillingCategory}json`,body,true);
    }
    deleteItem(id){        
        return this.delete(`${ApiUrls.ItemBillingCategory}json&&id=${id}`);
    }
   
}