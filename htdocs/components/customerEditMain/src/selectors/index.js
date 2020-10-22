import {createSelector} from "reselect";
import portalCustomerDocuments from "../reducers/portalCustomerDocuments";

const getContacts = (state) => state.contacts;
const getMappedContacts = createSelector(
    [getContacts],
    (contacts) => {
        return contacts.allIds.map(id => contacts.byIds[id])
    }
)
const getSitesMap = (state) => state.sites;
const getMappedSites = createSelector(
    [getSitesMap],
    (sitesMap) => {
        return sitesMap.allIds.map(id => sitesMap.byIds[id]);
    }
)

export const createGetSiteContacts = () => {
    return createSelector(
        [getMappedContacts, (state, props) => props.siteNo],
        (contacts, siteNo) => {
            return contacts.filter(contact => contact.siteNo === siteNo);
        }
    )
}

export const createGetSite = () => {
    return createSelector(
        [getSitesMap, (state, props) => props.siteNo],
        (sitesMap, siteNo) => {
            return sitesMap.byIds[siteNo];
        }
    )
}

export const getCustomer = (state) => state.customerEdit.customer;

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
    (state) =>state.newPortalDocumentModalShown
)


