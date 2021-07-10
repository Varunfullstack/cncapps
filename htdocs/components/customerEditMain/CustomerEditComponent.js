
import MainComponent from "../shared/MainComponent";
import React from 'react';
import ReactDOM from 'react-dom';
import CustomerEditMain from "./CustomerEditMain";
import CustomerProjectsComponent from "./CustomerProjectsComponent";
import PortalCustomerDocumentsComponent from "./PortalCustomerDocumentsComponent";
import CustomerSitesComponent from "./CustomerSitesComponent";
import CustomerContactsComponent from "./CustomerContactsComponent";
import CustomerOrdersComponent from "./CustomerOrdersComponent";
import CustomerCRMComponent from "./CustomerCRMComponent";

//import configureStore from "./configureStore";
/*
import './wdyr';
import {Provider} from "react-redux";
import {clearEditingSiteAction, fetchAllData, setEditSiteAction} from "./actions";
import ErrorHandler from "./helpers/ErrorHandlerComponent";
import {Tab, Tabs} from "react-bootstrap";


import SitesList from "./customerSites/SitesList";
import CustomerOrders from "./CustomerOrders";
import CustomerCRMComponent from "./CustomerCRMComponent";
import ContactsComponent from "./contacts/ContactsComponent";
*/
//const store = configureStore();
import './../style.css';
import { params } from "../utils/utils";
class CustomerEditComponent extends MainComponent {
    tabs = [];
    TAB_CUSTOMER='customer';    
    TAB_PROJECTS='projects';
    TAB_PORTAL_DOCUMENT='portal_document';
    TAB_SITES='sites';
    TAB_ORDERS='orders';
    TAB_CONTACTS='contacts';
    TAB_CRM='crm';

    constructor(props) {
        super(props);
         this.state = {
            customerId:null,
            loaded: true,
            filter: {                
                activeTab: this.TAB_CRM,                 
            },
        }
        this.tabs = [
            {id: this.TAB_CUSTOMER, title: "Customer", icon: null},
            {id: this.TAB_PROJECTS, title: "Projects", icon: null},
            {id: this.TAB_PORTAL_DOCUMENT, title: "Portal Documents", icon: null},
            {id: this.TAB_SITES, title: "Sites", icon: null},
            {id: this.TAB_ORDERS, title: "Orders", icon: null},
            {id: this.TAB_CONTACTS, title: "contacts", icon: null},
            {id: this.TAB_CRM, title: "CRM", icon: null},            
        ];
       // store.dispatch(fetchAllData(customerId));
    }
    componentDidMount() {
        const customerId=params.get("customerID");
        this.setState({customerId});
    }
    getTabsElement = () => {
        const {   tabs} = this;        
        return (
            <div key="tab" className="tab-container" style= {{flexWrap: "wrap", justifyContent: "flex-start", maxWidth: 1500}}
            >
                {
                    tabs.map((t,indx)=>{
                        return <i key={indx}   className= {this.isActive(t.id) + " nowrap"}
                        onClick={() => this.setActiveTab(t.id)}
                        style={{width: 150}}>
                            {t.title} 
                        </i>
                    })
                }
            </div>
        )
        
    };
    isActive = (code) => {
        const {filter} = this.state;
        if (filter.activeTab == code) return "active";
        else return "";
    };
    setActiveTab = (code) => {
        const {filter} = this.state;
        filter.activeTab = code;        
        this.setState({filter});
        //this.checkAutoReloading();
    };
    getActiveTab=()=>{
        const { filter,customerId } = this.state;
        if(customerId!=null)
        switch (filter.activeTab) {
          case this.TAB_CUSTOMER:
            return <CustomerEditMain customerId={customerId}/>;
          case this.TAB_CONTACTS:
            return <CustomerContactsComponent customerId={customerId}></CustomerContactsComponent>;
          case this.TAB_CRM:
            //return <label>CRM</label>;
            return   <CustomerCRMComponent customerId={customerId}/>;
          case this.TAB_ORDERS:
            return   <CustomerOrdersComponent customerId={customerId}/>;
          case this.TAB_PORTAL_DOCUMENT:
            return   <PortalCustomerDocumentsComponent customerId={customerId}/>;
          case this.TAB_PROJECTS:
            return <CustomerProjectsComponent customerId={customerId}></CustomerProjectsComponent>;
          case this.TAB_SITES:
            return <CustomerSitesComponent customerId={customerId}></CustomerSitesComponent>;
        }
    }
    getActions=()=>{
        return <div>
            <a className="m-3" target="_blank" href={`RenewalReport.php?action=produceReport&customerID=${this.state.customerId}`}>Renewal Information</a>
            <a className="m-3" target="_blank" href={`ThirdPartyContact.php?action=list&customerID=${this.state.customerId}`}>Third Party Contacts</a>
        </div>
    }
    render() {
        //const {customerId} = this.props;
        
        return <div>         
                {this.getActions()}  
                {this.getTabsElement()}
                {this.getActiveTab()}
               </div>
       /* if (!this.state.loaded) {
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
        )*/
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerEditMain');
    ReactDOM.render(React.createElement(CustomerEditComponent, {customerId: domContainer.dataset.customerId}), domContainer);
})
export default CustomerEditComponent;

