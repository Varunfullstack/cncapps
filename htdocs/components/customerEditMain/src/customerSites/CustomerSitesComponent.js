import configureStore from "./configureStore";
import React, {Component} from 'react'
import {Provider} from 'react-redux'
import SitesList from "./SitesList";
import {fetchContacts, fetchSites, initializeCustomer} from "./actions";


const store = configureStore();

export default class CustomerSitesComponent extends Component {
    constructor(props) {
        super(props);
        const {customerId, defaultInvoice, defaultDelivery} = props;
        store.dispatch(initializeCustomer(customerId, defaultInvoice, defaultDelivery))
        store.dispatch(fetchSites(customerId))
        store.dispatch(fetchContacts(customerId))
    }

    render() {
        const {customerId} = this.props
        return (
            <Provider store={store}>
                <div>
                    <a href={`/Customer.php?action=addSite&customerID=${customerId}`}
                       key: 'add-site-link'
                    >
                        Add Site
                    </a>
                    <SitesList></SitesList>
                </div>
            </Provider>
        )
    }
}
