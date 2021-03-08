import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIItemTypes extends APIMain { 
   getAllTypes(){
    return this.get(`${ApiUrls.ItemType}itemTypes`)
    }
    addType(body){
        return this.postJson(`${ApiUrls.ItemType}itemTypes`,body);
    }
    updateType(body){
        return this.put(`${ApiUrls.ItemType}itemTypes`,body);
    }
    deleteType(id){        
        return this.delete(`${ApiUrls.ItemType}itemTypes&&id=${id}`);
    }
    getStockCat()
    {
        return this.get(`${ApiUrls.ItemType}getStockCat`);
    }
}