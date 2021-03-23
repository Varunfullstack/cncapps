import MainComponent from "../shared/MainComponent";
import APICustomerInfo from "./services/APICustomerInfo";
import React from 'react';
import ReactDOM from 'react-dom';
import Spinner from "../shared/Spinner/Spinner";

import './../style.css';
import TwentyFourHoursSupportComponent from "./subComponents/TwentyFourHoursSupportComponent";
import SpecialAttentionComponent from "./subComponents/SpecialAttentionComponent";
import ContactAuditComponent from "./subComponents/ContactAuditComponent";
//import '/CustomerInfoComponent.css';


 
class CustomerInfoComponent extends MainComponent {
  el = React.createElement;
  tabs = [];
  api = new APICustomerInfo();
  TAB_24HOUR_SUPPORT = 1;
  TAB_SPECIAL_ATTENTION = 2;
  TAB_CONTACT_AUDIT = 3;

  constructor(props) {
    super(props);

    this.state = {
      ...this.state,
      showSpinner: false,
      filter: {
        activeTab: 0,
      },
      data: [],
    };

    this.tabs = [
      { id: this.TAB_24HOUR_SUPPORT, title: "24 Hour Support", icon: null },
      { id: this.TAB_SPECIAL_ATTENTION, title: "Special Attention", icon: null },
      { id: this.TAB_CONTACT_AUDIT, title: "Contact Audit", icon: null}
    ];
  }

  componentDidMount() {
    this.loadFilterFromStorage();    
  }
  isActive = (code) => {
    const { filter } = this.state;
    if (filter.activeTab == code) return "active";
    else 
        return "";
  };
  setActiveTab = (code) => {
    const { filter } = this.state;
    filter.activeTab = code;
    this.saveFilter(filter);
    this.setState({ filter });
  };

  getTabsElement = () => {
    const { el, tabs } = this;
    return el(
      "div",
      {
        key: "tab",
        className: "tab-container",
        style: {
          flexWrap: "wrap",
          justifyContent: "flex-start",
          maxWidth: 1300,
        },
      },
      tabs.map((t) => {
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
    let filter = localStorage.getItem("CustomerInfo");
    if (filter) filter = JSON.parse(filter);
    else filter = this.state.filter;
    this.setState({ filter });
  };

  setFilterValue = (property, value) => {
    const { filter } = this.state;
    filter[property] = value;
    this.setState({ filter }, () => this.saveFilter(filter));
  };

  saveFilter(filter) {
    localStorage.setItem("CustomerInfo", JSON.stringify(filter));
  
  }

   
  getActiveTab=()=>{
      const {filter}=this.state;
      switch(filter.activeTab)
      {
          case this.TAB_24HOUR_SUPPORT:
            return <TwentyFourHoursSupportComponent></TwentyFourHoursSupportComponent>;
          case this.TAB_SPECIAL_ATTENTION:
              return <SpecialAttentionComponent></SpecialAttentionComponent>;
          case this.TAB_CONTACT_AUDIT:
              return <ContactAuditComponent></ContactAuditComponent>
          default :
          return null;
      }
  }
  render() {
    const { el } = this;
    return el(
      "div",
      null,
      el(Spinner, { key: "spinner", show: this.state.showSpinner }),
      this.getAlert(),            
      this.getTabsElement(),
      this.getActiveTab()
    );
  }
}

export default CustomerInfoComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainCustomerInfo");
        ReactDOM.render(React.createElement(CustomerInfoComponent), domContainer);
    }
)
