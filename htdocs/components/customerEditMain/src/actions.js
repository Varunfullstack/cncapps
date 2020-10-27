import {
    ADD_CONTACT_TO_SITE,
    ADD_ERROR,
    ADD_SITE,
    CHANGE_DELIVER_SITE_NO,
    CHANGE_INVOICE_SITE_NO,
    CLEAR_EDIT_NOTE,
    CLEAR_EDIT_SITE,
    DELETE_PORTAL_CUSTOMER_DOCUMENT_FAILURE,
    DELETE_PORTAL_CUSTOMER_DOCUMENT_REQUEST,
    DELETE_PORTAL_CUSTOMER_DOCUMENT_SUCCESS,
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
    FETCH_CUSTOMER_NOTES_REQUEST,
    FETCH_CUSTOMER_NOTES_SUCCESS,
    FETCH_CUSTOMER_REQUEST,
    FETCH_CUSTOMER_SUCCESS,
    FETCH_CUSTOMER_TYPES,
    FETCH_CUSTOMER_TYPES_SUCCESS,
    FETCH_LEAD_STATUSES,
    FETCH_LEAD_STATUSES_SUCCESS,
    FETCH_ORDERS_REQUEST,
    FETCH_ORDERS_SUCCESS,
    FETCH_PORTAL_CUSTOMER_DOCUMENTS_FAILURE,
    FETCH_PORTAL_CUSTOMER_DOCUMENTS_REQUEST,
    FETCH_PORTAL_CUSTOMER_DOCUMENTS_SUCCESS,
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
    HIDE_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL,
    HIDE_NEW_PROJECT_MODAL,
    HIDE_NEW_SITE_MODAL,
    NEW_PORTAL_CUSTOMER_DOCUMENT_FIELD_UPDATE,
    NEW_PROJECT_FIELD_UPDATE,
    NEW_SITE_FIELD_UPDATE,
    REQUEST_ADD_NOTE,
    REQUEST_ADD_NOTE_FAILURE,
    REQUEST_ADD_NOTE_SUCCESS,
    REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT,
    REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_FAILURE,
    REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_SUCCESS,
    REQUEST_ADD_PROJECT,
    REQUEST_ADD_PROJECT_FAILURE,
    REQUEST_ADD_PROJECT_SUCCESS,
    REQUEST_ADD_SITE,
    REQUEST_ADD_SITE_FAILURE,
    REQUEST_ADD_SITE_SUCCESS,
    REQUEST_SAVE_SITE,
    REQUEST_UPDATE_CUSTOMER,
    REQUEST_UPDATE_CUSTOMER_FAILED,
    REQUEST_UPDATE_CUSTOMER_FAILED_OUT_OF_DATE,
    REQUEST_UPDATE_CUSTOMER_SUCCESS,
    REQUEST_UPDATE_NOTE,
    REQUEST_UPDATE_NOTE_FAILED,
    REQUEST_UPDATE_NOTE_FAILED_OUT_OF_DATE,
    REQUEST_UPDATE_NOTE_SUCCESS,
    REQUEST_UPDATE_SITE,
    REQUEST_UPDATE_SITE_FAILED,
    REQUEST_UPDATE_SITE_FAILED_OUT_OF_DATE,
    REQUEST_UPDATE_SITE_SUCCESS,
    SAVE_SITE_SUCCESS,
    SET_EDIT_NOTE,
    SET_EDIT_SITE,
    SHOW_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL,
    SHOW_NEW_PROJECT_MODAL,
    SHOW_NEW_SITE_MODAL,
    TOGGLE_VISIBILITY,
    UPDATE_CUSTOMER_VALUE,
    UPDATE_EDITING_NOTE_VALUE,
    UPDATE_EDITING_SITE_VALUE
} from "./actionTypes";
import {updateCustomer, updateNote, updateSite} from "./helpers";
import {OutOfDateError} from "./helpers/OutOfDateError";
import debounce from "../../utils/debounce";
import {fileToBase64} from "../../utils/utils";
import {getEditingNote, getEditingSite} from "./selectors";

export const VisibilityFilterOptions = {
    SHOW_ALL: 'SHOW_ALL',
    SHOW_ACTIVE: 'SHOW_ACTIVE'
}

export function addSite(customerId) {
    return {type: ADD_SITE, customerId};
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

function requestOrders(customerId) {
    return {type: FETCH_ORDERS_REQUEST, customerId};
}

export function receiveSites(customerId, sites) {
    return {type: FETCH_SITES_SUCCESS, customerId, sites}
}

function receiveOrders(customerId, orders) {
    return {type: FETCH_ORDERS_SUCCESS, customerId, orders}
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

export function fetchOrders(customerId) {
    return dispatch => {
        dispatch(requestOrders(customerId))
        return fetch(`?action=getCustomerOrders&customerId=${customerId}`)
            .then(res => res.json())
            .then(json => dispatch(receiveOrders(customerId, json.data)))
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

function fetchCustomerNotesRequestAction() {
    return {type: FETCH_CUSTOMER_NOTES_REQUEST}
}

function fetchCustomerNotesSuccessAction(customerNotes) {
    return {type: FETCH_CUSTOMER_NOTES_SUCCESS, customerNotes}
}

export function fetchCustomerNotes(customerId) {
    return dispatch => {
        dispatch(fetchCustomerNotesRequestAction());
        fetch(`/CustomerNote.php?action=getCustomerNotes&customerId=${customerId}`)
            .then(response => response.json())
            .then(response => {
                dispatch(fetchCustomerNotesSuccessAction(response.data));
            });
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

export function showNewPortalCustomerDocumentModal() {
    return {type: SHOW_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL};
}

export function hideNewPortalCustomerDocumentModal() {
    return {type: HIDE_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL};
}

export function newPortalCustomerDocumentFieldUpdate(field, value) {
    return {type: NEW_PORTAL_CUSTOMER_DOCUMENT_FIELD_UPDATE, field, value};
}

export function requestAddPortalCustomerDocument() {
    return {type: REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT};
}

export function requestAddPortalCustomerDocumentSuccess(portalCustomerDocument) {
    return {type: REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_SUCCESS, portalCustomerDocument};
}

export function requestAddPortalCustomerDocumentFailure() {
    return {type: REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_FAILURE};
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
        dispatch(fetchPortalCustomerDocuments(customerId));
        dispatch(fetchOrders(customerId));
        dispatch(fetchCustomerNotes(customerId));
    }
}

export function fetchPortalCustomerDocumentsRequest() {
    return {type: FETCH_PORTAL_CUSTOMER_DOCUMENTS_REQUEST};
}

export function fetchPortalCustomerDocumentsSuccess(portalCustomerDocuments) {
    return {type: FETCH_PORTAL_CUSTOMER_DOCUMENTS_SUCCESS, portalCustomerDocuments};
}

export function fetchPortalCustomerDocumentsFailure() {
    return {type: FETCH_PORTAL_CUSTOMER_DOCUMENTS_FAILURE};
}


export function fetchPortalCustomerDocuments(customerId) {
    return dispatch => {
        dispatch(fetchPortalCustomerDocumentsRequest());
        return fetch(`?action=getPortalCustomerDocuments&customerId=${customerId}`)
            .then(res => res.json())
            .then(response => {
                if (response.status !== 'ok') {
                    throw new Error(response.message);
                }
                dispatch(fetchPortalCustomerDocumentsSuccess(response.data));
            })
            .catch(error => {
                dispatch(fetchPortalCustomerDocumentsFailure());
                dispatch(addError(error));
            })
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
                dispatch(fetchCustomer(customerID));
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


export function setEditSiteAction(siteNo) {
    return {type: SET_EDIT_SITE, siteNo}
}

export function clearEditingSiteAction() {
    return {type: CLEAR_EDIT_SITE}
}


export function toggleEditingSite(siteNo) {
    return (dispatch, getState) => {
        const editingSite = getEditingSite(getState());
        if (editingSite) {
            if (editingSite.siteNo === siteNo) {
                return dispatch(clearEditingSiteAction());
            }
        }
        return dispatch(setEditSiteAction(siteNo))
    }
}

export function requestUpdateSiteFailedOutOfDate(lastUpdatedDateTime) {
    return {type: REQUEST_UPDATE_SITE_FAILED_OUT_OF_DATE, lastUpdatedDateTime};
}

function requestUpdateSiteAction(customerId, siteNo, field, value) {
    return {type: REQUEST_UPDATE_SITE, customerId, siteNo, field, value};
}

function requestUpdateSiteSuccessAction(newLastUpdatedDateTime) {
    return {type: REQUEST_UPDATE_SITE_SUCCESS, newLastUpdatedDateTime};
}

function requestUpdateSiteFailed() {
    return {type: REQUEST_UPDATE_SITE_FAILED}
}

const debounceUpdateSite = debounce((dispatch, field, value, state) => {
    const currentEditingSite = getEditingSite(state);
    const customerId = currentEditingSite.customerID;
    const siteNo = currentEditingSite.siteNo;
    const lastUpdatedDateTime = currentEditingSite.lastUpdatedDateTime;
    dispatch(requestUpdateSiteAction(customerId, siteNo, field, value));
    updateSite(customerId, siteNo, {[field]: value}, lastUpdatedDateTime)
        .then(newLastUpdated => {
            // we have to apply the change to the original customer
            dispatch(requestUpdateSiteSuccessAction(newLastUpdated))
        })
        .catch((error) => {
            if (error instanceof OutOfDateError) {
                //we should refetch everything ..just in case
                dispatch(requestUpdateSiteFailedOutOfDate(error.lastUpdatedDateTime));
                dispatch(addError('Unable to save change due to another edit by someone else'));
                dispatch(fetchSites(customerId));
                return;
            }

            dispatch(requestUpdateSiteFailed());
            dispatch(addError(`Unable to save change due to an error in the server: ${error.message}`))
        })
}, debounceTime);

const updateEditingSiteValueAction = (field, value) => {
    return {type: UPDATE_EDITING_SITE_VALUE, field, value}
}

export function updateSiteField(field, value) {
    return (dispatch, getState) => {
        dispatch(updateEditingSiteValueAction(field, value));
        debounceUpdateSite(dispatch, field, value, getState());
    }
}


export function setEditNoteAction(noteNo) {
    return {type: SET_EDIT_NOTE, noteNo}
}

export function clearEditingNoteAction() {
    return {type: CLEAR_EDIT_NOTE}
}


export function toggleEditingNote(noteNo) {
    return (dispatch, getState) => {
        const editingNote = getEditingNote(getState());
        if (editingNote) {
            if (editingNote.noteNo === noteNo) {
                return dispatch(clearEditingNoteAction());
            }
        }
        return dispatch(setEditNoteAction(noteNo))
    }
}

export function requestUpdateNoteFailedOutOfDate(lastUpdatedDateTime) {
    return {type: REQUEST_UPDATE_NOTE_FAILED_OUT_OF_DATE, lastUpdatedDateTime};
}

function requestUpdateNoteAction(customerId, noteNo, field, value) {
    return {type: REQUEST_UPDATE_NOTE, customerId, noteNo, field, value};
}

function requestUpdateNoteSuccessAction(newLastUpdatedDateTime) {
    return {type: REQUEST_UPDATE_NOTE_SUCCESS, newLastUpdatedDateTime};
}

function requestUpdateNoteFailed() {
    return {type: REQUEST_UPDATE_NOTE_FAILED}
}

const debounceUpdateNote = debounce((dispatch, field, value, state) => {
    const currentEditingNote = getEditingNote(state);
    const customerId = currentEditingNote.customerID;
    const noteNo = currentEditingNote.id;
    const lastUpdatedDateTime = currentEditingNote.lastUpdatedDateTime;
    dispatch(requestUpdateNoteAction(customerId, noteNo, field, value));
    updateNote(customerId, noteNo, {[field]: value}, lastUpdatedDateTime)
        .then(newLastUpdated => {
            // we have to apply the change to the original customer
            dispatch(requestUpdateNoteSuccessAction(newLastUpdated))
        })
        .catch((error) => {
            if (error instanceof OutOfDateError) {
                //we should refetch everything ..just in case
                dispatch(requestUpdateNoteFailedOutOfDate(error.lastUpdatedDateTime));
                dispatch(addError('Unable to save change due to another edit by someone else'));
                dispatch(fetchCustomerNotes(customerId));
                return;
            }

            dispatch(requestUpdateNoteFailed());
            dispatch(addError(`Unable to save change due to an error in the server: ${error.message}`))
        })
}, debounceTime);

const updateEditingNoteValueAction = (field, value) => {
    return {type: UPDATE_EDITING_NOTE_VALUE, field, value}
}

export function updateNoteField(field, value) {
    return (dispatch, getState) => {
        dispatch(updateEditingNoteValueAction(field, value));
        debounceUpdateNote(dispatch, field, value, getState());
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


function removeDataURLMetadata(base64DataURL) {
    return base64DataURL.split(",")[1];
}

export function addNewPortalCustomerDocument(customerId, portalDocument) {
    const {description, customerContract, mainContractOnly, file} = portalDocument;
    return dispatch => {
        dispatch(requestAddPortalCustomerDocument());
        return fileToBase64(file).then(encodedFile => {

            return fetch('?action=addPortalCustomerDocument',
                {
                    method: 'POST',
                    body: JSON.stringify({
                        customerId,
                        description,
                        customerContract,
                        mainContractOnly,
                        fileName: file.name,
                        fileSize: file.size,
                        encodedFile: removeDataURLMetadata(encodedFile),
                    })
                }
            )
                .then(res => res.json())
                .then(response => {
                    if (response.status !== 'ok') {
                        throw new Error(response.message);
                    }
                    dispatch(requestAddPortalCustomerDocumentSuccess(response.data));

                })
                .catch(error => {
                    dispatch(requestAddPortalCustomerDocumentFailure());
                    addError(error);
                })
        })

    }
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

export function deletePortalDocumentRequest(id) {
    return {type: DELETE_PORTAL_CUSTOMER_DOCUMENT_REQUEST, id}
}

export function deletePortalDocumentSuccess(id) {
    return {type: DELETE_PORTAL_CUSTOMER_DOCUMENT_SUCCESS, id};
}

export function deletePortalDocumentFailure(id) {
    return {type: DELETE_PORTAL_CUSTOMER_DOCUMENT_FAILURE, id};
}

export function deletePortalCustomerDocument(portalDocumentId) {
    return dispatch => {
        dispatch(deletePortalDocumentRequest(portalDocumentId));
        return fetch('?action=deletePortalDocument', {
            method: 'POST',
            body: JSON.stringify({portalDocumentId})
        })
            .then(res => res.json())
            .then(response => {
                if (response.status !== 'ok') {
                    throw new Error(response.message);
                }
                dispatch(deletePortalDocumentSuccess(portalDocumentId));
            })
            .catch(error => {
                dispatch(deletePortalDocumentFailure(portalDocumentId));
                addError(error);
            })
    }
}

export function newPortalDocumentFieldUpdate(field, value) {
    return dispatch => {
        dispatch(newPortalCustomerDocumentFieldUpdate(field, value));
    }
}

function createShowNewSiteModalAction() {
    return {type: SHOW_NEW_SITE_MODAL}
}

export function showNewSiteModal() {
    return dispatch => {
        dispatch(createShowNewSiteModalAction());
    }
}

function createHideNewSiteModalAction() {
    return {type: HIDE_NEW_SITE_MODAL}
}

export function hideNewSiteModal() {
    return dispatch => {
        dispatch(createHideNewSiteModalAction());
    }
}

function createNewSiteFieldUpdateAction(field, value) {
    return {type: NEW_SITE_FIELD_UPDATE, field, value};
}

export function newSiteFieldUpdate(field, value) {
    return dispatch => {
        dispatch(createNewSiteFieldUpdateAction(field, value));
    }
}

function createAddSiteRequestAction(customerId, newSite) {
    return {type: REQUEST_ADD_SITE, customerId, newSite}
}

function createAddSiteRequestSuccessAction(newSite) {
    return {type: REQUEST_ADD_SITE_SUCCESS, newSite};
}

function createAddSiteRequestFailureAction() {
    return {type: REQUEST_ADD_SITE_FAILURE};
}

export function addNewSite(customerId, newSite) {
    return (dispatch) => {
        dispatch(createAddSiteRequestAction(customerId, newSite));
        return fetch('?action=addSite',
            {
                method: 'POST',
                body: JSON.stringify(
                    {
                        customerId,
                        addressLine: newSite.addressLine,
                        town: newSite.town,
                        postcode: newSite.postcode,
                        phone: newSite.phone,
                        maxTravelHours: newSite.maxTravelHours,
                    }
                )
            }
        )
            .then(res => res.json())
            .then(response => {
                if (response.status !== 'ok') {
                    throw new Error(response.message);
                }
                dispatch(createAddSiteRequestSuccessAction(response.data));
            })
            .catch(error => {
                dispatch(createAddSiteRequestFailureAction());
                addError(error);
            })

    }
}

function createAddNoteRequestAction(customerId, newNote) {
    return {type: REQUEST_ADD_NOTE, customerId, newNote}
}

function createAddNoteRequestSuccessAction(newNote) {
    return {type: REQUEST_ADD_NOTE_SUCCESS, newNote};
}

function createAddNoteRequestFailureAction() {
    return {type: REQUEST_ADD_NOTE_FAILURE};
}

export function addNewNote(customerId, note) {
    return (dispatch) => {
        dispatch(createAddNoteRequestAction(customerId, note));
        return fetch('?action=addNote',
            {
                method: 'POST',
                body: JSON.stringify(
                    {
                        customerId,
                        note,
                    }
                )
            }
        )
            .then(res => res.json())
            .then(response => {
                if (response.status !== 'ok') {
                    throw new Error(response.message);
                }
                dispatch(createAddNoteRequestSuccessAction(response.data));
            })
            .catch(error => {
                dispatch(createAddNoteRequestFailureAction());
                addError(error);
            })

    }
}


export function goToFirstNote() {
    return {type: GO_TO_FIRST_NOTE};
}

export function goToPreviousNote() {
    return {type: GO_TO_PREVIOUS_NOTE};
}

export function goToNextNote() {
    return {type: GO_TO_NEXT_NOTE};
}

export function goToLastNote() {
    return {type: GO_TO_LAST_NOTE};
}
