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
import CustomerNotesComponent from "./CustomerNotesComponent";
import {params} from "../utils/utils";
import ToolTip from "../shared/ToolTip";
import APICustomers from "../services/APICustomers";
import CustomerLog from "./CustomerLog";
import './../style.css';

class CustomerEditComponent extends MainComponent {
    tabs = [];
    TAB_CUSTOMER = 'customer';
    TAB_PROJECTS = 'projects';
    TAB_PORTAL_DOCUMENT = 'portal_document';
    TAB_SITES = 'sites';
    TAB_ORDERS = 'orders';
    TAB_CONTACTS = 'contacts';
    TAB_CRM = 'crm';
    TAB_NOTES = 'notes';
    api = new APICustomers();
    TAB_LOG = "log";

    constructor(props) {
        super(props);
        this.state = {
            customerId: params.get("customerID"),
            loaded: true,
            filter: {
                activeTab: params.get("activeTab") || this.TAB_CUSTOMER,
            },
            hasFolder: true,
            mode: 'new'
        }
    }

    componentDidMount() {
        const customerId = params.get("customerID");
        if (!customerId && params.get("customerId"))
            window.location = `Customer.php?action=dispEdit&customerID=${params.get("customerId")}`;
        const action = params.get("action");
        if (customerId && action != 'addCustomer') {
            this.tabs = [
                {id: this.TAB_CUSTOMER, title: "Customer", icon: null},
                {id: this.TAB_CONTACTS, title: "Contacts", icon: null},
                {id: this.TAB_SITES, title: "Sites", icon: null},
                {id: this.TAB_PROJECTS, title: "Projects", icon: null},
                {id: this.TAB_PORTAL_DOCUMENT, title: "Portal Documents", icon: null},
                {id: this.TAB_CRM, title: "CRM", icon: null},
                {id: this.TAB_NOTES, title: "Notes", icon: null},
                {id: this.TAB_ORDERS, title: "Orders", icon: null},
                {id: this.TAB_LOG, title: "Audit", icon: null},

            ];
            this.setState({mode: "edit", customerId});
            this.checkCustomerFolderExist();
        } else {
            this.tabs = [
                {id: this.TAB_CUSTOMER, title: "Customer", icon: null},
            ];
            this.setState({hasFolder: false});
        }

    }

    getTabsElement = () => {
        const {tabs} = this;
        if (!this.state.customerId)
            return null;
        return (
            <div key="tab" className="tab-container"
                 style={{flexWrap: "wrap", justifyContent: "flex-start", maxWidth: 1600}}
            >
                {
                    tabs.map((t, indx) => {
                        return <i key={indx} className={this.isActive(t.id) + " nowrap"}
                                  onClick={() => this.setActiveTab(t.id)}
                                  style={{width: 130}}>
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
    };
    getActiveTab = () => {
        const {filter, customerId} = this.state;
        switch (filter.activeTab) {
            case this.TAB_CUSTOMER:
                return <CustomerEditMain customerId={customerId}/>;
            case this.TAB_CONTACTS:
                return <CustomerContactsComponent customerId={customerId}/>;
            case this.TAB_CRM:
                return <CustomerCRMComponent customerId={customerId}/>;
            case this.TAB_ORDERS:
                return <CustomerOrdersComponent customerId={customerId}/>;
            case this.TAB_PORTAL_DOCUMENT:
                return <PortalCustomerDocumentsComponent customerId={customerId}/>;
            case this.TAB_PROJECTS:
                return <CustomerProjectsComponent customerId={customerId}/>;
            case this.TAB_SITES:
                return <CustomerSitesComponent customerId={customerId}/>;
            case this.TAB_NOTES:
                return <CustomerNotesComponent customerId={customerId}/>;
            case this.TAB_LOG:
                return <CustomerLog customerId={customerId}/>
        }
    }
    getActions = () => {
        const {mode} = this.state;
        if (mode == 'new')
            return null;
        return <div className="flex-row">
            <ToolTip title="Renewal Information" width={35}>
                <i className="fal fa-tasks fa-2x m-5 pointer icon"
                   onClick={() => window.open(`RenewalReport.php?action=produceReport&customerID=${this.state.customerId}`, "_blank")}/>
            </ToolTip>
            <ToolTip title="Third Party Contacts" width={35}>
                <i className="fal fa-users fa-2x m-5 pointer icon"
                   onClick={() => window.open(`ThirdPartyContact.php?action=list&customerID=${this.state.customerId}`, "_blank")}/>
            </ToolTip>
            {!this.state.hasFolder ?
                <ToolTip title="Create Customer Folder" width={35}>
                    <i className="fal fa-folder-plus fa-2x m-5 pointer icon" onClick={this.handleCreateFolder}/>
                </ToolTip> : null}
        </div>
    }
    checkCustomerFolderExist = () => {
        this.api.customerHasFolder(params.get("customerID"))
            .then(res => {
                if (res.state)
                    this.setState({hasFolder: true})
                else
                    this.setState({hasFolder: false})

            }, error => {
                this.setState({hasFolder: false})

            })
    }
    handleCreateFolder = () => {
        this.api.createCustomerFolder(this.state.customerId)
            .then(res => {
                this.setState({hasFolder: true})
            }, error => {
            })
    }

    render() {
        return <div>
            {this.getActions()}
            {this.getTabsElement()}
            {this.getActiveTab()}
        </div>
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerEditMain');
    ReactDOM.render(React.createElement(CustomerEditComponent, {customerId: domContainer.dataset.customerId}), domContainer);
})
export default CustomerEditComponent;

