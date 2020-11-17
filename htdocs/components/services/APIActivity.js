import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";

class APIActivity extends APIMain {
    getCallActivityDetails(callActivityID, filters) {
        return fetch(`${ApiUrls.SRActivity}getCallActivity&callActivityID=${callActivityID}&includeTravel=${filters.showTravel}&includeOperationalTasks=${filters.showOperationalTasks}&includeServerGuardUpdates=${filters.showServerGaurdUpdates}`).then(res => res.json());
    }

    unlinkSalesOrder(serviceRequestId) {
        return fetch(`Activity.php?action=unlinkSalesOrder&serviceRequestId=${serviceRequestId}`);
    }

    setActivityCritical(callActivityID) {
        return fetch(`Activity.php?action=toggleCriticalFlag&callActivityID=${callActivityID}`);
    }

    setActivityMonitoring(callActivityID) {
        return fetch(`Activity.php?action=toggleMonitoringFlag&callActivityID=${callActivityID}`);
    }

    deleteActivity(callActivityID) {
        return fetch(`Activity.php?action=deleteCallActivity&callActivityID=${callActivityID}`);

    }

    unHideSrActivity(callActivityID) {
        return fetch(`Activity.php?action=unhideSR&callActivityID=${callActivityID}`);
    }

    sendActivityVisitEmail(callActivityID) {
        return fetch(`Activity.php?action=sendVisitEmail&callActivityID=${callActivityID}`);
    }

    deleteDocument(callActivityID, id) {
        return fetch(`Activity.php?action=deleteFile&callActivityID=${callActivityID}&callDocumentID=${id}`);

    }

    sendPartsUsed(data) {
        return this.post(`${ApiUrls.SRActivity}messageToSales`, data).then(res => res.json());

    }

    getSalesRequestOptions = () => {
        return fetch(`StandardText.php?action=getSalesRequestOptions`).then(res => res.json());
    }

    sendSalesRequest(customerId, problemID, data) {
        let url = 'Activity.php?action=sendSalesRequest&problemID=' + problemID;
        if (customerId)
            url = 'CreateSalesRequest.php?action=createSalesRequest&customerID=' + customerId;
        return this.postFormData(url, data).then(res => res.json());
    }

    getChangeRequestOptions = () => {
        return fetch(`StandardText.php?action=getChangeRequestOptions`).then(res => res.json());
    }

    sendChangeRequest(problemID, data) {
        let url = 'Activity.php?action=sendChangeRequest&problemID=' + problemID;
        return this.postFormData(url, data).then(res => res.json());
    }

    updateActivity(activity) {
        let url = `${ApiUrls.SRActivity}updateActivity`;
        return this.post(url, activity).then(res => res.json());
    }

    getPriorities() {
        return fetch(`${ApiUrls.SRActivity}getPriorities`).then(res => res.json());
    }

    activityRequestAdditionalTime(callActivityID, reason) {
        let url = `Activity.php?action=requestAdditionalTime&callActivityID=${callActivityID}&reason=${reason}`;
        return fetch(url);
    }

    getAllUsers() {
        return fetch(`${ApiUrls.SRActivity}getAllUsers`).then(res => res.json());
    }

    getCustomerContracts(customerId, contractCustomerItemID, linkedToSalesOrder) {
        return fetch(`${ApiUrls.SRActivity}getCustomerContracts&customerId=${customerId}&contractCustomerItemID=${contractCustomerItemID}&linkedToSalesOrder=${linkedToSalesOrder}`).then(res => res.json());
    }

    getRootCauses() {
        return fetch(`${ApiUrls.SRActivity}getRootCauses`).then(res => res.json());
    }

    createProblem(data) {
        return this.post(`${ApiUrls.SRActivity}createProblem`, data).then(res => res.json());
    }

    getCallActivityTypeId(callActivityId) {
        return fetch(`${ApiUrls.SRActivity}getCallActivityType&callActivityID=${callActivityId}`).then(res => res.json());
    }

    getCustomerRaisedRequest(Id) {
        return fetch(`${ApiUrls.SRActivity}getCustomerRaisedRequest&customerproblemno=${Id}`).then(res => res.json());
    }

    getCallActivityBasicInfo(Id) {
        return fetch(`${ApiUrls.SRActivity}getCallActivityBasicInfo&callActivityID=${Id}`).then(res => res.json());
    }

    getDocuments(callActivityID, problemID) {
        return fetch(`${ApiUrls.SRActivity}getDocuments&callActivityID=${callActivityID}&problemID=${problemID}`).then(res => res.json());
    }

    saveFixedInformation(body) {
        return this.post(`${ApiUrls.SRActivity}saveFixedInformation`, body).then(res => res.json());
    }

    getInitialActivity(problemID) {
        return fetch(`${ApiUrls.SRActivity}getInitialActivity&problemID=${problemID}`)
            .then(res => res.json());
    }

    saveManagementReviewDetails(body) {
        return this.post(`${ApiUrls.SRActivity}saveManagementReviewDetails`, body).then(res => res.json());
    }

    changeProblemPriority(body) {
        return this.post(`${ApiUrls.SRActivity}changeProblemPriority`, body).then(res => res.json());
    }
}

export default APIActivity;