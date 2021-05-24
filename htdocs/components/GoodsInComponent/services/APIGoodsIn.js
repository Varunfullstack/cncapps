import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIGoodsIn extends APIMain { 
    getSearchResult(){
    return this.get(`${ApiUrls.GoodsIn}search`)
    }
    addEmail(body){
        return this.postJson(`${ApiUrls.GoodsIn}emails`,body);
    }
    updateEmail(body){
        return this.put(`${ApiUrls.GoodsIn}emails`,body);
    }
    deleteEmail(id){        
        return this.delete(`${ApiUrls.GoodsIn}emails&&id=${id}`);
    }
   
}