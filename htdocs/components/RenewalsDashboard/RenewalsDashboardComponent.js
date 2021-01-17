import MainComponent from "../shared/MainComponent";
import APIRenewals from "./services/APIRenewals";
import React from 'react';
import ReactDOM from 'react-dom';

import './../style.css';
//import '../shared/table/table.css';
import './RenewalsDashboardComponent.css';
import Spinner from "../shared/Spinner/Spinner";
import { RenewalComponent } from "./subComponents/RenewalComponent";
import { RenContractComponent } from "./subComponents/RenContractComponent";
import { RenBroadbandComponent } from "./subComponents/RenBroadbandComponent";
import {RenDomainComponent } from "./subComponents/RenDomainComponent";
import { RenHostingComponent } from "./subComponents/RenHostingComponent";
class RenewalsDashboardComponent extends MainComponent {
    //el = React.createElement;
    tabs = [];
    api = new APIRenewals();
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
            filter:{
                activeTab:this.TAB_RENEWAL
            },
            data:[]
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
        const {filter}   =this.state;
        this.loadTab(filter.activeTab);
    }

    isActive = (code) => {
        const {filter} = this.state;
        if (filter.activeTab == code) return "active";
        else return "";
    };
    setActiveTab = (code) => {
        const {filter} = this.state;
        filter.activeTab = code;
        this.setState({filter});        
        this.loadTab(filter.activeTab);
    };

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
        console.log(id);
        this.setState({showSpinner:true});
        switch(id)
        {
            case this.TAB_RENEWAL:
                this.api.getRenewals().then(data=>{
                    console.log(data);
                    this.setState({showSpinner:false,data});
                });
                break;
            case this.TAB_CONTRACT:
                this.api.getRenContract().then(data=>{
                    console.log(data);
                    this.setState({showSpinner:false,data});
                });
                break;
            case this.TAB_INTERNET:
                this.api.getRenBroadband().then(data=>{
                    console.log(data);
                    this.setState({showSpinner:false,data});
                });
                break;
            case this.TAB_DOMAIN:
                this.api.getRenDomain().then(data=>{
                    console.log(data);
                    this.setState({showSpinner:false,data});
                });
                break;
            case this.TAB_HOSTING:
                this.api.getRenHosting().then(data=>{
                    console.log(data);
                    this.setState({showSpinner:false,data});
                });
                break;
            default:
                this.setState({showSpinner:false});

        }

    };
    
    getActiveElement=()=>{
        const {filter,data}=this.state;
        switch(filter.activeTab)
        {
            case this.TAB_RENEWAL:
                return <RenewalComponent data={data}></RenewalComponent>;  
            case this.TAB_CONTRACT:      
                return <RenContractComponent data={data}></RenContractComponent>;  
            case this.TAB_INTERNET:
                return <RenBroadbandComponent data={data}></RenBroadbandComponent>;
            case this.TAB_DOMAIN:
                return <RenDomainComponent data={data}></RenDomainComponent>;
            case this.TAB_HOSTING:
                return <RenHostingComponent data={data}></RenHostingComponent>;
        }
    }
    render() {
        const {el} = this;
        return el("div", null,
            el(Spinner, {key: "spinner", show: this.state.showSpinner}),
            this.getAlert(), 
            this.getTabsElement(),            
            this.getActiveElement()
        );
    }
}

export default RenewalsDashboardComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainRenewalsDashboard");
        ReactDOM.render(React.createElement(RenewalsDashboardComponent), domContainer);
    }
)
