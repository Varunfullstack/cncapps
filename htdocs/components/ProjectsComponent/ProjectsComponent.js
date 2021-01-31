
import React from 'react';
import ReactDOM from 'react-dom';
import MainComponent from "../shared/MainComponent";
import { params } from '../utils/utils';
import './../style.css';
import './ProjectsComponent.css';
import CurrentProjectsComponent from './subComponents/CurrentProjectsComponent';
import ProjectDetailsComponent from './subComponents/ProjectDetailsComponent';
class ProjectsComponent extends MainComponent {  
  tabs = [];
  TAB_CURRENT_PROJECTS=1;
  TAB_REPORTS=2;  
  constructor(props) {
    super(props);
    this.state = {
      activeTab:this.TAB_CURRENT_PROJECTS
    };
    this.tabs = [
      { id: this.TAB_CURRENT_PROJECTS, title: "Current Projects", icon: null,visible:true },      
      { id: this.TAB_REPORTS, title: "Reports", icon: null,visible:true },            
    ];
  }

  componentDidMount() { 
  }
  isActive = (code) => {
    const { activeTab } = this.state;
    if (activeTab == code) return "active";
    else return "";
  };
  setActiveTab = (activeTab) => {      
    this.setState({ activeTab  });    
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
      tabs.filter(t=>t.visible).map((t) => {
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
  getActiveTab=()=>{
    const {activeTab}=this.state;       
    switch (activeTab) {
        case this.TAB_CURRENT_PROJECTS :
          return <CurrentProjectsComponent></CurrentProjectsComponent>
        case this.TAB_REPORTS :
          return null;        
        default:
            return null;
    }
  }

  getActionElement=()=>{
    const action=params.get('action');
    const projectID=params.get('projectID');
    switch (action) {
      case 'add':
        return <ProjectDetailsComponent mode={action}  ></ProjectDetailsComponent>        
      case 'edit':
        return <ProjectDetailsComponent mode={action} projectID={projectID}></ProjectDetailsComponent>        
      default:
         return <div>
            {this.getTabsElement()}
            <div style={{marginTop:10}}>
              {this.getActiveTab()}
            </div>
          </div>;
    }
  }
  render() {
   return this.getActionElement();
  }
}
export default ProjectsComponent;

document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactMainProjects");
  ReactDOM.render(React.createElement(ProjectsComponent), domContainer);
});
