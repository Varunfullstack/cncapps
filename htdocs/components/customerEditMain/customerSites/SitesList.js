import React from 'react';
import {connect} from "react-redux";
import {
    addNewSite,
    changeDeliverSiteNo,
    changeInvoiceSiteNo,
    hideNewSiteModal,
    newSiteFieldUpdate,
    showNewSiteModal,
    toggleVisibility
} from "../actions";
import Site from './Site.js';
import {SHOW_ACTIVE} from "../visibilityFilterTypes";
import {getDeliverSiteNo, getInvoiceSiteNo, getVisibleSites} from "../selectors/selectors";
import AddSiteComponent from "../modals/AddSiteComponent";
import {Accordion} from "react-bootstrap";

const SitesList = ({
                       customerId,
                       sites,
                       visibilityFilter,
                       onToggleVisibility,
                       invoiceSiteNo,
                       deliverSiteNo,
                       newSite,
                       newSiteModalShow,
                       onChangeInvoiceSiteNo,
                       onChangeDeliverSiteNo,
                       onNewSiteFieldUpdate,
                       onNewSiteModalHide,
                       onNewSiteModalShow,
                       onAddSite,
                   }) => {
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
    // console.warn('sitesList rerendered', newSite);
    return (
        <div className="mt-3">
            <AddSiteComponent
                addressLine={newSite.addressLine}
                town={newSite.town}
                postcode={newSite.postcode}
                phone={newSite.phone}
                maxTravelHours={newSite.maxTravelHours}
                show={newSiteModalShow}
                onFieldUpdate={onNewSiteFieldUpdate}
                onClose={onNewSiteModalHide}
                onAdd={() => {
                    onAddSite(customerId, newSite)
                }}
            />
            <div className="row">
                <div className="col-md-12">
                    <h2>Sites</h2>
                </div>
                <div className="col-md-12 custom-fa">
                    <div className="form-inline">
                        <div className="form-group">

                            <button className="btn btn-sm btn-new mt-3 mb-3"
                                    onClick={() => onNewSiteModalShow()}
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
                        <Accordion>
                            {
                                sites.length ?
                                    sites.map(site => (
                                        <Site key={site.siteNo}
                                              siteNo={site.siteNo}
                                        />
                                    )) : ''
                            }
                        </Accordion>
                    </div>
                </div>
            </div>
        </div>
    )
}

function mapStateToProps(state) {

    return {
        sites: getVisibleSites(state),
        visibilityFilter: state.visibilityFilter,
        invoiceSiteNo: getInvoiceSiteNo(state),
        deliverSiteNo: getDeliverSiteNo(state),
        newSite: state.sites.newSite,
        newSiteModalShow: state.sites.newSiteModalShow
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
        },
        onNewSiteModalShow: () => {
            dispatch(showNewSiteModal());
        },
        onNewSiteModalHide: () => {
            dispatch(hideNewSiteModal());
        },
        onAddSite: (customerId, newSite) => {
            dispatch(addNewSite(customerId, newSite));
        },
        onNewSiteFieldUpdate: (field, value) => {
            dispatch(newSiteFieldUpdate(field, value));
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(SitesList)