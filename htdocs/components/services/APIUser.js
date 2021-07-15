import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";


class APIUser extends APIMain{    
    getAllUsers() {
        return this.get(`${ApiUrls.User}all`);
    } 
    getActiveUsers() {
        return this.get(`${ApiUrls.User}active`);
    } 
    getUsersByTeamLevel(teamLevel){
        return fetch(`${ApiUrls.User}getUsersByTeamLevel&teamLevel=${teamLevel}`).then(res => res.json());

    }
    saveSettings(consID,type,settings){
        const body={
            consID,
            settings:JSON.stringify(settings),
            type
        }
        return this.post(`${ApiUrls.User}settings`,body);
    }
    getSettings(page){        
        return this.get(`${ApiUrls.User}settings&type=${page}`);
    }
    getMyFeedback(from=null,to=null){     
        let url=`${ApiUrls.User}myFeedback`;   
        if(from)
        url +='&from='+from;

        if(to)
        url +='&to='+to;
        return this.get(url);
    }
}
export default APIUser;