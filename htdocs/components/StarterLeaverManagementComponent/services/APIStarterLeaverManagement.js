import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIStarterLeaverManagement extends APIMain { 
   getCustomersHaveQuestions(){
    return this.get(`${ApiUrls.StarterLeaverManagement}customers`);
    }
}