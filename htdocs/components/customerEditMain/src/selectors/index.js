import {createSelector} from "reselect";
import {SHOW_ACTIVE} from "../visibilityFilterTypes";

const getContacts = (state) => state.contacts;
const getMappedContacts = createSelector(
    [getContacts],
    (contacts) => {
        return contacts.allIds.map(id => contacts.byIds[id])
    }
)

const getSitesState = (state) => state.sites;
const getSitesByIds = createSelector(
    [getSitesState],
    sitesState => sitesState.byIds
)

const getSitesAllIds = createSelector(
    [getSitesState],
    sitesState => sitesState.allIds
)
const getMappedSites = createSelector(
    [getSitesByIds, getSitesAllIds],
    (byIds, allIds) => {
        return allIds.map(id => byIds[id]);
    }
)

export const createGetSiteContacts = () => {
    return createSelector(
        [getMappedContacts, (state, siteNo) => siteNo],
        (contacts, siteNo) => {
            return contacts.filter(contact => contact.siteNo === siteNo);
        }
    )
}

export const createGetEditingSiteForSite = () => {
    return createSelector(
        [getEditingSite, (state, observedSiteNo) => observedSiteNo],
        (editingSite, observedSiteNo) => {
            if (!editingSite || editingSite.siteNo !== observedSiteNo) {
                return null;
            }
            return editingSite;
        }
    )
}

export const createGetSite = () => {
    return createSelector(
        [getSitesState, (state, siteNo) => siteNo],
        (sitesMap, siteNo) => {
            return sitesMap.byIds[siteNo];
        }
    )
}
const getCustomerState = (state) => state.customerEdit;

export const getCustomer = createSelector(
    [getCustomerState],
    (customerState) => customerState.customer
)

export const getCustomerId = createSelector(
    [getCustomer],
    (customer) => customer?.id
)


export const getInvoiceSiteNo = createSelector(
    [getCustomer],
    (customer) => customer?.invoiceSiteNo
)

export const getDeliverSiteNo = createSelector(
    [getCustomer],
    (customer) => customer?.deliverSiteNo
)

export const getMainContacts = createSelector(
    [getMappedContacts],
    (contacts) => contacts.filter(contact => contact.supportLevel === 'main')
)

const getPortalCustomerDocumentsById = (state) => state.portalCustomerDocuments.byIds;
export const getMappedPortalCustomerDocuments = createSelector(
    [getPortalCustomerDocumentsById],
    (portalCustomerDocumentsById) => Object.keys(portalCustomerDocumentsById).map(key => portalCustomerDocumentsById[key])
)

const getPortalCustomerDocumentsState = (state) => state.portalCustomerDocuments;

export const getPortalCustomerDocumentsIsFetching = createSelector(
    [getPortalCustomerDocumentsState],
    (portalCustomerDocumentsState) => portalCustomerDocumentsState.isFetching
)

export const getPortalCustomerDocumentsNewPortalDocument = createSelector(
    [getPortalCustomerDocumentsState],
    (portalCustomerDocumentsState) => portalCustomerDocumentsState.newPortalDocument
)

export const getPortalCustomerDocumentsModalShown = createSelector(
    [getPortalCustomerDocumentsState],
    (state) => state.newPortalDocumentModalShown
)

const getVisibilityFilter = (state) => state.visibilityFilter;

export const getVisibleSites = createSelector(
    [getMappedSites, getVisibilityFilter],
    (mappedSites, visibilityFilter) => {
        if (visibilityFilter === SHOW_ACTIVE) {
            return mappedSites.filter(x => x.active);
        }
        return mappedSites;
    }
);


export const getEditingSite = createSelector(
    [getSitesState],
    siteState => siteState.editingSite
)


export const getLeadStatuses = createSelector(
    [getCustomerState],
    customerState => customerState.leadStatuses
)

export const getReviewEngineers = createSelector(
    [getCustomerState],
    customerState => customerState.reviewEngineers
)

const getCustomerNotesState = (state) => state.customerNotesState

export const getEditingNote = createSelector(
    [getCustomerNotesState],
    customerNotesState => customerNotesState.editingNote
)


const getNotesByIds = createSelector(
    [getCustomerNotesState],
    state => state.byIds
)

const getNotesAllIds = createSelector(
    [getCustomerNotesState],
    state => state.allIds
)
const getMappedNotes = createSelector(
    [getNotesByIds, getNotesAllIds],
    (byIds, allIds) => {
        return allIds.map(id => byIds[id]);
    }
)


export const getCustomerNotes = getMappedNotes;

const getOrderState = (state) => state.ordersState;
const getOrderStateByIds = createSelector(
    [getOrderState],
    orderState => orderState.byIds
)
const getOrderStateAllIds = createSelector(
    [getOrderState],
    orderState => orderState.allIds
)

const mappedOrders = createSelector(
    [getOrderStateByIds, getOrderStateAllIds],
    (byIds, allIds) => {
        return allIds.map(x => byIds[x]);
    }
)
export const getOrders = mappedOrders;