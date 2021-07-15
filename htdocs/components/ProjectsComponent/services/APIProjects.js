import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIProjects extends APIMain {
    getProjects() {
        return this.get(`${ApiUrls.Projects}projects`);
    }

    getProject(projectID) {
        return this.get(`${ApiUrls.Projects}project&&projectID=${projectID}`);
    }

    getProjectHistory(projectID, lastUpdateOnly = false) {
        let url = `${ApiUrls.Projects}history&&projectID=${projectID}`
        if (lastUpdateOnly)
            url += "lastUpdateOnly";
        return this.get(url);
    }

    getBudgetData(projectID) {
        return this.get(`${ApiUrls.Projects}budgetData&&projectID=${projectID}`);
    }

    updateProject(body) {
        return this.put(`${ApiUrls.Projects}project`, body)
    }

    uploadProjectFiles(projectID, files) {
        return this.uploadFiles(`${ApiUrls.Projects}projectFiles&projectID=${projectID}`, files, 'files');
    }

    unlinkSalesOrder(projectID, orignalOrder = false) {
        return this.post(`${ApiUrls.Projects}unlinkSalesOrder&projectID=${projectID}&orignalOrder=${orignalOrder}`);
    }

    linkSalesOrder(projectID, ordHeadID, orignalOrder = false) {
        return this.post(`${ApiUrls.Projects}linkSalesOrder&projectID=${projectID}&ordHeadID=${ordHeadID}&orignalOrder=${orignalOrder}`).then((res) => res.json());
    }

    calculateBudget(projectID) {
        return this.post(`${ApiUrls.Projects}calculateBudget&projectID=${projectID}`)
            .then((res) => res.json());
    }

    addProject(body) {
        return this.post(`${ApiUrls.Projects}project`, body).then((res) => res.json());
    }

    getProjectIssues(projectID) {
        return this.get(`${ApiUrls.Projects}projectIssues&projectID=${projectID}`);
    }

    addProjectIssues(projectID, data) {
        return this.post(`${ApiUrls.Projects}projectIssues&projectID=${projectID}`, data).then((res) => res.json());
    }

    updateProjectIssue(projectID, data) {
        return this.put(`${ApiUrls.Projects}projectIssues&projectID=${projectID}`, data);
    }

    deleteProjectIssue(id) {
        return this.delete(`${ApiUrls.Projects}projectIssues&id=${id}`);
    }

    getProjectSummary(projectID) {
        return this.get(`${ApiUrls.Projects}projectSummary&projectID=${projectID}`);
    }

    updateProjectSummary(projectID, data) {
        return this.put(`${ApiUrls.Projects}projectSummary&projectID=${projectID}`, data);
    }

    getProjectStagesHistory(projectID) {
        return this.get(`${ApiUrls.Projects}projectStagesHistory&projectID=${projectID}`);
    }

    updateProjectStage(projectID, oldStageID, newStageID) {
        return this.put(`${ApiUrls.Projects}projectStage&projectID=${projectID}&oldStageID=${oldStageID}&newStageID=${newStageID}`);
    }

    getOriginalQuoteDocument = (projectID) => {
        return this.put(`${ApiUrls.Projects}projectOriginalQuotoeDoc&projectID=${projectID}`);

    }

    getPRojectsSummary() {
        return this.get(`${ApiUrls.Projects}projectsSummary`);
    }

    //------------------------Report API
    getProjectsSearch(consID = '', dateFrom = '', dateTo = '', stageID = '', typeID = '') {
        return this.get(`${ApiUrls.Projects}projectsSearch&consID=${consID}&dateFrom=${dateFrom}&dateTo=${dateTo}&stageID=${stageID}&typeID=${typeID}`);
    }

    getProjectsByConsultantInProgress(consID, dateFrom, dateTo) {
        return this.get(`${ApiUrls.Projects}projectsByConsultantInProgress&consID=${consID}&dateFrom=${dateFrom}&dateTo=${dateTo}`);
    }

    getProjectsByCustomerStageFallsStartEnd(customerID, dateFrom, dateTo) {
        return this.get(`${ApiUrls.Projects}projectsByCustomerStageFallsStartEnd&customerID=${customerID}&dateFrom=${dateFrom}&dateTo=${dateTo}`);
    }

    getProjectsWithoutClousureMeeting(consID = '') {
        return this.get(`${ApiUrls.Projects}projectsWithoutClousureMeeting&consID=${consID}`);
    }
    deleteProject(projectId){
        return this.delete('Project.php?action=delete&projectID=' + projectId,true);
    }
}
