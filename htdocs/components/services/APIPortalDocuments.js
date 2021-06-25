import {sort} from "../utils/utils";
import APIMain from "./APIMain";
import ApiUrls from "./ApiUrls";


class APIPortalDocuments extends APIMain {
     
    getPortalDocuments(){
        return this.get(`${ApiUrls.PortalCustomerDocument}documents`);
    }     
    updateDocument(document){
        return this.put(`${ApiUrls.PortalCustomerDocument}documents`, document);
        //return this.uploadFiles(`${ApiUrls.PortalCustomerDocument}documents`,files,"userfile",body,true);
    }
    deletePortalDocument(portalDocumentID){
        return this.delete('PortalCustomerDocument.php?action=delete&portalCustomerDocumentID=' + portalDocumentID,true);
    }
}

export default APIPortalDocuments;