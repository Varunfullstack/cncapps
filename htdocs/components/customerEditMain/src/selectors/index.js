import {createSelector} from "reselect";

const getContacts = (state) => state.contacts;
const getMappedContacts = createSelector(
    [getContacts],
    (contacts) => {
        return contacts.allIds.map(id => contacts.byIds[id])
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

export const getCustomer = (state) => state.customer;

export const getCustomerId = createSelector(
    [getCustomer],
    (customer) => customer.id
)


export const getInvoiceSiteNo = createSelector(
    [getCustomer],
    (customer) => customer.invoiceSiteNo
)

export const getDeliverSiteNo = createSelector(
    [getCustomer],
    (customer) => customer.deliverSiteNo
)

