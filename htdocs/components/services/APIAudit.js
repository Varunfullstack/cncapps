import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";
export const AuditActionType={
    UPDATE:"UPDATE",
    NEW:"NEW",
    DELETE:"DELETE"
}
class APIAudit extends APIMain{    
    /**
     * 
     * @param {customerID,problemID,pageID,oldValues,newValues,action} body 
     * @returns 
     */
    addLog(body){
        body.oldValues=body.oldValues!=null?JSON.stringify(body.oldValues):null;
        body.newValues=body.newValues!=null?JSON.stringify(body.newValues):null;
        if(!body.action)
        body.action=AuditActionType.UPDATE;
        return this.postJson(`${ApiUrls.Audit}log`,body);
    }
    getLogs(customerID)
    {
        return this.get(`${ApiUrls.Audit}log&customerID=${customerID}`);
    }
    
}
export default APIAudit;