import { groupBy } from "../utils/utils.js";
import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";

 
class APICallactType extends APIMain{    
    get(id)
    {
        return fetch(`${ApiUrls.callActType}getById&id=${id}`)
        .then(res => res.json());        
    }
    getAll(){
        return fetch(`${ApiUrls.callActType}getCallActTypes`).then(res => res.json());
    } 
}
export default APICallactType;