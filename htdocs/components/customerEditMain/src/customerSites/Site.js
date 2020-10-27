import React from 'react'
import {createGetEditingSiteForSite, createGetSite, createGetSiteContacts, getCustomerId} from "../selectors";
import {connect} from "react-redux";
import {addContactToSite, deleteSite, saveSite, updateSiteField} from "../actions";
import {AccordionCollapse, Card} from "react-bootstrap";
import SiteAccordionCustomToggle from "./SiteAccordionCustomToggle";

class Site extends React.Component {

    constructor(props) {
        super(props);
        this.handleInputChange = this.handleInputChange.bind(this);
    }

    handleInputChange($event) {
        const target = $event.target;
        let value = target.value;
        const name = target.name;
        if (target.type === 'checkbox') {
            value = target.checked;
        }
        this.props.onUpdateSiteField(name, value);
    }

    checkDelete() {
        return confirm('Are you sure you want to delete this site?');
    }

    render() {
        const {site, contacts, editingSite} = this.props;
        // console.warn(`rerendered site ${site.siteNo} `);
        return (
            <Card className="site"
                  style={{width: "100%"}}
            >
                <SiteAccordionCustomToggle eventKey={`site${site.siteNo}`}
                                           siteId={site.siteNo}
                >
                    <Card.Header style={{width: "100%"}}
                    >
                        <h5 className="mb-0">

                            {site.address1 || ''}
                        </h5>
                    </Card.Header>
                </SiteAccordionCustomToggle>
                <AccordionCollapse eventKey={`site${site.siteNo}`}
                >
                    {!editingSite ? <span/> :
                        (
                            <Card.Body className="card-body">
                                <div className="row">
                                    <div className="col-lg-4">
                                        <div className="form-group">

                                            <label>Site Address</label>
                                            <input value={editingSite.address1 || ''}
                                                   onChange={($event) => this.handleInputChange($event)}
                                                   name="address1"
                                                   size="35"
                                                   maxLength="35"
                                                   className="form-control mb-3"
                                            />
                                            <input name="address2"
                                                   value={editingSite.address2 || ''}
                                                   onChange={($event) => this.handleInputChange($event)}
                                                   size="35"
                                                   maxLength="35"
                                                   className="form-control mb-3"
                                            />
                                            <input name="address3"
                                                   value={editingSite.address3 || ''}
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
                                                   value={editingSite.town || ''}
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
                                                   value={editingSite.county || ''}
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
                                                   value={editingSite.postcode || ''}
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
                                                   value={editingSite.phone || ''}
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
                                                   value={editingSite.maxTravelHours || ''}
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
                                                   value={editingSite.what3Words || ''}
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
                                                    value={editingSite.invoiceContact || ''}
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
                                                    value={editingSite.deliveryContact || ''}
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
                                                       checked={editingSite.nonUKFlag}
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
                                                       readOnly={editingSite.siteNo === 0}
                                                       disabled={editingSite.siteNo === 0}
                                                       onChange={($event) => this.handleInputChange($event)}
                                                       checked={editingSite.active}
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
                                            Add Contact (not working)
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
                            </Card.Body>
                        )
                    }

                </AccordionCollapse>

            </Card>
        )
    }
}

const makeMapStateToProps = () => {
    const getSiteContacts = createGetSiteContacts()
    const getSite = createGetSite();
    const getEditingSiteForSite = createGetEditingSiteForSite();
    return (state, props) => {
        return {
            site: getSite(state, props.siteNo),
            editingSite: getEditingSiteForSite(state, props.siteNo),
            contacts: getSiteContacts(state, props.siteNo),
            customerId: getCustomerId(state),
        }
    }
}


const mapDispatchToProps = dispatch => {
    return {
        addContactToSite: siteNo => {
            dispatch(addContactToSite(siteNo))
        },
        onUpdateSiteField: (field, value) => {
            dispatch(updateSiteField(field, value))
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
