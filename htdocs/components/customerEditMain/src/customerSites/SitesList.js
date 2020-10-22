import React from 'react';
import {connect} from "react-redux";
import {changeDeliverSiteNo, changeInvoiceSiteNo, toggleVisibility} from "../actions";
import Site from './Site.js';
import {SHOW_ACTIVE} from "../visibilityFilterTypes";
import {getDeliverSiteNo, getInvoiceSiteNo} from "../selectors";

const SitesList = ({sites, visibilityFilter, onToggleVisibility, invoiceSiteNo, deliverSiteNo, onChangeInvoiceSiteNo, onChangeDeliverSiteNo}) => {
    const getSiteOptions = (sites) => {
        return sites.filter(x => x.active).map(site => {
            return (
                <option value={site.siteNo}
                        key={site.siteNo}
                >
                    {`${site.address1} - ${site.town}`}
                </option>
            )
        })
    }
    console.log('sitesList rerendered');
    return (

        <div className="mt-3">
            <div className="row">
                <div className="col-md-12">
                    <h2>Sites</h2>
                </div>
                <div className="col-md-12 custom-fa">
                    <div className="form-inline">
                        <div className="form-group">

                            <button className="btn btn-sm btn-new mt-3 mb-3"
                                    data-toggle="modal"
                                    data-target="#newSiteModal"
                            >Add Site
                            </button>
                        </div>
                        <div className="form-group">
                            <select name="invoiceSiteNo"
                                    value={'' + invoiceSiteNo || ''}
                                    onChange={($event) => onChangeInvoiceSiteNo($event.target.value)}
                                    className="form-control input-sm mr-1"
                            >
                                <option value="">
                                    Select a Default Invoice
                                </option>
                                {getSiteOptions(sites)}
                            </select>

                            <select name="deliverSiteNo"
                                    value={'' + deliverSiteNo || ''}
                                    onChange={($event) => onChangeDeliverSiteNo($event.target.value)}
                                    className="form-control input-sm"
                            >
                                <option value="">
                                    Select a Default deliver Site
                                </option>
                                {getSiteOptions(sites)}
                            </select>
                        </div>
                        <div className="form-group form-inline">
                            <label className="switch">
                                <input type="checkbox"
                                       name="showOnlyActiveSites"
                                       onChange={($event) => onToggleVisibility()}
                                       title="Show only active sites"
                                       value="1"
                                       checked={visibilityFilter === SHOW_ACTIVE}
                                       className="form-control"
                                />
                                <span className="slider round"/>
                            </label>
                            Show Active Only
                        </div>
                    </div>
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
                                              siteNo={site.siteNo}
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

function mapStateToProps(state) {
    return {
        sites: getVisibleSites(state.sites, state.visibilityFilter),
        visibilityFilter: state.visibilityFilter,
        invoiceSiteNo: getInvoiceSiteNo(state),
        deliverSiteNo: getDeliverSiteNo(state)
    }
}

function mapDispatchToProps(dispatch) {
    return {
        onToggleVisibility: () => {
            dispatch(toggleVisibility())
        },
        onChangeInvoiceSiteNo: (value) => {
            dispatch(changeInvoiceSiteNo(value));
        },
        onChangeDeliverSiteNo: (value) => {
            dispatch(changeDeliverSiteNo(value));
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(SitesList)