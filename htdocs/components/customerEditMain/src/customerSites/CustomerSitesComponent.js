import configureStore from "./configureStore";
import React, {Component} from 'react'
import {Provider} from 'react-redux'
import SitesList from "./SitesList";
import {fetchContacts, fetchSites, initializeCustomer} from "./actions";
import ReactDOM from 'react-dom';

const store = configureStore();

export default class CustomerSitesComponent extends Component {
    constructor(props) {
        console.log(props);
        super(props);
        const {customerId, invoiceSiteNo, deliverSiteNo} = props;
        store.dispatch(initializeCustomer(customerId, invoiceSiteNo, deliverSiteNo))
        store.dispatch(fetchSites(customerId))
        store.dispatch(fetchContacts(customerId))
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