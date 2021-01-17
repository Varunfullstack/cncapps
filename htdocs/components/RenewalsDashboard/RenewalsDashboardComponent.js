import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import ToolTip from "../shared/ToolTip";
import APIRenewals from "./services/APIRenewals";
import React from 'react';
import ReactDOM from 'react-dom';

import './../style.css';
import './RenewalsDashboardComponent.css';
import Spinner from "../shared/Spinner/Spinner";

class RenewalsDashboardComponent extends MainComponent {
    //el = React.createElement;
    tabs = [];
    api = new APIRenewals();
    apiCurrentActivityService = new CurrentActivityService();
    TAB_RENEWAL=1;
    TAB_CONTRACT=2;
    TAB_INTERNET=3;
    TAB_DOMAIN=4;
    TAB_HOSTING=5;
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,            
        };
        this.tabs = [
            {id: this.TAB_RENEWAL, title: "Renewal", icon: null},
            {id: this.TAB_CONTRACT, title: "Contract", icon: null},
            {id: this.TAB_INTERNET, title: "Internet", icon: null},
            {id: this.TAB_DOMAIN, title: "Domain", icon: null},
            {id: this.TAB_HOSTING, title: "Hosting", icon: null},            
        ];
    }

    componentDidMount() {        
    }

    isActive = (code) => {
        const {filter} = this.state;
        if (filter.activeTab == code) return "active";
        else return "";
    };
    setActiveTab = (code) => {
        const {filter} = this.state;
        filter.activeTab = code;
        this.setState({filter, queueData: []});
        //this.checkAutoReloading();
    };

    checkAutoReloading() {
        if (this.intervalRef) {
            clearInterval(this.intervalRef);
        }
        this.intervalRef = setInterval(() => {
            const {filter} = this.state;
            this.loadTab(filter.activeTab);
        }, AUTO_RELOAD_TIME)
    }

    getTabsElement = () => {
        const {el, tabs} = this;
        return el(
            "div",
            {
                key: "tab",
                className: "tab-container",
                style: {flexWrap: "wrap", justifyContent: "flex-start", maxWidth: 1300}
            },
            tabs.map((t) => {
                return el(
                    "i",
                    {
                        key: t.id,
                        className: this.isActive(t.id) + " nowrap",
                        onClick: () => this.setActiveTab(t.id),
                        style: {width: 200}
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
    
    loadTab = (id) => {
        if ([1, 2, 3, CRITICAL_SERVICE_REQUESTS, 5, 6, 7, 8, CUSTOMER_TAB, HELD_FOR_QA_TAB].indexOf(id) >= 0
        ) {
            this.loadAllocatedUsers();
            const {filter} = this.state;
            this.setState({showSpinner: true});
            this.api.getQueue(id, filter)
                .then((queueData) => {
                    this.setState({queueData, showSpinner: false})
                });
        } else
            return [];

    };
    
    render() {
        const {el} = this;
        return el("div", null,
            el(Spinner, {key: "spinner", show: this.state.showSpinner}),
            this.getAlert(), 
            this.getTabsElement(),            
        );
    }
}

export default RenewalsDashboardComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainSDManagerDashboard");
        ReactDOM.render(React.createElement(RenewalsDashboardComponent), domContainer);
    }
)
