import {sort} from "../utils/utils";
import APIMain from "./APIMain";
import ApiUrls from "./ApiUrls";


class APIPortalDocuments extends APIMain {
     
    getPortalDocuments(customerID){
        return this.get(`${ApiUrls.PortalCustomerDocument}documents&customerID=${customerID}`);
    }     
    updateDocument(document){
        return this.put(`${ApiUrls.PortalCustomerDocument}documents`, document,true);        
        //return this.uploadFiles(`${ApiUrls.PortalCustomerDocument}documents`,files,"userfile",body,true);
    }
    addDocument(document){
        return this.postJson(`${ApiUrls.PortalCustomerDocument}documents`, document);
    }
    uploadDocument(documentID,file){
        return this.uploadFile(`${ApiUrls.PortalCustomerDocument}uploadDocument&documentID=${documentID}`,file,'userfile');
    }
    deletePortalDocument(documentID){
        return this.delete(`${ApiUrls.PortalCustomerDocument}documents&portalCustomerDocumentID=${documentID}`,true);
    }
}

export default APIPortalDocuments;