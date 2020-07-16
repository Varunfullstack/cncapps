import configureStore from "./configureStore";
import React, {Component} from 'react'
import {Provider} from 'react-redux'
import SitesList from "./SitesList";
import {fetchContacts, fetchSites, initializeCustomer, savedSiteData, toggleVisibility} from "./actions";
import ReactDOM from 'react-dom';
import ToggleSwitch from "./dumbComponents/ToggleSwitch";

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
            Object.keys(state.sites.sitesPendingChanges).map(siteId => {
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
                        console.log('customer data saved');
                    })
            })
        )
    }

    render() {
        const {customerId} = this.props
        return (
            <Provider store={store}>
                <div>
                    <a href={`/Customer.php?action=addSite&customerID=${customerId}`}
                    >
                        Add Site
                    </a>
                    &nbsp;
                    <ToggleSwitch/>
                    <SitesList/>
                </div>
            </Provider>
        )
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerSites');
    ReactDOM.render(
        React.createElement(
            CustomerSitesComponent,
            {
                customerId: domContainer.dataset.customerId,
                invoiceSiteNo: domContainer.dataset.invoiceSiteNo,
                deliverSiteNo: domContainer.dataset.deliverSiteNo
            }
        ),
        domContainer
    );
});