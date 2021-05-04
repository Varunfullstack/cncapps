import MainComponent from "../shared/MainComponent";
import CurrentActivityService from "../CurrentActivityReportComponent/services/CurrentActivityService";
import Table from "../shared/table/table";
import Toggle from "../shared/Toggle";
import ToolTip from "../shared/ToolTip";
 
import React from 'react';
import ReactDOM from 'react-dom';
import Spinner from "../shared/Spinner/Spinner";
import APIHeader from "../services/APIHeader";
import './../style.css';
import './HeaderComponent.css';

class HeaderComponent extends MainComponent {
  el = React.createElement;
  tabs = [];
  api = new APIHeader();
  apiCurrentActivityService = new CurrentActivityService();
  intervalRef;
  TAB_COMPANY = "COMPANY";
  TAB_BILLING = "BILLING";
  TAB_SERVICEDESK = "SERVICEDESK";
  TAB_GUI = "GUI";
  TAB_PORTAL = "PORTAL";
  TAB_BACKUPS = "BACKUPS";
  TAB_PROJECTS = "PROJECTS";
  TAB_ASSET_MANAGEMENT = "ASSET_MANAGEMENT";
  TAB_SECURITY = "SECURITY";
  TAB_ACCOUNT_MANAGEMENT = "ACCOUNT_MANAGEMENT";
  TAB_CUSTOMERCONTACTFLAGS = "CUSTOMER_CONTACT_FLAGS";

  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,      
      currentUser: null,
      data:{

      },
      filter:{
        activeTab:""
      }
    };
    this.tabs = [
      { id: this.TAB_COMPANY, title: "Company", icon: null },
      { id: this.TAB_BILLING, title: "Billing", icon: null },
      { id: this.TAB_SERVICEDESK, title: "ServiceDesk", icon: null },
      { id: this.TAB_GUI, title: "GUI", icon: null },
      { id: this.TAB_PORTAL, title: "Portal", icon: null },
      { id: this.TAB_BACKUPS, title: "Backups", icon: null },
      { id: this.TAB_PROJECTS, title: "Projects", icon: null },
      { id: this.TAB_ASSET_MANAGEMENT, title: "Asset Management", icon: null },
      { id: this.TAB_SECURITY, title: "Security", icon: null },
      { id: this.TAB_ACCOUNT_MANAGEMENT, title: "Account Management", icon: null },       
      { id: this.TAB_CUSTOMERCONTACTFLAGS, title: "Customer Contact Flags", icon: null },       
    ];
  }

  componentDidMount() {
 
  }
 
  isActive = (code) => {
    const { filter } = this.state;
    if (filter.activeTab == code) return "active";
    else return "";
  };
  setActiveTab = (code) => {
    const { filter } = this.state;
    filter.activeTab = code;
    this.saveFilter(filter);
    this.setState({ filter, queueData: [] });
    this.checkAutoReloading();
  };
 
  getTabsElement = () => {
    const { el, tabs } = this;
    const { currentUser } = this.state;
    return el(
      "div",
      {
        key: "tab",
        className: "tab-container",
        style: {
          flexWrap: "wrap",
          justifyContent: "flex-start",
          maxWidth: 1500,
        },
      },
      tabs
        .filter(
          (tab) =>
            !tab.requiredPermission ||
            (currentUser && currentUser[tab.requiredPermission])
        )
        .map((t) => {
          return el(
            "i",
            {
              key: t.id,
              className: this.isActive(t.id) + " nowrap",
              onClick: () => this.setActiveTab(t.id),
              style: { width: 200 },
            },
            t.title,
            t.icon
              ? el("span", {
                  className: t.icon,
                  style: {
                    fontSize: "12px",
                    marginTop: "-12px",
                    marginLeft: "-5px",
                    position: "absolute",
                    color: "#000",
                  },
                })
              : null
          );
        })
    );
  };
  loadFilterFromStorage = () => {
    let filter = localStorage.getItem("SDManagerDashboardFilter");
    if (filter) filter = JSON.parse(filter);
    else filter = this.state.filter;
    this.setState({ filter }, () => {
      this.loadTab(filter.activeTab);
      this.checkAutoReloading();
    });
  };
  setFilterValue = (property, value) => {
    const { filter } = this.state;
    filter[property] = value;
    this.setState({ filter }, () => this.saveFilter(filter));
  };

  saveFilter(filter) {
    localStorage.setItem("Header", JSON.stringify(filter));
    this.loadTab(filter.activeTab);
  }
  render() {
    const { el } = this;
    return el(
      "div",
      null,
      el(Spinner, { key: "spinner", show: this.state.showSpinner }),
      this.getAlert(),
      
      
    );
  }
}

export default HeaderComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainHeaderComponent");
        ReactDOM.render(React.createElement(HeaderComponent), domContainer);
    }
)
