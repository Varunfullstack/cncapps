import './wdyr';
import React from 'react';
import ReactDOM from 'react-dom';
import {Provider} from "react-redux";
import configureStore from "./configureStore";
import {clearEditingSiteAction, fetchAllData, setEditSiteAction} from "./actions";
import ErrorHandler from "./helpers/ErrorHandlerComponent";
import {Tab, Tabs} from "react-bootstrap";
import CustomerEditMain from "./CustomerEditMain";
import CustomerProjectsComponent from "./CustomerProjectsComponent";
import PortalCustomerDocumentsComponent from "./PortalCustomerDocumentsComponent";
import SitesList from "./customerSites/SitesList";
import CustomerOrders from "./CustomerOrders";
import CustomerCRMComponent from "./CustomerCRMComponent";
import ContactsComponent from "./ContactsComponent";

const store = configureStore();

class CustomerEditComponent extends React.PureComponent {

    constructor(props) {
        super(props);
        const {customerId} = props;
        this.state = {
            loaded: true
        }
        store.dispatch(fetchAllData(customerId));
    }

    render() {
        const {customerId} = this.props;
        if (!this.state.loaded) {
            return '';

        }

        return (
            <Provider store={store}>
                <ErrorHandler/>
                <div className="container-fluid py-3">
                    <div className="row">
                        <div className="col-md-12">

                            <nav>
                                <Tabs defaultActiveKey="customer"
                                      onSelect={(eventKey, $event) => {
                                          if (eventKey === 'crm') {
                                              store.dispatch(setEditSiteAction(0));
                                          }
                                          if (eventKey === 'sites') {
                                              store.dispatch(clearEditingSiteAction());
                                          }
                                      }}
                                >
                                    <Tab eventKey="customer"
                                         title="Customer"
                                    >
                                        <CustomerEditMain customerId={customerId}/>
                                    </Tab>
                                    <Tab eventKey="projects"
                                         title="Projects"
                                    >
                                        <CustomerProjectsComponent customerId={customerId}/>
                                    </Tab>
                                    <Tab eventKey="portalDocuments"
                                         title="Portal Documents"
                                    >
                                        <PortalCustomerDocumentsComponent customerId={customerId}/>
                                    </Tab>
                                    <Tab eventKey="sites"
                                         title="Sites"
                                    >
                                        <SitesList customerId={customerId}/>
                                    </Tab>
                                    <Tab eventKey="orders"
                                         title="Orders"
                                    >
                                        <CustomerOrders customerId={customerId}/>
                                    </Tab>
                                    <Tab eventKey="contacts"
                                         title="Contacts"
                                    >
                                        <ContactsComponent/>
                                    </Tab>
                                    <Tab eventKey="crm"
                                         title="CRM"
                                    >
                                        <CustomerCRMComponent/>
                                    </Tab>
                                </Tabs>
                            </nav>
                            <div className="tab-content"
                                 id="nav-tabContent"
                            >
                                <div className="tab-pane fade show active"
                                     id="nav-home"
                                     role="tabpanel"
                                     aria-labelledby="nav-home-tab"
                                >

                                </div>
                                <div className="tab-pane fade customerAddProjects"
                                     id="nav-profile"
                                     role="tabpanel"
                                     aria-labelledby="nav-profile-tab"
                                >

                                </div>
                                <div className="tab-pane fade"
                                     id="nav-portal-documents-tab"
                                     role="tabpanel"
                                     aria-labelledby="nav-portal-documents-tab"
                                >

                                </div>
                                <div className="tab-pane fade"
                                     id="nav-sites"
                                     role="tabpanel"
                                     aria-labelledby="nav-sites-tab"
                                >

                                </div>
                                <div className="tab-pane fade"
                                     id="nav-orders"
                                     role="tabpanel"
                                     aria-labelledby="nav-orders-tab"
                                >

                                </div>
                                <div className="tab-pane fade"
                                     id="nav-crm"
                                     role="tabpanel"
                                     aria-labelledby="nav-crm-tab"
                                >

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Provider>
        )
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerEditMain');
    ReactDOM.render(React.createElement(CustomerEditComponent, {customerId: domContainer.dataset.customerId}), domContainer);
})
export default CustomerEditComponent;

