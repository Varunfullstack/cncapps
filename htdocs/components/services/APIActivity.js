import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";

class APIActivity extends APIMain{
    getCallActivityDetails(callActivityID,filters) {
        return fetch(`${ApiUrls.SRActivity}getCallActivity&callActivityID=${callActivityID}&includeTravel=${filters.showTravel}&includeOperationalTasks=${filters.showOperationalTasks}&includeServerGuardUpdates=${filters.showServerGaurdUpdates}`).then(res => res.json());
    } 
    unlinkSalesOrder(linkedOrdheadId)
    {
        return fetch(`Activity.php?action=unlinkSalesOrder&activityId=${linkedOrdheadId}`);
    }
    setActivityCritical(callActivityID)
    {        
        return fetch(`Activity.php?action=toggleCriticalFlag&callActivityID=${callActivityID}`);
    }
    setActivityMonitoring(callActivityID)
    {  
        return fetch(`Activity.php?action=toggleMonitoringFlag&callActivityID=${callActivityID}`);
    }
    deleteActivity(callActivityID){
        return fetch(`Activity.php?action=deleteCallActivity&callActivityID=${callActivityID}`);

    }
    unHideSrActivity(callActivityID){
        return fetch(`Activity.php?action=unhideSR&callActivityID=${callActivityID}`);
    }
    sendActivityVisitEmail(callActivityID){
        return fetch(`Activity.php?action=sendVisitEmail&callActivityID=${callActivityID}`);
    }
    deleteDocument(callActivityID,id){
        return fetch(`Activity.php?action=deleteFile&callActivityID=${callActivityID}&callDocumentID=${id}`);

    }
    sendPartsUsed(data)
    {
        return this.post(`${ApiUrls.SRActivity}messageToSales`,data).then(res => res.json());

    }
    getSalesRequestOptions=()=>{
        return fetch(`StandardText.php?action=getSalesRequestOptions`).then(res => res.json());
    }
    sendSalesRequest(customerId,problemID,data)
    {
        let url='Activity.php?action=sendSalesRequest&problemID=' + problemID;
        if(customerId)
            url = 'CreateSalesRequest.php?action=createSalesRequest&customerID=' + customerId;
        return this.postFormData(url,data).then(res => res.json());
    }
    getChangeRequestOptions=()=>{
        return fetch(`StandardText.php?action=getChangeRequestOptions`).then(res => res.json());
    }
    sendChangeRequest(problemID,data)
    {
        let url='Activity.php?action=sendChangeRequest&problemID=' + problemID;        
        return this.postFormData(url,data).then(res => res.json());
    }
    updateActivity(activity)
    {
        let url=`${ApiUrls.SRActivity}updateActivity`;        
        return this.post(url,activity).then(res => res.json());
    }
         
    getPriorities()
    {
        return fetch(`${ApiUrls.SRActivity}getPriorities`).then(res => res.json());
    }
    activityRequestAdditionalTime(callActivityID,reason)
    {
        let url=`Activity.php?action=requestAdditionalTime&callActivityID=${callActivityID}&reason=${reason}`;        
        return fetch(url) ;
    }
    getAllUsers()
    {
        return fetch(`${ApiUrls.SRActivity}getAllUsers`).then(res => res.json());
    }
    getCustomerContracts(customerId,contractCustomerItemId,linkedToSalesOrder)
    {
        return fetch(`${ApiUrls.SRActivity}getCustomerContracts&customerId=${customerId}&contractCustomerItemId=${contractCustomerItemId}&linkedToSalesOrder=${linkedToSalesOrder}`).then(res => res.json());
    }
    getRootCauses()
    {
        return fetch(`${ApiUrls.SRActivity}getRootCauses`).then(res => res.json());
    }
    createProblem(data)
    {
        return this.post(`${ApiUrls.SRActivity}createProblem`,data).then(res => res.json());
    }
}
export default APIActivity;