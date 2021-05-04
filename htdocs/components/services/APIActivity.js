import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";

import {getBase64} from "../utils/utils";

class APIActivity extends APIMain {
    getCallActivityDetails(callActivityID, filters) {
        return fetch(`${ApiUrls.SRActivity}getCallActivity&callActivityID=${callActivityID}&includeTravel=${filters.showTravel}&includeOperationalTasks=${filters.showOperationalTasks}&includeServerGuardUpdates=${filters.showServerGuardUpdates}`).then(res => res.json());
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

    setProblemHoldForQA(problemID) {
        return fetch(`SRActivity.php?action=toggleHoldForQAFlag&problemID=${problemID}`);
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
        let url = `Activity.php?action=requestAdditionalTime&callActivityID=${callActivityID}&reason=${encodeURIComponent(reason)}`;
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

    getCustomerContactActivityDurationThresholdValue() {
        return fetch(`${ApiUrls.SRActivity}getCustomerContactActivityDurationThresholdValue`).then(res => res.json()).then(res => res.data);
    }

    getRemoteSupportActivityDurationThresholdValue() {
        return fetch(`${ApiUrls.SRActivity}getRemoteSupportActivityDurationThresholdValue`).then(res => res.json()).then(res => res.data);
    }

    getTimeBreakdown(problemId) {
        return this.get(`${ApiUrls.SRActivity}usedBudgetData&problemID=${problemId}`)
    }

    getLastActivityInServiceRequest(serviceRequestId) {
        return fetch(`${ApiUrls.SRActivity}getLastActivityInServiceRequest&serviceRequestId=${serviceRequestId}`).then(res => res.json());
    }

    getNotAttemptFirstTimeFix(startDate, endDate, customerID, enginnerID) {
        return this.get(`${ApiUrls.SRActivity}getNotAttemptFirstTimeFix&startDate=${startDate}&endDate=${endDate}&userID=${enginnerID}&customerID=${customerID}`);
    }

    getDocumentsForServiceRequest(serviceRequestId) {
        return fetch(`${ApiUrls.SRActivity}getDocumentsForServiceRequest&serviceRequestId=${serviceRequestId}`)
            .then(res => res.json())
            .then(res => res.data);
    }


    async addServiceRequestFiles(serviceRequestId, uploadFiles) {
        const base64Files = await Promise.all(uploadFiles.map(x => {
                return getBase64(x).then(base64 => {
                    return {
                        name: x.name,
                        file: base64
                    }
                })
            })
        );
        const payload = {
            serviceRequestId,
            files: base64Files,
        }

        return fetch(`${ApiUrls.SRActivity}uploadInternalDocument`, {
            method: 'POST',
            body: JSON.stringify(payload)
        })
    }

    async deleteInternalDocument(id) {
        return fetch(`${ApiUrls.SRActivity}deleteInternalDocument&documentId=${id}`, {
            method: 'DELETE'
        })
            .then(res => res.json())
    }

    async linkSalesOrder(serviceRequestId, salesOrderId) {
        return fetch(`Activity.php?action=assignLinkedSalesOrderToServiceRequest`,
            {
                method: 'POST',
                body: JSON.stringify({serviceRequestId, salesOrderId})
            }
        )
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'ok') {
                    throw new Error(res.message);
                }
            })
    }

    async addAdditionalTimeRequest(serviceRequestId, reason, timeRequested, selectedContactId) {
        const response = await fetch(`${ApiUrls.SRActivity}addAdditionalTimeRequest`,
            {
                method: 'POST',
                body: JSON.stringify({serviceRequestId, reason, timeRequested, selectedContactId})
            }
        )
        let jsonResponse = null;
        try {
            jsonResponse = await response.json();
        } catch (error) {
            throw new Error('Failed to parse json response');
        }
        if (jsonResponse.status !== 'ok') {
            throw new Error(jsonResponse.message);
        }
    }

    async getAdditionalChargeableWorkRequestInfo(id) {
        const response = await fetch(`${ApiUrls.SRActivity}getAdditionalChargeableWorkRequestInfo&id=${id}`)
        const jsonResponse = await response.json();
        if (jsonResponse.status !== 'ok') {
            throw new Error(jsonResponse.message);
        }
        return jsonResponse.data;
    }

    async cancelChargeableRequest(id, cancelReason) {
        return this.post(`${ApiUrls.sdDashboard}cancelPendingChargeableRequest`, {id, cancelReason})
    }

    async resendChargeableRequestEmail(id) {
        return this.post(`${ApiUrls.sdDashboard}resendPendingChargeableRequestEmail`, {id})
    }

    async checkServiceRequestPendingCallbacks(serviceRequestId) {
        return this.post(`${ApiUrls.SRActivity}checkServiceRequestPendingCallbacks`, {serviceRequestId}).then(res => res.json()).then(res => res.data)
    }

    getPendingReopen(id) {
        return this.get(`${ApiUrls.SRActivity}pendingReopened&id=${id}`);
    }

    async deleteUnstartedServiceRequests(search) {
        const res = await this.post(`${ApiUrls.SRActivity}deleteUnstartedServiceRequests`, {search});
        return await res.json();
    }

    async forceCloseServiceRequest(serviceRequestId) {
        const res = await this.post(`${ApiUrls.SRActivity}forceCloseServiceRequest`, {serviceRequestId});
        return await res.json();
    }
}

export default APIActivity;