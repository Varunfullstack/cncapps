import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";

 
class APIUser extends APIMain{    
    getAllUsers() {
        return fetch(`${ApiUrls.User}all`).then(res => res.json());
    } 
    getActiveUsers() {
        return fetch(`${ApiUrls.User}active`).then(res => res.json());
    } 
     
}
export default APIUser;