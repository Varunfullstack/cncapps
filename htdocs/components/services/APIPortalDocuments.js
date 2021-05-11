import {sort} from "../utils/utils";
import APIMain from "./APIMain";
import ApiUrls from "./ApiUrls";


class APIPortalDocuments extends APIMain {
     
    getPortalDocuments(){
        return this.get(`${ApiUrls.PortalDocument}documents`);
    }     
    updateDocument(body,files){
        return this.uploadFiles(`${ApiUrls.PortalDocument}documents`,files,"userfile",body,true);
    }
    deletePortalDocuments(portalDocumentID){
        return this.delete(`${ApiUrls.PortalDocument}documents&portalDocumentID=${portalDocumentID}`);
    }
}

export default APIPortalDocuments;