import React from 'react';
import ReactDOM from 'react-dom';
import MainComponent from "../shared/MainComponent";
import Toggle from '../shared/Toggle';
import {params} from '../utils/utils';
import './../style.css';
import './ProjectsComponent.css';
import APIProjects from './services/APIProjects';
import CurrentProjectsComponent from './subComponents/CurrentProjectsComponent';
import ProjectDetailsComponent from './subComponents/ProjectDetailsComponent';
import ProjectsCalendarComponent from './subComponents/ProjectsCalendarComponent';

class ProjectsComponent extends MainComponent {
    api = new APIProjects();
    tabs = [];
    TAB_CURRENT_PROJECTS = 1;
    TAB_REPORTS = 2;
    TAB_CALENDAR = 3;

    constructor(props) {
        super(props);
        this.state = {
            activeTab: this.TAB_CURRENT_PROJECTS,
            totalProjects: 0,
            summary: '',
            projectsSummary: []
        };
        this.tabs = [
            {id: this.TAB_CURRENT_PROJECTS, title: "Current Projects", icon: null, visible: true},
            {id: this.TAB_REPORTS, title: "Reports", icon: null, visible: true},
            {id: this.TAB_CALENDAR, title: "Calendar", icon: null, visible: true},

        ];
    }

    componentDidMount() {
        this.api.getPRojectsSummary().then(projects => {
            projects.map(p => {
                p.filter = true;
                return p;
            });
            return projects;
        }).then(projectsSummary => this.setState({projectsSummary}))
    }

    isActive = (code) => {
        const {activeTab} = this.state;
        if (activeTab == code) return "active";
        else return "";
    };
    setActiveTab = (activeTab) => {
        this.setState({activeTab});
    };
    getTabsElement = () => {
        const {el, tabs} = this;
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
            tabs.filter(t => t.visible).map((t) => {
                return el(
                    "i",
                    {
                        key: t.id,
                        className: this.isActive(t.id) + " nowrap",
                        onClick: () => this.setActiveTab(t.id),
                        style: {width: 200},
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
    getActiveTab = () => {
        const {activeTab, projectsSummary} = this.state;


        switch (activeTab) {
            case this.TAB_CURRENT_PROJECTS :
                return <CurrentProjectsComponent projectsSummary={projectsSummary}></CurrentProjectsComponent>
            case this.TAB_REPORTS :
                return <iframe style={{
                    border: 0,
                    overflow: "hidden",
                    overflowX: "hidden",
                    overflowY: "hidden",
                    minHeight: 500,
                    minWidth: 200,
                    //position: "absolute",
                    top: 40,
                    left: 0,
                    right: 0,
                    bottom: 0
                }}
                               width="100%"
                               height="100%"
                               src={`Reports.php?hideMenu&&categoryID=1&&hideCategories=true&time=` + Date.now()}
                ></iframe>;
            case this.TAB_CALENDAR:
                return <ProjectsCalendarComponent></ProjectsCalendarComponent>
            default:
                return null;
        }
    }
    setProjectsSummaryElement = () => {
        const {projectsSummary} = this.state;
        const total = projectsSummary
            .map((p) => p.total)
            .reduce((prev, cur) => prev + cur, 0);
        return (
            <div className="mini-tabs">
                <div className="mini-tab-item">
                    <strong>{"Total :" + total}</strong>
                </div>
                {projectsSummary
                    .filter((p) => p.name != null)
                    .map((p) => (
                        <div key={p.name}>
                            <div className="mini-tab-item">
                                {p.name}
                                <strong className="mr-5">{" : " + p.total}</strong>
                                <Toggle checked={p.filter}
                                        onChange={() => this.toggleSummaryItem(p)}
                                ></Toggle>
                            </div>

                        </div>
                    ))}
            </div>
        );
    }
    toggleSummaryItem = (item) => {
        const {projectsSummary} = this.state;
        const indx = projectsSummary.map(p => p.name).indexOf(item.name);
        projectsSummary[indx].filter = !projectsSummary[indx].filter;
        this.setState({projectsSummary});
    }
    getActionElement = () => {
        const action = params.get('action');
        const projectID = params.get('projectID');
        switch (action) {
            case 'add':
                return <ProjectDetailsComponent mode={action}></ProjectDetailsComponent>
            case 'edit':
                return <ProjectDetailsComponent mode={action}
                                                projectID={projectID}
                ></ProjectDetailsComponent>
            default:
                return <div>
                    {this.setProjectsSummaryElement()}
                    <label>{this.state.summary}</label>
                    {this.getTabsElement()}
                    <div style={{marginTop: 10}}>
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
