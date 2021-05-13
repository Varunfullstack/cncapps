import Spinner from "../shared/Spinner/Spinner";
import React from 'react';

import MainComponent from "../shared/MainComponent";
import DailyStatsDashboardAPI from "./services/DailyStatsDashboardAPI";
import * as ReactDOM from "react-dom";
import Table from "../shared/table/table";
import '../style.css';
import moment from "moment";
import {getTeamCode} from "../utils/utils";
import ToolTip from "../shared/ToolTip";


const TABS = {
    NEAR_SLA: {
        id: 'NEAR_SLA',
        description: "Near Sla Today",
        dataLoader: (api) => {
            return api.getNearSLA();
        }
    },
    NEAR_FIX_SLA_BREACH: {
        id: 'NEAR_FIX_SLA_BREACH',
        description: 'Near Fix Sla Breach Today',
        dataLoader: (api) => {
            return api.getNearFixSLABreach();
        }
    },
    RAISED_ON: {
        id: 'RAISED_ON',
        description: 'Raised On',
        dataLoader: (api, date) => {
            return api.getRaisedOn(date);
        },
    },
    STARTED_ON: {
        id: 'STARTED_ON',
        description: 'Started On',
        dataLoader: (api, date) => {
            return api.getStartedOn(date);
        },
    },
    FIXED_ON: {
        id: 'FIXED_ON',
        description: 'Fixed On',
        dataLoader: (api, date) => {
            return api.getFixedOn(date);
        },
    },
    REOPENED_ON: {
        id: 'REOPENED_ON',
        description: 'Reopened On',
        dataLoader: (api, date) => {
            return api.getReopenedOn(date);
        },
    },
    BREACHED_ON: {
        id: 'BREACHED_ON',
        description: 'Breached On',
        dataLoader: (api, date) => {
            return api.breachedSLAOn(date);
        },
    }
}

const TabsOrder = [
    TABS.NEAR_SLA,
    TABS.NEAR_FIX_SLA_BREACH,
    TABS.RAISED_ON,
    TABS.STARTED_ON,
    TABS.FIXED_ON,
    TABS.REOPENED_ON,
    TABS.BREACHED_ON,
]

class DailyStatsDashboardComponent extends MainComponent {

    tabs = [];
    api = new DailyStatsDashboardAPI();

    constructor(props) {
        super(props);
        const queryParams = new URLSearchParams(window.location.search);

        const dateParam = queryParams.get('date');
        let dateToBeSelected = moment();
        if (dateParam) {
            dateToBeSelected = moment(dateParam, 'YYYY-MM-DD');
        }

        let tabToActivate = TABS.NEAR_SLA;
        const tabParam = queryParams.get('tab');
        if (tabParam) {
            tabToActivate = TABS[tabParam];
        }
        this.state = {
            ...this.state,
            showSpinner: false,
            selectedDate: dateToBeSelected.format('YYYY-MM-DD'),
            data: [],
            activeTab: tabToActivate
        };
    }

    componentDidMount() {
        const {activeTab} = this.state;
        this.loadTab(activeTab)
    }

    getActiveClass = (tabId) => {
        const {activeTab} = this.state;
        return activeTab.id == tabId ? "active" : "";
    };
    setActiveTab = (tab) => {


        this.setState({activeTab: tab, data: []});
        this.loadTab(tab);
    };


    getTabsElement = () => {
        return (
            <div key="tab"
                 className="tab-container"
                 style={{flexWrap: "wrap", justifyContent: "flex-start", maxWidth: 1500}}
            >
                {this.renderTabs()}
            </div>
        );
    };

    renderTabs() {
        return TabsOrder.map(tab => {
            return (
                <i key={tab.id}
                   className={`${this.getActiveClass(tab.id)} nowrap`}
                   onClick={() => this.setActiveTab(tab)}
                   style={{width: 200}}
                >
                    {tab.description}
                </i>
            )
        });
    }


    getFilterElement = () => {
        const {selectedDate, activeTab} = this.state;
        return (
            <div className="m-5">
                <input type="date" value={selectedDate || ''} onChange={this.updateSelectedDate}
                       disabled={activeTab.id === TABS.NEAR_SLA.id || activeTab.id === TABS.NEAR_FIX_SLA_BREACH.id}/>
            </div>
        );
    }
    loadTab = async (tab) => {
        const {selectedDate} = this.state;
        this.setState({showSpinner: true}, async () => {
            const data = await tab.dataLoader(this.api, selectedDate);
            this.setState({showSpinner: false, data});
        });

    };


    addToolTip = (element, title) => {
        return (
            <div className="tooltip"
            >
                {element}
                <div className="tooltiptext tooltip-bottom">{title}</div>
            </div>
        );
    };

    getQueueElement = () => {
        const {data} = this.state;

        let columns = [
            {
                path: "serviceRequestId",
                label: "",
                hdToolTip: "Service Request Number",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: false,
                className: "text-center",
                classNameColumn: "",
                content: (problem) => (
                    <a href={`SRActivity.php?action=displayActivity&serviceRequestId=${problem.serviceRequestId}`}
                       target='_blank'>
                        {problem.serviceRequestId}
                    </a>)
            },
            {
                path: "customerName",
                label: "",
                hdToolTip: "Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: false,
            },
            {
                path: "priority",
                label: "",
                hdToolTip: "Service Request Priority",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,
                className: "text-center",
                content: problem => {
                    if (problem.priority !== 1) {
                        return problem.priority;
                    }
                    return this.addToolTip(
                        <i className="fal fa-2x fa-exclamation-triangle color-gray"/>,
                        `Priority 1`)
                }
            },
            {
                path: "subjectSummary",
                label: "",
                hdToolTip: "Description of the Service Request",
                icon: "fal fa-2x fa-file-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                content: (problem) => (
                    <a className="pointer"
                       onClick={() => this.srDescription(problem)}
                       dangerouslySetInnerHTML={{__html: problem.subjectSummary}}
                    />
                ),
            },
            {
                path: "teamId",
                label: "",
                key: "team",
                hdToolTip: "Team",
                icon: "fal fa-2x fa-users color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                content: (problem) => {
                    return (
                        <label>
                            {getTeamCode(problem.teamId)}
                        </label>
                    )
                },
            },
            {
                path: "assignedEngineerName",
                label: "",
                key: "assignedUser",
                hdToolTip: "Service Request is assigned to this person",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
            },
        ]
        return (
            <Table id="queueData" data={data} columns={columns} pk="serviceRequestId" search={false}/>
        );

    }
    srDescription = (problem) => {
        window
            .open(`Activity.php?action=problemHistoryPopup&problemID=${problem.serviceRequestId}&htmlFmt=popup`,
                "reason",
                "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
            );
    };

    render() {

        return (
            <div>
                <Spinner key="spinner" show={this.state.showSpinner}/>
                {this.getAlert()}
                {this.getFilterElement()}
                {this.getTabsElement()}
                {this.getTotalElement()}
                {this.getQueueElement()}
            </div>
        );
    }

    updateSelectedDate = ($event) => {
        this.setState({selectedDate: $event.target.value}, () => {
            this.loadTab(this.state.activeTab);
        });
    };

    getTotalElement = () => {
        const {data} = this.state;
        return (
            <div>
                <ToolTip title="Total" width="50px">
                    <i className="fal fa-2x fa-sigma"/>
                    <span style={{marginLeft: "1em", fontSize: "large"}}>{data.length}</span>
                </ToolTip>
            </div>
        )
    }
}

export default DailyStatsDashboardComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainDailyStatsDashboard");
        ReactDOM.render(React.createElement(DailyStatsDashboardComponent), domContainer);
    }
)
