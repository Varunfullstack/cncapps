import MainComponent from "../shared/MainComponent";
import CurrentActivityService from "../CurrentActivityReportComponent/services/CurrentActivityService";
import Table from "../shared/table/table";
import Toggle from "../shared/Toggle";
import ToolTip from "../shared/ToolTip";
import {SRQueues} from "../utils/utils";
import DailyStatsComponent from "./subComponents/DailyStatsComponent";
import APISDManagerDashboard from "./services/APISDManagerDashboard";
import React from 'react';
import ReactDOM from 'react-dom';

import './../style.css';
import './SDManagerDashboardComponent.css';
import ActivityFollowOn from "../Modals/ActivityFollowOn";

class SDManagerDashboardComponent extends MainComponent {
    el = React.createElement;
    tabs = [];
    api = new APISDManagerDashboard();
    apiCurrentActivityService = new CurrentActivityService();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
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
            {id: 1, title: "Shortest SLA Remaining", showP5: true, icon: null},
            {id: 2, title: "Current Open P1 Requests", showP5: false, icon: null},
            {id: 3, title: "Shortest SLA Fix Remaining", showP5: false, icon: null},
            {id: 4, title: "Critical Service Requests", showP5: false, icon: null},
            {id: 5, title: "Current Open SRs", showP5: true, icon: null},
            {id: 6, title: "Oldest Updated SRs", showP5: true, icon: null},
            {id: 7, title: "Longest Open SR", showP5: true, icon: null},
            {id: 8, title: "Most Hours Logged", showP5: true, icon: null},
            {id: 9, title: "Customer", showP5: false, icon: null},
            {id: 10, title: "Daily Stats", showP5: false, icon: null},

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
        if (filter.activeTab < 9 && allocatedUsers.length == 0)
            this.apiCurrentActivityService.getAllocatedUsers().then((res) => {
                //console.log(res);
                this.setState({allocatedUsers: res});
            });
    }
    isActive = (code) => {
        const {filter} = this.state;
        if (filter.activeTab === code) return "active";
        else return "";
    };
    setActiveTab = (code) => {
        console.log("tab change");
        const {filter} = this.state;
        filter.activeTab = code;
        this.saveFilter(filter);
        //this.saveFilterToLocalStorage(filter);
        this.setState({filter, queueData: []});
    };

    getTabsElement = () => {
        const {el, tabs} = this;
        const {filter} = this.state;
        let tabsFilter = tabs;
        if (filter.p5) tabsFilter = tabs.filter((t) => t.showP5 == true);
        return el(
            "div",
            {
                key: "tab",
                className: "tab-container",
                style: {flexWrap: "wrap", justifyContent: "space-between", maxWidth: 1200}
            },
            tabsFilter.map((t) => {
                return el(
                    "i",
                    {
                        key: t.id,
                        className: this.isActive(t.id) + " nowrap",
                        onClick: () => this.setActiveTab(t.id),
                        style: {flex: "3 3 160px", flexBasis: 200}
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
        this.setState({filter}, () => this.loadTab(filter.activeTab));
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
        const {el} = this;
        const {filter} = this.state;
        return el(
            "div",
            {className: "m-5"},
            el("label", {className: "mr-3 ml-5"}, "HD"),
            el(Toggle, {
                disabled: false,
                checked: filter.hd,
                onChange: (value) => this.setFilterValue("hd", !filter.hd),
            }),

            el("label", {className: "mr-3 ml-5"}, "ES"),
            el(Toggle, {
                disabled: false,
                checked: filter.es,
                onChange: (value) => this.setFilterValue("es", !filter.es),
            }),

            el("label", {className: "mr-3 ml-5"}, "SP"),
            el(Toggle, {
                disabled: false,
                checked: filter.sp,
                onChange: (value) => this.setFilterValue("sp", !filter.sp),
            }),

            el("label", {className: "mr-3 ml-5"}, "P"),
            el(Toggle, {
                disabled: false,
                checked: filter.p,
                onChange: (value) => this.setFilterValue("p", !filter.p),
            }),

            el("label", {className: "mr-3 ml-5"}, "P5"),
            el(Toggle, {
                disabled: false,
                checked: filter.p5,
                onChange: (value) => this.setFilterValue("p5", !filter.p5),
            }),

            el("label", {className: "mr-3 ml-5"}, "Limit"),
            el('select', {
                    value: filter.limit,
                    onChange: (event) => this.setFilterValue("limit", event.target.value),
                },
                el("option", {value: 5}, 5),
                el("option", {value: 10}, 10),
                el("option", {value: 15}, 15),
                el("option", {value: 20}, 20),
                el("option", {value: 25}, 25),
                el("option", {value: 30}, 30),
            )
        );
    };
    loadTab = (id) => {
        if (id < 10) {
            this.loadAllocatedUsers();
            const {filter} = this.state;
            this.api.getQueue(id, filter).then((queueData) => {
                queueData = queueData.map(p => {
                    return {...p, workBgColor: this.getProblemWorkColor(p)}
                });
                console.log(queueData);

                this.setState({queueData})
            });
        } else return [];

    };
    startWork = async (problem) => {
        if (problem.lastCallActTypeID != null) {
            this.setState({showFollowOn: true, followOnActivity: problem})
        } else {
            this.alert("Another user is currently working on this SR");
        }
    };
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

    // end of shared methods
    getProblemWorkTitle(problem) {
        if (problem.isBeingWorkedOn) {
            return "Request being worked on by somebody else";
        }
        if (problem.status === "I") {
            return "Request not started yet";
        }
        return "Work on this request";
    }

    getWorkIconClassName(problem) {

        const commonClasses = "fa-play fa-2x pointer";
        if (problem.isBeingWorkedOn) {
            return `being-worked-on fad ${commonClasses}`;
        }
        if (problem.status === "I") {
            return `not-yet-started fad ${commonClasses}`
        }
        return `start-work fal ${commonClasses}`;
    }

    getProblemWorkColor(problem) {
        if (problem.hoursRemainingBgColor === "#FFF5B3") return "#FFF5B3";
        if (problem.bgColour === "#BDF8BA") return "#BDF8BA";
        return "#C6C6C6";
    }

    getQueueElement = () => {
        const {filter, queueData} = this.state;
        const {el} = this;
        if (filter.activeTab < 9) {
            const columns = [
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
                            title: this.getProblemWorkTitle(problem),
                            content:
                                el(
                                    "div",
                                    {key: "img1", onClick: () => this.startWork(problem)},
                                    el("i", {
                                        className: this.getWorkIconClassName(problem)
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
                {
                    hide: false,
                    order: 3,
                    path: null,
                    key: "hoursRemainingIcon",
                    label: "",
                    sortable: false,
                    toolTip: "On Hold",
                    className: "text-center",
                    content: (problem) =>
                        problem.hoursRemainingBgColor === "#BDF8BA"
                            ? el("i", {
                                className: "fal  fa-user-clock color-gray pointer inbox-icon",
                                key: "icon",
                                style: {float: "right"},
                            })
                            : null,
                },

                {
                    hide: false,
                    order: 4,
                    path: null,
                    key: "problemIdIcon",
                    label: "",
                    sortable: false,
                    className: "text-center",
                    toolTip: "SLA Failed for this Service Request",
                    content: (problem) =>
                        problem.bgColour == "#F8A5B6"
                            ? el("i", {
                                className:
                                    "fal fa-2x fa-bell-slash color-gray pointer inbox-icon",
                                title: "",
                                key: "icon",
                            })
                            : null,
                },
                {
                    hide: false,
                    order: 4.1,
                    path: null,
                    key: "Future Icon",
                    label: "",
                    sortable: false,
                    content: (problem) =>
                        moment(problem.alarmDateTime) > moment()
                            ? addToolTip(
                            el("i", {
                                className:
                                    "fal fa-2x fa-alarm-snooze color-gray pointer float-right inbox-icon",
                                key: "starIcon",
                            }),
                            `This Service Request is scheduled for the future date of ${moment(
                                problem.alarmDateTime
                            ).format("DD/MM/YYYY HH:mm")}`
                            )
                            : null,
                },
                {
                    path: "problemID",
                    label: "",
                    hdToolTip: "Service Request Number",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    //backgroundColorColumn: "bgColour",
                    classNameColumn: "",
                    content: (problem) => el('a', {
                        href: `Activity.php?action=displayLastActivity&problemID=${problem.problemID}`,
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
                    //classNameColumn: "customerNameDisplayClass",
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
                    // classNameColumn: "priorityBgColor",
                },
                {
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
                    path: "hoursRemaining",
                    label: "",
                    hdToolTip: "Open Hours",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-clock  color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    //backgroundColorColumn: "hoursRemainingBgColor"
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
                    content: (problem) => el('label', null, this.getTeamCode(problem.teamID)),
                },
                {
                    path: "engineerName",
                    label: "",
                    key: "assignedUser",
                    hdToolTip: "Service Request is assigned to this person",
                    icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                    sortable: false,
                    hdClassName: "text-center",
                    content: (problem) => this.getAllocatedElement(problem, problem.teamID),
                },
                {
                    path: "dateTime",
                    label: "",
                    key: "dateTime",
                    hdToolTip: "Purple = Updated by another user OR has an alarm date in past",
                    icon: "fal fa-2x fa-calendar color-gray2 ",
                    sortable: false,
                    hdClassName: "text-center",
                    backgroundColorColumn: "updatedBgColor",
                },
            ]
            return el(Table, {
                key: "queueData",
                data: queueData || [],
                columns: columns,
                pk: "problemID",
                search: true,
            });
        } else if (filter.activeTab == 9) {
            const columns = [
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
                    path: "srCount",
                    label: "",
                    hdToolTip: "Number Of Activities",
                    hdClassName: "text-center",
                    icon: "fal fa-2x fa-sigma color-gray2 pointer",
                    sortable: false,
                    className: "text-center",
                    content: (problem) => el('a', {
                        href: `CurrentActivityReport.php?action=setFilter&selectedCustomerID=${problem.customerID}`,
                        target: '_blank'
                    }, problem.srCount)

                }
            ]
            return el('div', {style: {width: 500}},
                el(Table, {
                    key: "queueData",
                    data: queueData || [],
                    columns: columns,
                    pk: "customerID",
                    search: true,

                })
            );
        } else if (filter.activeTab == 10) {
            return el(DailyStatsComponent);
        }
    }
    srDescription = (problem) => {
        window.open(
            `Activity.php?action=problemHistoryPopup&problemID=${problem.problemID}&htmlFmt=popup`,
            "reason",
            "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
        );
    };
    getAllocatedElement = (problem, teamId) => {
        const {el} = this;
        const {allocatedUsers} = this.state;
        const currentTeam = allocatedUsers.filter((u) => u.teamID === teamId);
        const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamId);
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
                            className: teamId === p.teamID ? "in-team" : "",
                        },
                        p.fullName
                    )
                ),
            ]
        );
    };

    handleUserOnSelect = (event, problem, code) => {
        const engineerId = event.target.value != "" ? event.target.value : 0;
        problem.engineerId = engineerId;
        this.apiCurrentActivityService
            .allocateUser(problem.problemID, engineerId)
            .then((res) => {
                if (res.status) {
                    this.loadTab(this.state.filter.activeTab);
                }
            });
    };
    getTeamCode = (teamID) => {
        const queues = SRQueues.filter(q => q.teamID == teamID);
        if (queues.length > 0)
            return queues[0].code;
        else return ""
    }

    render() {
        const {el} = this;
        return el("div", null,
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
})
