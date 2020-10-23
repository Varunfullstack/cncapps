import React from 'react'
import {createGetSite, createGetSiteContacts, getCustomerId} from "../selectors";
import {connect} from "react-redux";
import {addContactToSite, deleteSite, saveSite, updateSite} from "../actions";

class Site extends React.Component {

    constructor(props) {
        super(props);
        this.handleInputChange = this.handleInputChange.bind(this);
    }

    handleInputChange($event) {
        const {site} = this.props;
        const target = $event.target;
        let value = target.value;
        const name = target.name;
        if (target.type === 'checkbox') {
            value = target.checked;
        }
        this.props.updateSite(site.siteNo, {[name]: value});
    }

    checkDelete() {
        return confirm('Are you sure you want to delete this site?');
    }

    render() {
        const {site, contacts} = this.props;

        return (
            <div className="site"
                 style={{width: "100%"}}
            >

                <div className="card">
                    <div className="card-header"
                         id={`heading${site.siteNo}`}
                         style={{width: "100%"}}
                    >
                        <h5 className="mb-0">
                            <button className="btn btn-link"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target={`#collapse${site.siteNo}`}
                                    aria-expanded="false"
                                    aria-controls={`collapse${site.siteNo}`}
                            >
                                {site.address1 || ''}
                            </button>
                        </h5>
                    </div>
                    <div id={`collapse${site.siteNo}`}
                         className="collapse"
                         aria-labelledby={site.siteNo}
                         data-parent="#accordionExample1"
                    >
                        <div className="card-body">
                            <div className="row">
                                <div className="col-lg-4">
                                    <div className="form-group">

                                        <label>Site Address</label>
                                        <input value={site.address1 || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               name="address1"
                                               size="35"
                                               maxLength="35"
                                               className="form-control mb-3"
                                        />
                                        <input name="address2"
                                               value={site.address2 || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               size="35"
                                               maxLength="35"
                                               className="form-control mb-3"
                                        />
                                        <input name="address3"
                                               value={site.address3 || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               size="35"
                                               maxLength="35"
                                               className="form-control input-sm"
                                        />
                                    </div>
                                </div>

                                <div className="col-lg-4">
                                    <label htmlFor="town">Town</label>
                                    <div className="form-group">
                                        <input name="town"
                                               value={site.town || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               size="25"
                                               maxLength="25"
                                               className="form-control input-sm"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="country">County</label>
                                    <div className="form-group">
                                        <input name="county"
                                               value={site.county || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               size="25"
                                               maxLength="25"
                                               className="form-control input-sm"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="postcode">Postcode</label>
                                    <div className="form-group">
                                        <input name="postcode"
                                               value={site.postcode || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               size="15"
                                               maxLength="15"
                                               className="form-control input-sm"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="phone">Phone</label>
                                    <div className="form-group">
                                        <input name="phone"
                                               value={site.phone || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               size="20"
                                               maxLength="20"
                                               className="form-control input-sm telephoneValidator"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="maxTravelHours">
                                        Max Travel Hours
                                    </label>
                                    <div className="form-group">
                                        <input name="maxTravelHours"
                                               value={site.maxTravelHours || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               type="number"
                                               size="5"
                                               maxLength="5"
                                               className="form-control input-sm"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="what3Words">What3Words</label>
                                    <div className="form-group">
                                        <input name="what3Words"
                                               id="What3Words"
                                               value={site.what3Words || ''}
                                               onChange={($event) => this.handleInputChange($event)}
                                               size="5"
                                               maxLength="5"
                                               className="form-control input-sm"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="invoiceContact">
                                        Invoice Contact
                                    </label>
                                    <div className="form-group">
                                        <select name="invoiceContact"
                                                className="form-control input-sm"
                                                value={site.invoiceContact || ''}
                                                onChange={($event) => this.handleInputChange($event)}
                                        >
                                            {
                                                contacts.map(c => {
                                                    return (<option key={c.id}
                                                                    value={c.id}
                                                    >
                                                        {`${c.firstName} ${c.lastName}`}
                                                    </option>)
                                                })
                                            }
                                        </select>
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="default-contact">
                                        Delivery Contact
                                    </label>
                                    <div className="form-group">
                                        <select name="deliveryContact"
                                                className="form-control input-sm"
                                                value={site.deliveryContact || ''}
                                                onChange={($event) => this.handleInputChange($event)}
                                        >
                                            {
                                                contacts.map(c => {
                                                    return (<option key={c.id}
                                                                    value={c.id}
                                                    >
                                                        {`${c.firstName} ${c.lastName}`}
                                                    </option>)
                                                })
                                            }
                                        </select>
                                    </div>
                                </div>
                                <div className="col-lg-2">
                                    <label>Non UK</label>
                                    <div className="form-group form-inline">
                                        <label className="switch">
                                            <input type="checkbox"
                                                   name="nonUKFlag"
                                                   onChange={($event) => this.handleInputChange($event)}
                                                   title="Check to show this site is overseas and not in the UK"
                                                   value="1"
                                                   checked={site.nonUKFlag}
                                                   className="form-control"
                                            />
                                            <span className="slider round"/>
                                        </label>
                                    </div>
                                </div>
                                <div className="col-lg-2">
                                    <label>Active</label>
                                    <div className="form-group form-inline">
                                        <label className="switch">
                                            <input type="checkbox"
                                                   name="active"
                                                   onChange={($event) => this.handleInputChange($event)}
                                                   checked={site.active}
                                                   value="1"
                                                   className="tick_field"
                                            />
                                            <span className="slider round"/>
                                        </label>
                                    </div>
                                </div>
                                <div className="col-lg-12">
                                    <button type="button"
                                            className="btn btn-primary"
                                        // onClick="addContact({siteNo})"
                                    >
                                        Add Contact
                                    </button>
                                    <button type="button"
                                            className="btn btn-primary"
                                            onClick={() => this.props.saveSite(site, this.props.deliverSiteNo, this.props.invoiceSiteNo)}
                                    >
                                        Save Changes
                                    </button>
                                    {
                                        site.canDelete ?
                                            (
                                                <button type="button"
                                                        className="btn btn-danger"
                                                        onClick={() => this.checkDelete() && this.props.deleteSite(this.props.customerId, site.siteNo)}
                                                >
                                                    Delete Site
                                                </button>
                                            ) : null

                                    }

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

const makeMapStateToProps = () => {
    const getSiteContacts = createGetSiteContacts()
    const getSite = createGetSite();
    return (state, props) => {
        return {
            site: getSite(state, props),
            contacts: getSiteContacts(state, props),
            customerId: getCustomerId(state),
        }
    }
}


const mapDispatchToProps = dispatch => {
    return {
        addContactToSite: siteNo => {
            dispatch(addContactToSite(siteNo))
        },
        updateSite: (siteNo, data) => {
            dispatch(updateSite(siteNo, data))
        },
        saveSite: (site) => {
            dispatch(saveSite(site))
        },
        deleteSite: (customerId, siteNo) => {
            dispatch(deleteSite(customerId, siteNo))
        },
    }
}

export default connect(
    makeMapStateToProps,
    mapDispatchToProps
)(Site)
