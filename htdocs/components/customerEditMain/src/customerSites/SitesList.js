import React from 'react';
import {connect} from "react-redux";
import {SHOW_ACTIVE} from "./visibilityFilterTypes";
import {addContactToSite, changeDeliverSiteNo, changeInvoiceSiteNo} from "./actions";
import Site from './Site.js';

const SitesList = ({sites, customerId, contacts, invoiceSiteNo, deliverSiteNo, changeInvoiceSiteNo, changeDeliverSiteNo, addContactToSite}) => {
    console.log(sites);
    return (
        <div className="sites-list">
            {
                sites.length ?
                    sites.map(site => (
                        <Site key={site.siteNo}
                              site={site}
                              contacts={contacts}
                              customerId={customerId}
                              invoiceSiteNo={invoiceSiteNo}
                              deliverSiteNo={deliverSiteNo}
                              changeInvoiceSiteNo={changeInvoiceSiteNo}
                              changedDeliverSiteNo={changeDeliverSiteNo}
                              addContactToSite={addContactToSite}
                        />
                    )) : ''
            }
        </div>
    )
}

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

function debug(key, result) {
    console.log(key, result);
    return result;
}

function mapStateToProps(state) {
    console.log('mapStateToProps', state);
    const {customer, sites, contacts, visibilityFilter} = state;
    return {
        sites: debug('getVisibleSites', getVisibleSites(sites, visibilityFilter)),
        customerId: customer.customerId,
        contacts: debug('getMappedContacts', getMappedContacts(contacts)),
        invoiceSiteNo: customer.invoiceSiteNo,
        deliverSiteNo: customer.deliverSiteNo
    }
}

function mapDispatchToProps(dispatch) {
    return {
        changeDeliverSiteNo: siteNo => {
            dispatch(changeDeliverSiteNo(siteNo))
        },
        changeInvoiceSiteNo: siteNo => {
            dispatch(changeInvoiceSiteNo(siteNo))
        },
        addContactToSite: siteNo => {
            dispatch(addContactToSite(siteNo))
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(SitesList)