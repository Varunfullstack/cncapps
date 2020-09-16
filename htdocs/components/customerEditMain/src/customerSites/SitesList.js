import React from 'react';
import {connect} from "react-redux";
import {SHOW_ACTIVE} from "./visibilityFilterTypes";
import {addContactToSite, changeDeliverSiteNo, changeInvoiceSiteNo, updateSite} from "./actions";
import Site from './Site.js';

const SitesList = ({sites, customerId, contacts, invoiceSiteNo, deliverSiteNo, changeInvoiceSiteNo, changeDeliverSiteNo, addContactToSite, updateSite}) => {
    return (

        <div className="container-fluid">
            <div className="row">
                <div className="col-md-12">
                    <h2>Sites</h2>
                </div>
                <div className="col-md-12">
                    <a href={`/Customer.php?action=addSite&customerID=${customerId}`}>
                        <button className="btn btn-primary mt-3 mb-3">Add Site</button>
                    </a>
                </div>
            </div>
            <div className="row">
                <div className="col-md-12">
                    <div className="customerEditSites">
                        <div className="accordion"
                             id="accordionExample1"
                        >
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
                                              updateSite={updateSite}
                                        />
                                    )) : ''
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

function getVisibleSites(sites, filter) {
    const mappedSites = sites.allIds.map(id => sites.byIds[id]);
    if (filter === SHOW_ACTIVE) {
        return mappedSites.filter(x => x.active);
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
    const {customer, sites, contacts, visibilityFilter} = state;
    return {
        sites: getVisibleSites(sites, visibilityFilter),
        customerId: customer.customerId,
        contacts: getMappedContacts(contacts),
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
        },
        updateSite: (siteNo, data) => {
            dispatch(updateSite(siteNo, data))
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(SitesList)