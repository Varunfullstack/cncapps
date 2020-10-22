import {
    ADD_CONTACT_TO_SITE,
    ADD_ERROR,
    ADD_SITE,
    CHANGE_DELIVER_SITE_NO,
    CHANGE_INVOICE_SITE_NO,
    DELETE_PROJECT_FAILURE,
    DELETE_PROJECT_REQUEST,
    DELETE_PROJECT_SUCCESS,
    DELETE_SITE_REQUEST,
    DELETE_SITE_SUCCESS,
    DISMISS_ERROR,
    FETCH_ACCOUNT_MANAGERS,
    FETCH_ACCOUNT_MANAGERS_SUCCESS,
    FETCH_CONTACTS_FAILURE,
    FETCH_CONTACTS_REQUEST,
    FETCH_CONTACTS_SUCCESS,
    FETCH_CUSTOMER_REQUEST,
    FETCH_CUSTOMER_SUCCESS,
    FETCH_CUSTOMER_TYPES,
    FETCH_CUSTOMER_TYPES_SUCCESS,
    FETCH_LEAD_STATUSES,
    FETCH_LEAD_STATUSES_SUCCESS,
    FETCH_PROJECTS_FAILURE,
    FETCH_PROJECTS_REQUEST,
    FETCH_PROJECTS_SUCCESS,
    FETCH_REVIEW_ENGINEERS,
    FETCH_REVIEW_ENGINEERS_SUCCESS,
    FETCH_SECTORS,
    FETCH_SECTORS_SUCCESS,
    FETCH_SITES_FAILURE,
    FETCH_SITES_REQUEST,
    FETCH_SITES_SUCCESS,
    HIDE_NEW_PROJECT_MODAL,
    NEW_PROJECT_FIELD_UPDATE,
    REQUEST_ADD_PROJECT,
    REQUEST_ADD_PROJECT_FAILURE,
    REQUEST_ADD_PROJECT_SUCCESS,
    REQUEST_SAVE_SITE,
    REQUEST_UPDATE_CUSTOMER,
    REQUEST_UPDATE_CUSTOMER_FAILED,
    REQUEST_UPDATE_CUSTOMER_FAILED_OUT_OF_DATE,
    REQUEST_UPDATE_CUSTOMER_SUCCESS,
    SAVE_CUSTOMER_DATA_SUCCESS,
    SAVE_SITE_SUCCESS,
    SHOW_NEW_PROJECT_MODAL,
    TOGGLE_VISIBILITY,
    UPDATE_CUSTOMER_VALUE,
    UPDATE_SITE
} from "./actionTypes";
import {updateCustomer} from "./helpers";
import {OutOfDateError} from "./helpers/OutOfDateError";
import debounce from "../../utils/debounce";

export const VisibilityFilterOptions = {
    SHOW_ALL: 'SHOW_ALL',
    SHOW_ACTIVE: 'SHOW_ACTIVE'
}

export function addSite(customerId) {
    return {type: ADD_SITE, customerId};
}

export function updateSite(siteNo, data) {
    return {type: UPDATE_SITE, siteNo, data}
}


export function deleteSiteRequest(siteNo) {
    return {type: DELETE_SITE_REQUEST, siteNo}
}

export function deleteSiteSuccess(siteNo) {
    return {type: DELETE_SITE_SUCCESS, siteNo}
}

export function requestContacts(customerId) {
    return {type: FETCH_CONTACTS_REQUEST, customerId}
}

export function receiveContacts(customerId, contacts) {
    return {type: FETCH_CONTACTS_SUCCESS, customerId, contacts}
}

export function failedRequestContacts(response) {
    return {type: FETCH_CONTACTS_FAILURE, response}
}

export function requestSites(customerId) {
    return {type: FETCH_SITES_REQUEST, customerId}
}

export function receiveSites(customerId, sites) {
    return {type: FETCH_SITES_SUCCESS, customerId, sites}
}

export function requestCustomer(customerId) {
    return {type: FETCH_CUSTOMER_REQUEST, customerId}
}

export function receiveCustomer(customer) {
    return {type: FETCH_CUSTOMER_SUCCESS, customer}
}

export function requestCustomerTypes() {
    return {type: FETCH_CUSTOMER_TYPES}
}

export function receiveCustomerTypes(customerTypes) {
    return {type: FETCH_CUSTOMER_TYPES_SUCCESS, customerTypes}
}

export function requestLeadStatuses() {
    return {type: FETCH_LEAD_STATUSES}
}

export function receiveLeadStatuses(leadStatuses) {
    return {type: FETCH_LEAD_STATUSES_SUCCESS, leadStatuses}
}

export function requestSectors() {
    return {type: FETCH_SECTORS}
}

export function receiveSectors(sectors) {
    return {type: FETCH_SECTORS_SUCCESS, sectors}
}

export function requestAccountManagers() {
    return {type: FETCH_ACCOUNT_MANAGERS}
}

export function receiveAccountManagers(accountManagers) {
    return {type: FETCH_ACCOUNT_MANAGERS_SUCCESS, accountManagers}
}

export function requestReviewEngineers() {
    return {type: FETCH_REVIEW_ENGINEERS}
}

export function receiveReviewEngineers(reviewEngineers) {
    return {type: FETCH_REVIEW_ENGINEERS_SUCCESS, reviewEngineers}
}


export function failedRequestSites(response) {
    return {type: FETCH_SITES_FAILURE, response}
}

export function changeDeliverSiteNo(siteNo) {
    return {type: CHANGE_DELIVER_SITE_NO, siteNo}
}

export function changeInvoiceSiteNo(siteNo) {
    return {type: CHANGE_INVOICE_SITE_NO, siteNo}
}

export function addContactToSite(siteNo) {
    return {type: ADD_CONTACT_TO_SITE, siteNo}
}

export function toggleVisibility() {
    return {type: TOGGLE_VISIBILITY}
}

export function savedSiteData(siteNo) {
    return {type: SAVE_SITE_SUCCESS, siteNo}
}

export function savedCustomerData() {
    return {type: SAVE_CUSTOMER_DATA_SUCCESS}
}

export function requestSaveSite(siteNo) {
    return {type: REQUEST_SAVE_SITE, siteNo}
}

export function saveSiteSuccess(siteNo) {
    return {type: SAVE_SITE_SUCCESS, siteNo}
}

export function updateCustomerValue(field, value) {
    return {type: UPDATE_CUSTOMER_VALUE, field, value}
}

export function requestUpdateCustomer(field, value) {
    return {type: REQUEST_UPDATE_CUSTOMER, field, value};
}

export function requestUpdateCustomerFailedOutOfDate(lastUpdatedDateTime) {
    return {type: REQUEST_UPDATE_CUSTOMER_FAILED_OUT_OF_DATE, lastUpdatedDateTime};
}

export function requestUpdateCustomerSuccess(lastUpdatedDateTime) {
    return {type: REQUEST_UPDATE_CUSTOMER_SUCCESS, lastUpdatedDateTime};
}

export function dismissError(errorIndex) {
    return {type: DISMISS_ERROR, errorIndex};
}

export function addError(message, variant = 'danger') {
    return {type: ADD_ERROR, message, variant};
}

export function requestUpdateCustomerFailed() {
    return {type: REQUEST_UPDATE_CUSTOMER_FAILED}
}

export function deleteProjectRequest(id) {
    return {type: DELETE_PROJECT_REQUEST, id}
}

export function deleteProjectSuccess(id) {
    return {type: DELETE_PROJECT_SUCCESS, id};
}

export function deleteProjectFailure(id) {
    return {type: DELETE_PROJECT_FAILURE, id};
}

export function fetchSites(customerId) {
    return dispatch => {
        dispatch(requestSites(customerId))
        return fetch(`?action=getSites&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveSites(customerId, json.data)))
    }
}

export function fetchCustomer(customerId) {
    return dispatch => {
        dispatch(requestCustomer(customerId))
        return fetch('?action=getCustomer&customerID=' + customerId)
            .then(response => response.json())
            .then(json => dispatch(receiveCustomer(json.data)))
    }
}

export function fetchCustomerTypes() {
    return dispatch => {
        dispatch(requestCustomerTypes());
        return fetch('?action=getCustomerTypes')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveCustomerTypes(json.data.map(x => ({
                    label: x.cty_desc,
                    value: x.cty_ctypeno
                }))))
            })
    }
}

export function fetchLeadStatuses() {
    return dispatch => {
        dispatch(requestLeadStatuses());
        return fetch('?action=getLeadStatuses')
            .then(response => response.json())
            .then(json => {
                dispatch(
                    receiveLeadStatuses(json.data.map(x => ({
                            label: x.name,
                            value: x.id
                        }))
                    )
                )
            })
    }
}

export function fetchSectors() {
    return dispatch => {
        dispatch(requestSectors());
        return fetch('?action=getSectors')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveSectors(json.data.map(x => ({
                    label: x.sec_desc,
                    value: x.sec_sectorno
                }))))
            })
    }
}

export function fetchAccountManagers() {
    return dispatch => {
        dispatch(requestAccountManagers());
        return fetch('?action=getAccountManagers')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveAccountManagers(json.data.map(x => ({
                    label: x.cns_name,
                    value: x.cns_consno,
                }))))
            })
    }
}

export function fetchReviewEngineers() {
    return dispatch => {
        dispatch(requestReviewEngineers());
        return fetch('?action=getReviewEngineers')
            .then(response => response.json())
            .then(json => {
                dispatch(receiveReviewEngineers(json.data.map(x => ({
                    label: x.cns_name,
                    value: x.cns_consno,
                }))))
            })
    }
}


export function fetchContacts(customerId) {
    return dispatch => {
        dispatch(requestContacts(customerId))
        return fetch(`?action=getContacts&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveContacts(customerId, json.data)))
    }
}


export function deleteProject(projectId) {
    return dispatch => {
        dispatch(deleteProjectRequest(projectId));
        return fetch('Project.php?action=delete&projectId=' + projectId)
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'ok') {
                    throw new Error(res.message);
                }
                dispatch(deleteProjectSuccess(projectId));
            })
            .catch(error => {
                dispatch(deleteProjectFailure(projectId));
                dispatch(addError(`Failed to delete project! - ${error}`));
            })
    }
}

export function deleteSite(customerId, siteNo) {
    return dispatch => {
        dispatch(deleteSiteRequest(siteNo))
        return fetch(`?action=deleteSite`,
            {
                method: 'POST',
                body: JSON.stringify({
                    customerId,
                    siteNo
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'ok') {
                    throw new Error(res.message);
                }
                dispatch(deleteSiteSuccess(siteNo));
            })
    }
}

export function fetchAllData(customerId) {
    return dispatch => {
        dispatch(fetchSites(customerId));
        dispatch(fetchContacts(customerId));
        dispatch(fetchCustomer(customerId));
        dispatch(fetchCustomerTypes());
        dispatch(fetchLeadStatuses());
        dispatch(fetchSectors());
        dispatch(fetchAccountManagers());
        dispatch(fetchReviewEngineers());
        dispatch(fetchProjects(customerId));
    }
}

export function fetchProjectsRequest() {
    return {type: FETCH_PROJECTS_REQUEST};
}

export function fetchProjectsSuccess(projects) {
    return {type: FETCH_PROJECTS_SUCCESS, projects};
}

export function fetchProjectsFailure() {
    return {type: FETCH_PROJECTS_FAILURE};
}

export function fetchProjects(customerId) {
    return dispatch => {
        dispatch(fetchProjectsRequest());
        return fetch(`?action=getCustomerProjects&customerId=${customerId}`)
            .then(res => res.json())
            .then(response => {
                if (response.status !== 'ok') {
                    throw new Error(response.message);
                }
                dispatch(fetchProjectsSuccess(response.data));
            })
            .catch(error => {
                dispatch(fetchProjectsFailure());
                dispatch(addError(error));
            })
    }
}

const debounceTime = 350;


const debouncedUpdateCustomer = debounce((dispatch, field, value, getState) => {

    const {customerID, lastUpdatedDateTime} = getState().customerEdit.customer;
    dispatch(requestUpdateCustomer(field, value));
    updateCustomer(customerID, {[field]: value}, lastUpdatedDateTime)
        .then(newLastUpdated => {
            // we have to apply the change to the original customer
            dispatch(requestUpdateCustomerSuccess(newLastUpdated))
        })
        .catch((error) => {
            if (error instanceof OutOfDateError) {
                //we should refetch everything ..just in case
                dispatch(requestUpdateCustomerFailedOutOfDate(error.lastUpdatedDateTime));
                dispatch(addError('Unable to save change due to another edit by someone else'));
                dispatch(fetchAllData(customerID));
                return;
            }

            dispatch(requestUpdateCustomerFailed());
            dispatch(addError(`Unable to save change due to an error in the server: ${error.message}`))
        })
}, debounceTime);

export function updateCustomerField(field, value) {
    return (dispatch, getState) => {
        dispatch(updateCustomerValue(field, value));
        debouncedUpdateCustomer(dispatch, field, value, getState);
    }
}


export function updateInvoiceSiteNo(value) {
    return updateCustomerField('invoiceSiteNo', value);
}

export function updateDeliverSiteNo(value) {
    return updateCustomerField('deliverSiteNo', value);
}

export function saveSite(site) {
    return dispatch => {
        dispatch(requestSaveSite(site.siteNo));
        return fetch('?action=updateSite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ...site,
            })
        })
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'ok') {
                    throw new Error(res.message);
                }
                dispatch(saveSiteSuccess(site.siteNo));
            })
    }
}


export function newProjectFieldUpdate(field, value) {
    return {type: NEW_PROJECT_FIELD_UPDATE, field, value}
}

export function hideNewProjectModal() {
    return {type: HIDE_NEW_PROJECT_MODAL}
}

export function showNewProjectModal() {
    return {type: SHOW_NEW_PROJECT_MODAL}
}

export function requestAddProject(customerId, description, summary, openedDate) {
    return {type: REQUEST_ADD_PROJECT, customerId, description, summary, openedDate}
}

export function requestAddProjectSuccess(project) {
    return {type: REQUEST_ADD_PROJECT_SUCCESS, project}
}

export function requestAddProjectFailure() {
    return {type: REQUEST_ADD_PROJECT_FAILURE}
}

export function addNewProject(customerId, description, summary, openedDate) {
    return (dispatch) => {
        dispatch(requestAddProject(customerId, description, summary, openedDate));
        return fetch('Project.php?action=addProject',
            {
                method: 'POST',
                body: JSON.stringify(
                    {
                        customerId,
                        description,
                        summary,
                        openedDate
                    }
                )
            }
        )
            .then(res => res.json())
            .then(response => {
                if (response.status !== 'ok') {
                    throw new Error(response.message);
                }
                dispatch(requestAddProjectSuccess(response.data));
            })
            .catch(error => {
                dispatch(requestAddProjectFailure());
                addError(error);
            })

    }
}
