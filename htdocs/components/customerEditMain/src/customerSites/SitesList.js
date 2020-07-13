import React from 'react';
import {connect} from "react-redux";
import {SHOW_ACTIVE} from "./visibilityFilterTypes";
import {addContactToSite, changeDefaultDeliveryContact, changeDefaultInvoiceContact} from "./actions";

const SitesList = ({sites, customerId, defaultInvoice, defaultDelivery, changeDefaultInvoice, changeDefaultDelivery, addContactToSite}) => (
    <div className="sites-list">
        {
            sites.map(site => (
                <Site key={site.siteNo}
                      site={site}
                      customerId={customerId}
                      defaultInvoice={defaultInvoice}
                      defaultDelivery={defaultDelivery}
                      changeDefaultInvoice={changeDefaultInvoice}
                      changedDefaultDelivery={changeDefaultDelivery}
                      addContactToSite={addContactToSite}
                />
            ))
        }
    </div>
)

function getVisibleSites(sites, filter) {
    const mappedSites = sites.allIds.map(id => sites.byIds[id]);
    if (filter === SHOW_ACTIVE) {
        return mappedSites.filter(x => x.active === 'Y');
    }
    return mappedSites;
}

function getMappedContacts(contacts) {
    return contacts.allIds.map(id => contacts.byIds[id])
}

function mapStateToProps(state) {
    const {customer, sites, contacts, visibilityFilter} = state;
    return {
        sites: getVisibleSites(sites, visibilityFilter),
        customerId,
        contacts: getMappedContacts(contacts),
        defaultInvoice: customer.defaultInvoice,
        defaultDelivery: customer.defaultDelivery
    }
}

function mapDispatchToProps(dispatch) {
    return {
        changeDefaultInvoice: contactId => {
            dispatch(changeDefaultInvoiceContact(contactId))
        },
        changedDefaultDelivery: contactId => {
            dispatch(changeDefaultDeliveryContact(contactId))
        },
        addContactToSite: siteNo => {
            dispatch(addContactToSite(siteNo))
        }
    }
}

export default connect(mapStateToProps)(SitesList)