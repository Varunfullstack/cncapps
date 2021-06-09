import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIPurchaseInv extends APIMain { 
    getSearchResult(){
    return this.get(`${ApiUrls.PurchaseInv}orders`)
    }
    getOrderLines(porheadID){
        return this.get(`${ApiUrls.PurchaseInv}lines&porheadID=${porheadID}`)
    }
    updateInvoice( body){
        return this.put(`${ApiUrls.PurchaseInv}orders`,body,true);
    }
}