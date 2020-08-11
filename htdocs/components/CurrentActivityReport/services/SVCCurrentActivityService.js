"use strict";
import APIMain from './../../services/APIMain.js?v=1';

class SVCCurrentActivityService extends APIMain {
    baseURL = "CurrentActivityReportNew.php?action=";
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
}

export default SVCCurrentActivityService;
