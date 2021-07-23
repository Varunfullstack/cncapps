import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";
import { replaceQuotes } from "../../utils/utils";

export default class APIQuoteTemplates extends APIMain { 
   getAllTemplates(){
    return this.get(`${ApiUrls.QuoteTemplates}templates`)
    }
    updateTemplate(body){               
        body.description= replaceQuotes(body.description);                
        if(body.linkedSalesOrderURL)
        delete body.linkedSalesOrderURL;
        return this.post(`${ApiUrls.QuoteTemplates}templates`,body,true);
    }
     
    deleteTemplate(id){        
        return this.delete(`${ApiUrls.QuoteTemplates}templates&&id=${id}`);
    }
   
}