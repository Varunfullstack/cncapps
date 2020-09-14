import configureStore from "./configureStore";
import React, {Component} from 'react'
import {
    fetchContacts,
    fetchSites,
    initializeCustomer,
    savedCustomerData,
    savedSiteData,
    toggleVisibility
} from "./actions";

const store = configureStore();

export default class CustomerSitesComponent extends Component {
    constructor(props) {
        super(props);
        const {customerId, invoiceSiteNo, deliverSiteNo} = props;
        store.dispatch(initializeCustomer(customerId, invoiceSiteNo, deliverSiteNo))
        store.dispatch(fetchSites(customerId))
        store.dispatch(fetchContacts(customerId))
        document.customerSitesComponent = this;
        this.toggleVisibility = this.toggleVisibility.bind(this);
    }

    toggleVisibility() {
        store.dispatch(toggleVisibility);
    }

    saveSites() {
        const state = store.getState();
        return Promise.all(
            [
                ...Object.keys(state.sites.sitesPendingChanges)
                    .map(siteId => {
                        return fetch('?action=updateSite', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(state.sites.byIds[siteId])
                        })
                            .then(response => response.json())
                            .then(response => {
                                store.dispatch(savedSiteData(siteId));
                            })
                    }),
                Promise.resolve(store.dispatch(savedCustomerData()))
            ]
        )
    }

    render() {
        const {customerId} = this.props
        return (
            <div className="tab-pane fade show"
                 id="nav-sites"
                 role="tabpanel"
                 aria-labelledby="nav-sites-tab"
            >

                <div className="container-fluid">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Sites</h2>
                        </div>
                        <div className="col-md-12">
                            {/*<a href="{addSiteURL}">*/}
                            <button className="btn btn-primary mt-3 mb-3">Add Site</button>
                            {/*</a>*/}
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                            <div className="customerEditSites">
                                <div className="addSite">
                                    {/*<a href="{addSiteURL}">{addSiteText}</a>*/}
                                </div>
                                <div className="accordion"
                                     id="accordionExample1"
                                >
                                    <div className="site"
                                         style="width: 100%"
                                    >
                                        <div className="card">
                                            <div className="card-header"
                                                 id="heading{siteNo}"
                                                 style="width: 100%;"
                                            >
                                                <h5 className="mb-0">
                                                    {/*<button className="btn btn-link"*/}
                                                    {/*        type="button"*/}
                                                    {/*        data-toggle="collapse"*/}
                                                    {/*        data-target="#collapse{siteNo}"*/}
                                                    {/*        aria-expanded="false"*/}
                                                    {/*        aria-controls="collapse{siteNo}"*/}
                                                    {/*>*/}
                                                    {/*    {add1}*/}
                                                    {/*</button>*/}
                                                </h5>
                                            </div>
                                            <input type="hidden"
                                                   name="form[site][{customerID}{siteNo}][sageRef]"
                                                   value="{sageRef}"
                                                   size="3"
                                                   maxLength="6"
                                            />
                                            <input type="hidden"
                                                   name="form[site][{customerID}{siteNo}][siteNo]"
                                                   value="{siteNo}"
                                            />
                                            <input type="hidden"
                                                   name="form[site][{customerID}{siteNo}][customerID]"
                                                   value="{customerID}"
                                            />
                                            <input type="hidden"
                                                   name="form[site][{customerID}{siteNo}][debtorCode]"
                                                   value="{debtorCode}"
                                            />
                                            <div id="collapse{siteNo}"
                                                 className="collapse"
                                                 aria-labelledby="{siteNo}"
                                                 data-parent="#accordionExample1"
                                            >
                                                <div className="card-body">
                                                    <div className="row">
                                                        <div className="col-lg-4">
                                                            <div className="form-group">

                                                                <label>Site Address</label>
                                                                <input name="form[site][{customerID}{siteNo}][add1]"
                                                                       value="{add1}"
                                                                       size="35"
                                                                       maxLength="35"
                                                                       className="form-control mb-3"
                                                                />
                                                                <input name="form[site][{customerID}{siteNo}][add2]"
                                                                       value="{add2}"
                                                                       size="35"
                                                                       maxLength="35"
                                                                       className="form-control mb-3"

                                                                />
                                                                <input name="form[site][{customerID}{siteNo}][add3]"
                                                                       value="{add3}"
                                                                       size="35"
                                                                       maxLength="35"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>

                                                        <div className="col-lg-4">
                                                            <label htmlFor="town">Town</label>
                                                            <div className="form-group">
                                                                <input id="town"
                                                                       name="form[site][{customerID}{siteNo}][town]"
                                                                       value="{town}"
                                                                       size="25"
                                                                       maxLength="25"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-4">
                                                            <label htmlFor="country">Country</label>
                                                            <div className="form-group">
                                                                <input id="country"
                                                                       name="form[site][{customerID}{siteNo}][county]"
                                                                       value="{county}"
                                                                       size="25"
                                                                       maxLength="25"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-4">
                                                            <label htmlFor="postcode">Postcode</label>
                                                            <div className="form-group">
                                                                <input id="postcode"
                                                                       name="form[site][{customerID}{siteNo}][postcode]"
                                                                       value="{postcode}"
                                                                       size="15"
                                                                       maxLength="15"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-4">
                                                            <label htmlFor="phone">Phone</label>
                                                            <div className="form-group">
                                                                <input id="phone"
                                                                       name="form[site][{customerID}{siteNo}][phone]"
                                                                       value="{sitePhone}"
                                                                       size="20"
                                                                       maxLength="20"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-4">
                                                            <label htmlFor="form[site][{customerID}{siteNo}][maxTravelHours]">Max
                                                                Travel Hours</label>
                                                            <div className="form-group">
                                                                <input name="form[site][{customerID}{siteNo}][maxTravelHours]"
                                                                       id="form[site][{customerID}{siteNo}][maxTravelHours]"
                                                                       value="{maxTravelHours}"
                                                                       size="5"
                                                                       maxLength="5"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-2">
                                                            <label htmlFor="default-voices">Default
                                                                Invoice</label>
                                                            <div className="form-group form-inline">
                                                                <input id="default-voices"
                                                                       type="radio"
                                                                       name="form[customer][{customerID}][invoiceSiteNo]"
                                                                       value="{siteNo}"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-2">
                                                            <label htmlFor="default-delivery">Default
                                                                Delivery</label>
                                                            <div className="form-group form-inline">
                                                                <input id="default-delivery"
                                                                       type="radio"
                                                                       name="form[customer][{customerID}][deliverSiteNo]"
                                                                       value="{siteNo}"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-4">
                                                            <label htmlFor="invoice-contact">Invoice
                                                                Contact</label>
                                                            <div className="form-group">
                                                                <select
                                                                    name="form[site][{customerID}{siteNo}][invoiceContactID]"
                                                                    className="form-control"
                                                                >
                                                                    <option value="{selectInvoiceContactBlockContactID}"
                                                                    >
                                                                        {selectInvoiceContactBlockFirstName}
                                                                        {selectInvoiceContactBlockLastName}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-4">
                                                            <label htmlFor="default-contact">Delivery
                                                                Contact</label>
                                                            <div className="form-group">
                                                                <select
                                                                    id="default-contact"
                                                                    name="form[site][{customerID}{siteNo}][deliverContactID]"
                                                                    className="form-control"
                                                                >
                                                                    <option value="{selectDeliverContactBlockContactID}"
                                                                    >
                                                                        {selectDeliverContactBlockFirstName}
                                                                        {selectDeliverContactBlockLastName}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-2">
                                                            <label htmlFor="non-uk">Non
                                                                UK</label>
                                                            <div className="form-group form-inline">
                                                                <input type="checkbox"
                                                                       name="form[site][{customerID}{siteNo}][nonUKFlag]"
                                                                       title="Check to show this site is overseas and not in the UK"
                                                                       value="Y"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-2">
                                                            <label>Active</label>
                                                            <div className="form-group form-inline">
                                                                <input type="checkbox"
                                                                       name="form[site][{customerID}{siteNo}][activeFlag]"
                                                                       value="Y"
                                                                       className="form-control"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div className="col-lg-12">
                                                            <button type="button"
                                                                    className="btn btn-primary"
                                                                    onClick="addContact({siteNo})"
                                                            >Add
                                                                Contact
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        );
        //
        // return (
        //     <Provider store={store}>
        //         <div>
        //             <a href={`/Customer.php?action=addSite&customerID=${customerId}`}
        //             >
        //                 Add Site
        //             </a>
        //             &nbsp;
        //             <ToggleSwitch/>
        //             <SitesList/>
        //         </div>
        //     </Provider>
        // )
    }
}