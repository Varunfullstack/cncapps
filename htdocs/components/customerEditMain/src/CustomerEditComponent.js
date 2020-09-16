import Skeleton from "react-loading-skeleton";
import React from 'react';
import ReactDOM from 'react-dom';
import CustomerEditMain from "./CustomerEditMain";
import CustomerProjectsComponent from "./CustomerProjectsComponent";
import CustomerPortalDocumentsComponent from "./CustomerPortalDocumentsComponent";
import CustomerSitesComponent from "./customerSites/CustomerSitesComponent";
import CustomerOrders from "./CustomerOrders";

class CustomerEditComponent extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            loaded: true
        }
    }

    render() {
        const {customerId} = this.props;
        if (!this.state.loaded) {
            return (
                <Skeleton>
                </Skeleton>
            )
        }

        return (
            <div id="main">
                <div className="container-fluid">
                    <div className="row">
                        <div className="col-md-12">
                            <div className="card">
                                <div className="card-body">
                                    <nav>
                                        <div className="nav nav-tabs"
                                             id="nav-tab"
                                             role="tablist"
                                        >
                                            <a className="nav-item nav-link active"
                                               id="nav-home-tab"
                                               data-toggle="tab"
                                               href="#nav-home"
                                               role="tab"
                                               aria-controls="nav-home"
                                               aria-selected="true"
                                            >Customer</a>
                                            <a className="nav-item nav-link"
                                               id="nav-profile-tab"
                                               data-toggle="tab"
                                               href="#nav-profile"
                                               role="tab"
                                               aria-controls="nav-profile"
                                               aria-selected="false"
                                            >Projects</a>
                                            <a className="nav-item nav-link"
                                               id="nav-contact-tab"
                                               data-toggle="tab"
                                               href="#nav-portal-documents-tab"
                                               role="tab"
                                               aria-controls="nav-portal-documents-tab"
                                               aria-selected="false"
                                            >Portal Documents</a>
                                            <a className="nav-item nav-link"
                                               id="nav-sites-tab"
                                               data-toggle="tab"
                                               href="#nav-sites"
                                               role="tab"
                                               aria-controls="nav-sites"
                                               aria-selected="false"
                                            >Sites</a>
                                            <a className="nav-item nav-link"
                                               id="nav-orders-tab"
                                               data-toggle="tab"
                                               href="#nav-orders"
                                               role="tab"
                                               aria-controls="nav-orders"
                                               aria-selected="false"
                                            >Orders</a>
                                        </div>
                                    </nav>
                                    <div className="tab-content"
                                         id="nav-tabContent"
                                    >
                                        <CustomerEditMain customerId={customerId}/>
                                        <CustomerProjectsComponent customerId={customerId}/>
                                        <CustomerPortalDocumentsComponent customerId={customerId}/>
                                        <CustomerSitesComponent customerId={customerId}/>
                                        <CustomerOrders customerId={customerId}/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerEditMain');
    ReactDOM.render(React.createElement(CustomerEditComponent, {customerId: domContainer.dataset.customerId}), domContainer);
})
export default CustomerEditComponent;

