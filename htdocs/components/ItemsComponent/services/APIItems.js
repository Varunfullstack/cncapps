import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIItems extends APIMain { 
   getItems(limit=50,page=1,orderBy='description',orderDir='asc',q=''){
    return this.get(`${ApiUrls.Item}items&limit=${limit}&page=${page}&orderBy=${orderBy}&orderDir=${orderDir}&q=${q}`);
    }
    getWarranty(){
        return this.get(`${ApiUrls.Item}warranty`);
    }
    getRenewalTypes(){
        return this.get(`${ApiUrls.Item}renewalTypes`);
    }
    getItemBillingCategory(){
        return this.get(`${ApiUrls.Item}itemBillingCategory`);
    }
    updateItem(item){
        return this.put(`${ApiUrls.Item}items`,item);
    }
    addItem(item){
        return this.post(`${ApiUrls.Item}items`,item).then((res) => res.json());
    }
    addChildItem(itemId,childItemId){
        return this.post(`${ApiUrls.Item}ADD_CHILD_ITEM`,{childItemId,itemId}).then((res) => res.json());
    }
    getChildItems(itemId){
        return this.get(`${ApiUrls.Item}GET_CHILD_ITEMS&itemId=${itemId}`);
        
    }
    updateChildItems(itemId,items){
        return this.post(`${ApiUrls.Item}childItems&itemId=${itemId}`,items);
        
    }
    updateItemQty(itemId,value){
        return this.post(`${ApiUrls.Item}salesStockQty&id=${itemId}&value=${value}`);        
    }
}