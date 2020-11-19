import {sort} from "../utils/utils.js";
import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";


class APICallactType extends APIMain{    
    get(id)
    {
        return fetch(`${ApiUrls.callActType}getById&id=${id}`)
        .then(res => res.json());        
    }
    getAll(){
        return fetch(`${ApiUrls.callActType}getCallActTypes`).then(res => res.json()).then(res=>sort(res,"order"));
    } 
    getAllWithDetails(){
        return fetch(`${ApiUrls.callActType}getAllDetails`).then(res => res.json());
    } 
    updateActivityTypeOrder(callActTypeID,order)
    {
        return this.post(`${ApiUrls.callActType}updateActivityTypeOrder&callActTypeID=${callActTypeID}&order=${order}`,null);
    }
}
export default APICallactType;