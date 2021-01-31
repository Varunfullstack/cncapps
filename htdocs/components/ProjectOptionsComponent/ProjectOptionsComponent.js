import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import Toggle from "../shared/Toggle";
import ToolTip from "../shared/ToolTip";
import APIProjectOptions from "./services/APIProjectOptions";
import React from 'react';
import ReactDOM from 'react-dom';
import Spinner from "../shared/Spinner/Spinner";
import { ProjectStagesComponent } from "./subComponents/ProjectStagesComponent";
import {ProjectTypesComponent} from "./subComponents/ProjectTypesComponent";
import './../style.css';
import './ProjectOptionsComponent.css'; 

class ProjectOptionsComponent extends MainComponent {
    el = React.createElement;
    tabs = [];
    api = new APIProjectOptions();    
    TAB_STAGES=1;
    TAB_TYPES=2;
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            filter: {                
                activeTab: 1,
                
            },
            data: [],            
        };
        this.tabs = [
            {id: this.TAB_STAGES, title: "Stages", icon: null},  
            {id: this.TAB_TYPES, title: "Types", icon: null},            
          
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
        this.setState({filter, data: []});         
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
     
    setFilterValue = (property, value) => {
        const {filter} = this.state;
        filter[property] = value;
        this.setState({filter} );
    };
  
    getActiveTab=()=>{
        const {filter}=this.state;
        switch (filter.activeTab) {
            case this.TAB_STAGES:                
                return <ProjectStagesComponent></ProjectStagesComponent>    
            case this.TAB_TYPES:
            return <ProjectTypesComponent></ProjectTypesComponent>    
            default:
                break;
        }
    }
    render() {
        const {el} = this;
        return el("div", null,
            el(Spinner, {key: "spinner", show: this.state.showSpinner}),
            this.getAlert(),          
            this.getTabsElement(),     
            this.getActiveTab()        
        );
    }
}

export default ProjectOptionsComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainProjectOptions");
        ReactDOM.render(React.createElement(ProjectOptionsComponent), domContainer);
    }
)
