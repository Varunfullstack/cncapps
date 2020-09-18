import React from 'react'

export default class Site extends React.Component {

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
        this.props.updateSite(this.props.site.siteNo, {[name]: value});
    }

    checkDelete() {
        return confirm('Are you sure you want to delete this site?');
    }

    render() {
        return <div className="site"
                    style={{width: "100%"}}
        >
            <div className="card">
                <div className="card-header"
                     id={`heading${this.props.site.siteNo}`}
                     style={{width: "100%"}}
                >
                    <h5 className="mb-0">
                        <button className="btn btn-link"
                                type="button"
                                data-toggle="collapse"
                                data-target={`#collapse${this.props.site.siteNo}`}
                                aria-expanded="false"
                                aria-controls={`collapse${this.props.site.siteNo}`}
                        >
                            {this.props.site.address1 || ''}
                        </button>
                    </h5>
                </div>
                <div id={`collapse${this.props.site.siteNo}`}
                     className="collapse"
                     aria-labelledby={this.props.site.siteNo}
                     data-parent="#accordionExample1"
                >
                    <div className="card-body">
                        <div className="row">
                            <div className="col-lg-4">
                                <div className="form-group">

                                    <label>Site Address</label>
                                    <input value={this.props.site.address1 || ''}
                                           onChange={($event) => this.handleInputChange($event)}
                                           name="address1"
                                           size="35"
                                           maxLength="35"
                                           className="form-control mb-3"
                                    />
                                    <input name="address2"
                                           value={this.props.site.address2 || ''}
                                           onChange={($event) => this.handleInputChange($event)}
                                           size="35"
                                           maxLength="35"
                                           className="form-control mb-3"
                                    />
                                    <input name="address3"
                                           value={this.props.site.address3 || ''}
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
                                           value={this.props.site.town || ''}
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
                                           value={this.props.site.county || ''}
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
                                           value={this.props.site.postcode || ''}
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
                                           value={this.props.site.phone || ''}
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
                                           value={this.props.site.maxTravelHours || ''}
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
                                           value={this.props.site.what3Words || ''}
                                           onChange={($event) => this.handleInputChange($event)}
                                           size="5"
                                           maxLength="5"
                                           className="form-control input-sm"
                                    />
                                </div>
                            </div>
                            <div className="col-lg-2">
                                <label htmlFor="invoiceSiteNo">
                                    Default Invoice
                                </label>
                                <div className="form-group form-inline">
                                    <input type="radio"
                                           name="invoiceSiteNo"
                                           value={this.props.site.siteNo || ''}
                                           checked={+this.props.invoiceSiteNo === +this.props.site.siteNo}
                                           onChange={($event) => this.props.changeInvoiceSiteNo($event.target.value)}
                                           className="form-control"
                                    />
                                </div>
                            </div>
                            <div className="col-lg-2">
                                <label htmlFor="deliverSiteNo">
                                    Default Delivery
                                </label>
                                <div className="form-group form-inline">
                                    <input type="radio"
                                           name="deliverSiteNo"
                                           value={this.props.site.siteNo || ''}
                                           checked={+this.props.deliverSiteNo === +this.props.site.siteNo}
                                           onChange={($event) => this.props.changedDeliverSiteNo($event.target.value)}
                                           className="form-control"
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
                                            value={this.props.site.invoiceContact || ''}
                                            onChange={($event) => this.handleInputChange($event)}
                                    >
                                        {
                                            this.props.contacts.map(c => {
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
                                            value={this.props.site.deliveryContact || ''}
                                            onChange={($event) => this.handleInputChange($event)}
                                    >
                                        {
                                            this.props.contacts.map(c => {
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
                                               checked={this.props.site.nonUKFlag}
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
                                               checked={this.props.site.active}
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
                                        onClick={() => this.props.saveSite(this.props.site, this.props.deliverSiteNo, this.props.invoiceSiteNo)}
                                >
                                    Save Changes
                                </button>
                                {
                                    this.props.site.canDelete ?
                                        (
                                            <button type="button"
                                                    className="btn btn-danger"
                                                    onClick={() => this.props.deleteSite(this.props.customerId, this.props.site.siteNo)}
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
    }
}
