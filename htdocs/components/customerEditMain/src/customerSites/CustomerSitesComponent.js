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
import {Provider} from "react-redux";
import SitesList from "./SitesList";

const store = configureStore();

export default class CustomerSitesComponent extends Component {
    constructor(props) {
        super(props);
        const {customerId, invoiceSiteNo, deliverSiteNo} = props;
        store.dispatch(initializeCustomer(customerId, invoiceSiteNo, deliverSiteNo))
        store.dispatch(fetchSites(customerId))
        store.dispatch(fetchContacts(customerId))
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
                            body: JSON.stringify({
                                ...state.sites.byIds[siteId],
                                deliverSiteNo: state.customer.deliverSiteNo,
                                invoiceSiteNo: state.customer.invoiceSiteNo
                            })
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
        return (
            <div className="tab-pane fade show"
                 id="nav-sites"
                 role="tabpanel"
                 aria-labelledby="nav-sites-tab"
            >
                <Provider store={store}>
                    <SitesList/>
                </Provider>
            </div>
        );
    }
}