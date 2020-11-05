"use strict";
import { sort } from '../../utils/utils.js?v=1';
import APIMain from './../../services/APIMain.js?v=1';

class SVCCurrentActivityService extends APIMain {
    baseURL = "CurrentActivityReport.php?action=";
    activityUrl="Activity.php?action=";
    getHelpDeskInbox() {
        return fetch(`${this.baseURL}getHelpDeskInbox`).then(res => res.json());
    } 
    getEscalationsInbox() {
        return fetch(`${this.baseURL}getEscalationsInbox`).then(res => res.json());
    } 
    getSalesInbox() {
        return fetch(`${this.baseURL}getSalesInbox`).then(res => res.json());
    } 
    getSmallProjectsInbox() {
        return fetch(`${this.baseURL}getSmallProjectsInbox`).then(res => res.json());
    } 
    getProjectsInbox() {
        return fetch(`${this.baseURL}getProjectsInbox`).then(res => res.json());
    } 
    getFixedInbox() {
        return fetch(`${this.baseURL}getFixedInbox`).then(res => res.json());
    } 
    getFutureInbox() {
        return fetch(`${this.baseURL}getFutureInbox`).then(res => res.json());
    }
    getToBeLoggedInbox() {
        return fetch(`${this.baseURL}getToBeLoggedInbox`).then(res => res.json());
    } 
    getPendingReopenedInbox() {
        return fetch(`${this.baseURL}getPendingReopenedInbox`).then(res => res.json());
    } 
    startActivityWork(callActivityId)
    {
        return fetch(`${this.activityUrl}createFollowOnActivity&callActivityID=${callActivityId}`).then(res=>{
             console.log(res)
             if(res.status===200)
             window.open(res.url,'_blank');
        });
    }
    changeQueue(problemID,queue,reason)
    {
        return fetch(`${this.baseURL}changeQueue&problemID=${problemID}&queue=${queue}&reason=${reason}`).then(res => res.json());
    }
    requestAdditionalTime(problemID,reason)
    {
        return fetch(`${this.activityUrl}requestAdditionalTime&problemID=${problemID}&reason=${reason}`);
    }
    getAllocatedUsers()
    {
        return fetch(`${this.baseURL}allocatedUsers`).then(res => res.json());
    }
    allocateUser(problemId,userId)
    {
        return fetch(`${this.baseURL}allocateUser&problemID=${problemId}&userID=${userId}`).then(res => res.json());

    }
    deleteSR(customerProblemNo)
    {
        return fetch(`${this.baseURL}deleteCustomerRequest&cpr_customerproblemno=${customerProblemNo}`).then(res => res.json());
    }
    processPendingReopened(pendingReopenedID,result)
    {
        const data={
            pendingReopenedID,result
        };
        return fetch(`${this.baseURL}processPendingReopened`, {
            method: 'POST',
            body: JSON.stringify(data)
        }).then(res => res.json());
    }
    newSRPendingReopened(data)
    { 
        return fetch(`${this.activityUrl}editServiceRequestHeader`, {
            method: 'POST',
            body: JSON.stringify(data)
        }).then(res => res.json());
    }
    getCustomerOpenSR(customerID) {
        return fetch(`${this.baseURL}getCustomerOpenSR&customerID=${customerID}`).then(res => res.json()) ;
    } 
}

export default SVCCurrentActivityService;
