import MainComponent from "../shared/MainComponent";
import CurrentActivityService from "../CurrentActivityReportComponent/services/CurrentActivityService";
import Table from "../shared/table/table";
import Toggle from "../shared/Toggle";
import ToolTip from "../shared/ToolTip";
import {getServiceRequestWorkTitle, getWorkIconClassName, SRQueues} from "../utils/utils";
import DailyStatsComponent from "../shared/DailyStatsComponent/DailyStatsComponent";
import APISDManagerDashboard from "./services/APISDManagerDashboard";
import React from 'react';
import ReactDOM from 'react-dom';

import './../style.css';
import './SDManagerDashboardComponent.css';
import ActivityFollowOn from "../Modals/ActivityFollowOn";
import moment from "moment";
import Spinner from "../shared/Spinner/Spinner";
import {ColumnRenderer} from "../CurrentActivityReportComponent/subComponents/ColumnRenderer";
import MissedCallBackComponent from "./subComponents/MissedCallBackComponent";
import PendingChargeableRequestsComponent from "./subComponents/PendingChargeableRequestsComponent";

const CUSTOMER_TAB = 9;

const HELD_FOR_QA_TAB = 11;

const DAILY_STATS_TAB = 10;

const SHORTEST_SLA_FIX_REMAINING = 3;

const AUTO_RELOAD_TIME = 60 * 1000;

const CRITICAL_SERVICE_REQUESTS = 4;
const TAB_MISSED_CALL_BACKS = 12;
const PENDING_CHARGEABLE_WORK_REQUESTS_TAB = 13;

class SDManagerDashboardComponent extends MainComponent {
    el = React.createElement;
    tabs = [];
    api = new APISDManagerDashboard();
    apiCurrentActivityService = new CurrentActivityService();
    intervalRef;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            filter: {
                hd: false,
                es: false,
                sp: false,
                p: false,
                p5: false,
                activeTab: 1,
                limit: 10
            },
            queueData: [],
            allocatedUsers: []
        };
        this.tabs = [
            {id: 1, title: "Shortest SLA Remaining", icon: null},
            {id: 2, title: "Current Open P1 Requests", icon: null},
            {id: SHORTEST_SLA_FIX_REMAINING, title: "Shortest SLA Fix Remaining", icon: null},
            {id: CRITICAL_SERVICE_REQUESTS, title: "Critical Service Requests", icon: null},
            {id: 5, title: "Current Open SRs", icon: null},
            {id: 6, title: "Oldest Updated SRs", icon: null},
            {id: 7, title: "Longest Open SR", icon: null},
            {id: 8, title: "Most Hours Logged", icon: null},
            {id: CUSTOMER_TAB, title: "Customer", icon: null},
            {id: HELD_FOR_QA_TAB, title: "Held for QA", icon: null},
            {id: DAILY_STATS_TAB, title: "Daily Stats", icon: null},
            {id: TAB_MISSED_CALL_BACKS, title: "Call Backs", icon: null},
            {id: PENDING_CHARGEABLE_WORK_REQUESTS_TAB, title: 'Pending Chargeable Work Requests', icon: null}
        ];
    }

    componentDidMount() {
        this.loadFilterFromStorage();
        setTimeout(() => {
            this.loadAllocatedUsers()
        }, 500);
    }

    loadAllocatedUsers = () => {
        const {filter, allocatedUsers} = this.state;
        if (filter.activeTab < CUSTOMER_TAB && allocatedUsers.length == 0)
            this.apiCurrentActivityService.getAllocatedUsers().then((res) => {
                this.setState({allocatedUsers: res});
            });
    }
    isActive = (code) => {
        const {filter} = this.state;
        if (filter.activeTab == code) return "active";
        else return "";
    };
    setActiveTab = (code) => {
        const {filter} = this.state;
        filter.activeTab = code;
        this.saveFilter(filter);
        this.setState({filter, queueData: []});
        this.checkAutoReloading();
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
    loadFilterFromStorage = () => {
        let filter = localStorage.getItem("SDManagerDashboardFilter");
        if (filter) filter = JSON.parse(filter);
        else filter = this.state.filter;
        this.setState({filter}, () => {
            this.loadTab(filter.activeTab)
            this.checkAutoReloading();
        });
    };
    setFilterValue = (property, value) => {
        const {filter} = this.state;
        filter[property] = value;
        this.setState({filter}, () => this.saveFilter(filter));
    };

    saveFilter(filter) {
        localStorage.setItem("SDManagerDashboardFilter", JSON.stringify(filter));
        this.loadTab(filter.activeTab);
    }

    getFilterElement = () => {
        const {filter} = this.state;
        const shouldBeHidden = [
            DAILY_STATS_TAB
        ].findIndex(x => x === filter.activeTab) > -1;

        return (
            <div className="m-5">
                {
                    shouldBeHidden ? '' :
                        <React.Fragment>

                            <label className="mr-3 ml-5">HD</label>
                            <Toggle checked={filter.hd}
                                    onChange={(value) => this.setFilterValue("hd", !filter.hd)}
                            />
                            <label className="mr-3 ml-5">ES</label>
                            <Toggle checked={filter.es}
                                    onChange={(value) => this.setFilterValue("es", !filter.es)}
                            />
                            <label className="mr-3 ml-5">SP</label>
                            <Toggle checked={filter.sp}
                                    onChange={(value) => this.setFilterValue("sp", !filter.sp)}
                            />
                            <label className="mr-3 ml-5">P</label>
                            <Toggle checked={filter.p}
                                    onChange={(value) => this.setFilterValue("p", !filter.p)}
                            />
                            <label className="mr-3 ml-5">P5</label>
                            <Toggle checked={filter.p5}
                                    onChange={(value) => this.setFilterValue("p5", !filter.p5)}
                            />
                        </React.Fragment>
                }
                <label className="mr-3 ml-5">
                    Limit
                </label>
                <select value={filter.limit}
                        onChange={(event) => this.setFilterValue("limit", event.target.value)}
                >
                    <option value="5"> 5</option>
                    <option value="10"> 10</option>
                    <option value="15"> 15</option>
                    <option value="20"> 20</option>
                    <option value="25"> 25</option>
                    <option value="30"> 30</option>
                    <option value="40"> 40</option>
                    <option value="75"> 75</option>
                    <option value="100"> 100</option>
                </select>
            </div>
        );
    }
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
    startWork = async (problem) => {
        if (problem.lastCallActTypeID != null) {
            this.setState({showFollowOn: true, followOnActivity: problem})
        } else {
            this.alert("Another user is currently working on this SR");
        }
    }
    ;
    getFollowOnElement = () => {
        const {showFollowOn, followOnActivity} = this.state;
        const startWork = true;
        return showFollowOn ? this.el(ActivityFollowOn, {
            startWork,
            key: "followOnModal",
            callActivityID: followOnActivity.callActivityID,
            onCancel: () => this.setState({showFollowOn: false})
        }) : null;
    }


    addToolTip = (element, title) => {
        return this.el(
            "div",
            {className: "tooltip"},
            element,
            this.el("div", {className: "tooltiptext tooltip-bottom"}, title)
        );
    }
    ;

    getQueueElement = () => {
        const {queueData} = this.state;
        const {el} = this;
        const filter = {...this.state.filter};

        if ([1, 2, 3, CRITICAL_SERVICE_REQUESTS, SHORTEST_SLA_FIX_REMAINING, 5, 6, 7, 8, HELD_FOR_QA_TAB].indexOf(filter.activeTab) >= 0) {
            let columns = [
                {
                    hide: false,
                    order: 1,
                    path: null,
                    label: "",
                    key: "work",
                    sortable: false,
                    hdClassName: "text-center",
                    className: "text-center",
                    content: (problem) =>
                        el(ToolTip, {
                            title: getServiceRequestWorkTitle(problem),
                            content:
                                el(
                                    "div",
                                    {key: "img1", onClick: () => this.startWork(problem)},
                                    el("i", {
                                        className: getWorkIconClassName(problem)
                                    })
                                )
                        }),
                },
                {
                    hide: false,
                    order: 2,
                    path: null,
                    key: "custsomerIcon",
                    label: "",
                    sortable: false,
                    toolTip: "Special Attention customer / contact",
                    content: (problem) =>
                        problem.specialAttentionCustomer
                            ? el("i", {
                                className:
                                    "fal fa-2x fa-star color-gray pointer float-right inbox-icon",
                                key: "starIcon",
                            })
                            : null,
                },
                ColumnRenderer.getFixSLAWarningColumn(),
                {
                    hide: false,
                    order: 3,
                    path: null,
                    key: "hoursRemainingIcon",
                    label: "",
                    sortable: false,
                    toolTip: "On Hold",
                    className: "text-center",
                    content: (problem) => {
                        if (!problem.awaitingCustomerResponse) {
                            return;
                        }
                        return (
                            <i className="fal  fa-user-clock color-gray pointer inbox-icon"
                               key="icon"
                               style={{float: "right"}}
                            />
                        )
                    }
                },

                {
                    hide: false,
                    order: 4,
                    path: null,
                    key: "problemIdIcon",
                    label: "",
                    sortable: false,
                    className: "text-center",
                    toolTip: "SLA or Fixed SLA Failed for this Service Request",
                    content: (problem) => {
                        if (!problem.isSLABreached && !problem.isFixedSLABreached) {
                            return null;
                        }
                        return (
                            <i className="fal fa-2x fa-bell-slash color-gray pointer inbox-icon"
                               title=""
                               key="icon"
                            />
                        )
                    },
                },
                {
                    hide: false,
                    order: 4.1,
                    path: null,
                    key: "Future Icon",
                    label: "",
                    sortable: false,
                    content: (problem) => {
                        const momentAlarmDateTime = moment(problem.alarmDateTime, 'YYYY-MM-DD HH:mm:ss');
                        if (!problem.alarmDateTime || !momentAlarmDateTime.isValid() || (momentAlarmDateTime.isSameOrBefore(moment()))) {
                            return null;
                        }
                        return this.addToolTip(
                            <i className="fal fa-2x fa-alarm-snooze color-gray pointer float-right inbox-icon"
                               key="starIcon"
                            />,
                            `This Service Request is scheduled for the future date of ${momentAlarmDateTime.format("DD/MM/YYYY HH:mm")}`
                        )
                    },
                },
                {
                    path: "problemID",
                    label: "",
                    hdToolTip: "Service Request Number",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    classNameColumn: "",
                    content: (problem) => el('a', {
                        href: `SRActivity.php?action=displayActivity&serviceRequestId=${problem.problemID}`,
                        target: '_blank'
                    }, problem.problemID)
                },
                {
                    path: "customerName",
                    label: "",
                    hdToolTip: "Customer",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-building color-gray2 pointer",
                    sortable: false,
                    content: (problem) => el('a', {
                        href: `SalesOrder.php?action=search&customerID=${problem.customerID}`,
                        target: '_blank'
                    }, problem.customerName)
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
                    display: [HELD_FOR_QA_TAB].indexOf(filter.activeTab) < 0,
                    path: "",
                    label: "",
                    hdToolTip: "Allocate additional time",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-alarm-plus color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    content: (problem) => el(ToolTip, {
                        title: "Allocate more time",
                        content: el('a', {
                            className: "fal fa-2x fa-hourglass-start color-gray inbox-icon",
                            href: `Activity.php?action=allocateAdditionalTime&problemID=${problem.problemID}`,
                            target: '_blank'
                        })
                    }),
                },
                {
                    display: [HELD_FOR_QA_TAB].indexOf(filter.activeTab) < 0,
                    path: "hoursRemainingForSLA",
                    label: "",
                    hdToolTip: "Open Hours",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-clock  color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                },
                {
                    path: "totalActivityDurationHours",
                    label: "",
                    hdToolTip: "Time spent",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-stopwatch color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                },
                {
                    path: "activityCount",
                    label: "",
                    hdToolTip: "Number Of Activities",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-sigma color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                },
                {
                    path: "reason",
                    label: "",
                    hdToolTip: "Description of the Service Request",
                    icon: "fal fa-2x fa-file-alt color-gray2 ",
                    sortable: false,
                    hdClassName: "text-center",
                    content: (problem) =>
                        el(
                            "a",
                            {
                                className: "pointer",
                                onClick: () => this.srDescription(problem),
                                dangerouslySetInnerHTML: {__html: problem.reason}
                            },
                        ),
                },
                {
                    path: "teamID",
                    label: "",
                    key: "team",
                    hdToolTip: "Team",
                    icon: "fal fa-2x fa-users color-gray2 ",
                    sortable: false,
                    hdClassName: "text-center",
                    content: (problem) => {
                        let teamCode = problem.teamID;
                        if (filter.activeTab == HELD_FOR_QA_TAB) {
                            if (problem.fixedDate) {
                                teamCode = problem.fixedTeamId;
                            }
                            if (!problem.engineerId) {
                                teamCode = problem.queueTeamId
                            }
                        }
                        return (
                            <label>
                                {this.getTeamCode(teamCode)}
                            </label>
                        )
                    },
                },
                {
                    display: filter.activeTab == HELD_FOR_QA_TAB,
                    path: "engineerName",
                    label: "",
                    key: "assignedUser",
                    hdToolTip: "Service Request is assigned to this person",
                    icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                    sortable: false,
                    hdClassName: "text-center",
                    content: problem => {
                        if (problem.fixedDate) {
                            return problem.engineerFixedName;
                        }
                        return problem.engineerName;
                    }
                },
                {
                    display: [HELD_FOR_QA_TAB].indexOf(filter.activeTab) < 0,
                    path: "engineerName",
                    label: "",
                    key: "assignedUser",
                    hdToolTip: "Service Request is assigned to this person",
                    icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                    sortable: false,
                    hdClassName: "text-center",
                    content: (problem) => this.getAllocatedElement(problem, problem.teamID, +problem.queueTeamId),
                },
                {
                    display: [HELD_FOR_QA_TAB].indexOf(filter.activeTab) < 0,
                    path: "dateTime",
                    label: "",
                    key: "dateTime",
                    content: serviceRequest => {
                        const dateTime = moment(serviceRequest.dateTime, 'YYYY-MM-DD HH:mm:ss');
                        if (!dateTime.isValid()) {
                            return null;
                        }
                        return dateTime.format('DD/MM/YYYY HH:mm');
                    },
                    hdToolTip: "Time",
                    icon: "fal fa-2x fa-calendar color-gray2 ",
                    sortable: false,
                    hdClassName: "text-center",
                },
                {
                    display: HELD_FOR_QA_TAB === filter.activeTab,
                    path: "fixedDate",
                    label: "",
                    hdToolTip: "Fixed date",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-calendar  color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    content: (problem => {
                        if (!problem.fixedDate || ["F", "C"].indexOf(problem.status) === -1) {
                            return null;
                        }

                        return moment(problem.fixedDate, 'YYYY-MM-DD').format('DD/MM/YYYY')
                    })
                },
            ]
            columns = columns.filter(c => c.display == undefined || c.display == true);
            return el(Table, {
                id: "queueData",
                data: queueData || [],
                columns: columns,
                pk: "problemID",
                search: true,
            });
        } else if (filter.activeTab == CUSTOMER_TAB) {
            const columns = [
                {
                    path: "customerName",
                    label: "",
                    hdToolTip: "Customer",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-building color-gray2 pointer",
                    sortable: false
                },
                {
                    path: "srCount",
                    label: "",
                    hdToolTip: "Number of open Service Requests",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-sigma color-gray2 pointer",
                    sortable: false,

                }
            ]
            return el('div', {style: {width: 500}},
                el(Table, {
                    id: "queueData",
                    data: queueData || [],
                    columns: columns,
                    pk: "customerID",
                    search: true,

                })
            );
        } else if (filter.activeTab == 10) {
            return el(DailyStatsComponent);
        } else if (filter.activeTab == TAB_MISSED_CALL_BACKS) {
            return <MissedCallBackComponent filter={filter}/>
        } else if (filter.activeTab == PENDING_CHARGEABLE_WORK_REQUESTS_TAB) {
            return <PendingChargeableRequestsComponent filter={filter}/>
        }
    }
    srDescription = (problem) => {
        window.open(
            `Activity.php?action=problemHistoryPopup&problemID=${problem.problemID}&htmlFmt=popup`,
            "reason",
            "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
        );
    }
    ;
    getAllocatedElement = (problem, teamId, queueTeamId) => {
        const {el} = this;
        const {allocatedUsers} = this.state;
        let teamIdCompare = teamId || queueTeamId;

        const currentTeam = allocatedUsers.filter((u) => u.teamID == teamIdCompare);
        const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamIdCompare);

        return el(
            "select",
            {
                key: "allocatedUser",
                value: problem.engineerId || "",
                width: 120,
                onChange: (event) => this.handleUserOnSelect(event, problem, teamId),
            },
            [
                el("option", {value: "", key: "allOptions"}, ""),
                ...[...currentTeam, ...otherTeams].map((p) =>
                    el(
                        "option",
                        {
                            value: p.userID,
                            key: "option" + p.userID,
                            className: teamIdCompare == p.teamID ? "in-team" : "",
                        },
                        p.fullName
                    )
                ),
            ]
        );
    }
    ;

    handleUserOnSelect = (event, problem, code) => {
        const engineerId = event.target.value !== "" ? event.target.value : 0;
        problem.engineerId = engineerId;
        this.apiCurrentActivityService
            .allocateUser(problem.problemID, engineerId)
            .then((res) => {
                if (res.status) {
                    this.loadTab(this.state.filter.activeTab);
                }
            });
    }
    ;
    getTeamCode = (teamID) => {
        const queues = SRQueues.filter(q => q.teamID == teamID);
        if (queues.length > 0)
            return queues[0].code;
        else return ""
    }

    render() {
        const {el} = this;
        return el("div", null,
            el(Spinner, {key: "spinner", show: this.state.showSpinner}),
            this.getAlert(),
            this.getFollowOnElement(),
            this.getFilterElement(),
            this.getTabsElement(),
            this.getQueueElement(),
        );
    }
}

export default SDManagerDashboardComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainSDManagerDashboard");
        ReactDOM.render(React.createElement(SDManagerDashboardComponent), domContainer);
    }
)
