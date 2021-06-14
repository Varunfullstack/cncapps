import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIGoodsIn extends APIMain { 
    getSearchResult(porheadID,supplierID){
    return this.get(`${ApiUrls.GoodsIn}search&porheadID=${porheadID}&supplierID=${supplierID}`)
    }
    getOrderLines(porheadID){
        return this.get(`${ApiUrls.GoodsIn}lines&porheadID=${porheadID}`)
    }
    receive(porheadID,body){
        return this.post(`${ApiUrls.GoodsIn}lines&porheadID=${porheadID}`,body,true);
    }   
}