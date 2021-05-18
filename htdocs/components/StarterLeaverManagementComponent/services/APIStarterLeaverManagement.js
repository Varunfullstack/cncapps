import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIStarterLeaverManagement extends APIMain { 
   getCustomersHaveQuestions(){
    return this.get(`${ApiUrls.StarterLeaverManagement}customers`);
    }
    getCustomerQuestions(customerID){
        return this.get(`${ApiUrls.StarterLeaverManagement}customerQuestions&&customerID=${customerID}`);
    }
    updateQuestion(question){
        return this.put(`${ApiUrls.StarterLeaverManagement}customerQuestions`,question,true);
    }
    addQuestion(question){
        return this.post(`${ApiUrls.StarterLeaverManagement}customerQuestions`,question,true);
    }
    deleteQuestion(question){
        return this.delete(`${ApiUrls.StarterLeaverManagement}customerQuestions&questionID=${question.questionID}`,true);
    }
}