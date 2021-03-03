// tabs subComponents
import InboxHelpDeskComponent from './subComponents/InboxHelpDeskComponent';
import InboxEscalationsComponent from './subComponents/InboxEscalationsComponent';
import InboxSmallProjectsComponent from './subComponents/InboxSmallProjectsComponent';
import InboxSalesComponent from './subComponents/InboxSalesComponent';
import InboxProjectsComponent from './subComponents/InboxProjectsComponent';
import InboxToBeLoggedComponent from './subComponents/InboxToBeLoggedComponent';
import InboxPendingReopenedComponent from './subComponents/InboxPendingReopenedComponent';
import CurrentActivityService from './services/CurrentActivityService';
import Spinner from './../shared/Spinner/Spinner';
import MainComponent from '../shared/MainComponent';
import ActivityFollowOn from '../Modals/ActivityFollowOn';
import InboxOpenSRComponent from './subComponents/InboxOpenSRComponent';
import {getServiceRequestWorkTitle, sort} from '../utils/utils';
import React from 'react';
import ReactDOM from 'react-dom';
import CallBackModal from './subComponents/CallBackModal';

import '../style.css';
import '../shared/ToolTip.css'
import CallBackComponent from './subComponents/CallBackComponent';

const AUTORELOAD_INTERVAL_TIME = 2 * 60 * 1000;

class CurrentActivityReportComponent extends MainComponent {
    el = React.createElement;
    apiCurrentActivityService;
    autoReloadInterval;
    teams;

    constructor(props) {
        super(props);
        const filter = this.getLocalStorageFilter();
        this.state = {
            ...this.state,
            showFollowOn: false,
            followOnActivity: null,
            helpDeskInbox: [],
            helpDeskInboxFiltered: [],
            escalationInbox: [],
            escalationInboxFiltered: [],
            salesInbox: [],
            salesInboxFiltered: [],
            smallProjectsInbox: [],
            smallProjectsInboxFiltered: [],
            projectsInbox: [],
            projectsInboxFiltered: [],
            toBeLoggedInbox: [],
            toBeLoggedInboxFiltered: [],
            pendingReopenedInbox: [],
            pendingReopenedInboxFiltered: [],
            openSRInboxFiltered: [],
            openSRInbox: [],
            fixedInbox: [],
            futureInbox: [],
            allocatedUsers: [],
            currentUser: null,
            _showSpinner: false,
            userFilter: "",
            filter,
            showCallBackModal:false,
            currentProblem:null
        };
        this.apiCurrentActivityService = new CurrentActivityService();
        this.teams = [
            {id: 1, title: 'Helpdesk', code: 'H', queueNumber: 1, order: 1, display: true, icon: null, canMove: true},
            {
                id: 2,
                title: 'Escalations',
                code: 'E',
                queueNumber: 2,
                order: 2,
                display: true,
                icon: null,
                canMove: true
            },
            {
                id: 4,
                title: 'Small Projects',
                code: 'SP',
                queueNumber: 3,
                order: 3,
                display: true,
                icon: null,
                canMove: true
            },
            {id: 5, title: 'Projects', code: 'P', queueNumber: 5, order: 4, display: true, icon: null, canMove: true},
            {id: 7, title: 'Sales', code: 'S', queueNumber: 4, order: 5, display: true, icon: null, canMove: true},
            {
                id: 10,
                title: 'To Be Logged',
                code: 'TBL',
                queueNumber: 10,
                order: 6,
                display: true,
                icon: null,
                canMove: false
            },
            {
                id: 11,
                title: 'Pending Reopen',
                code: 'PR',
                queueNumber: 11,
                order: 7,
                display: false,
                icon: null,
                canMove: false
            },
            {
                id: 12,
                title: 'All Teams',
                code: 'OSR',
                queueNumber: 13,
                order: 8,
                display: true,
                icon: null,
                canMove: false
            },

        ]

    }

    componentDidMount() {
        this.loadData();
    }

    showSpinner = () => {
        this.setState({_showSpinner: true});
    };
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    };
    getTabsElement = () => {
        const {el, isActive, setActiveTab, teams} = this;
        return el("div", {
            key: "tab",
            className: "tab-container"
        }, teams.sort((a, b) => a.order > b.order ? 1 : -1).map(t => {
            if (t.display)
                return el(
                    "i",
                    {
                        key: t.code,
                        className: isActive(t.code) + " nowrap",
                        onClick: () => setActiveTab(t.code),
                    },
                    t.title,
                    t.icon ? el("span", {
                        className: t.icon, style: {
                            fontSize: "12px",
                            marginTop: "-12px",
                            marginLeft: "-5px",
                            position: "absolute",
                            color: "#000"
                        }
                    }) : null
                );
            else return null;
        }));
    };
    isActive = (code) => {
        const {filter} = this.state;
        if (filter.activeTab == code) return "active";
        else return "";
    };

    checkAutoReload(code) {
        const specificAutoReload = {
            'H': 0,
            'E': 0,
            'SP': 0,
            'P': 0,
            'S': 0,
        };
        if (this.autoReloadInterval) {
            clearInterval(this.autoReloadInterval);
        }
        if (code in specificAutoReload) {
            this.autoReloadInterval = setInterval(() => {
                this.loadQueue(code);
                this.loadQueue('TBL');
                this.loadQueue('PR');
            }, AUTORELOAD_INTERVAL_TIME);
        }

    }

    setActiveTab = (code) => {

        const {filter} = this.state;
        filter.activeTab = code;
        this.loadQueue(code);
        this.checkAutoReload(code);
        this.saveFilterToLocalStorage(filter); 
        this.setState({filter,openSrCustomerID:''});
    };
    loadData = () => {
        const {filter} = this.state;
        this.apiCurrentActivityService
            .getAllocatedUsers()
            .then((res) => this.setState({allocatedUsers: res}));
        this.apiCurrentActivityService
            .getCurrentUser()
            .then((res) => {
                if (res.isSDManager || res.serviceRequestQueueManager)
                    this.teams.filter(t => t.id == 11)[0].display = true;
                this.setState({currentUser: res})
            });
        this.loadQueue(filter.activeTab);
        this.loadQueue('TBL');
        this.loadQueue('PR');
        this.checkAutoReload(filter.activeTab);
    };

    getLocalStorageFilter = () => {
        let filter = localStorage.getItem("inboxFilter");
        if (filter)
            filter = JSON.parse(filter);
        else
            filter = {activeTab: "H", userFilter: ""};

        return filter;
    }
    saveFilterToLocalStorage = (filter) => {
        localStorage.setItem("inboxFilter", JSON.stringify(filter));
    }
    loadQueue = (code) => {
        const {handleUserFilterOnSelect} = this;
        const {filter} = this.state;
        if (code) {
            if (code !== "OSR")
                this.showSpinner();
            switch (code) {
                case "H":
                    this.apiCurrentActivityService.getHelpDeskInbox().then((res) => {
                        const helpDeskInbox = this.prepareResult(res);
                        this.setState({
                            _showSpinner: false,
                            helpDeskInbox,
                        }, () => handleUserFilterOnSelect(filter.userFilter));
                    });
                    break;
                case "E":
                    this.apiCurrentActivityService.getEscalationsInbox().then((res) => {
                        const escalationInbox = this.prepareResult(res);
                        this.setState({
                            _showSpinner: false,
                            escalationInbox,
                        }, () => handleUserFilterOnSelect(filter.userFilter));
                    });
                    break;
                case "S":
                    this.apiCurrentActivityService.getSalesInbox().then((res) => {
                        const salesInbox = this.prepareResult(res);
                        this.setState({
                            _showSpinner: false,
                            salesInbox,
                        }, () => handleUserFilterOnSelect(filter.userFilter));
                    });
                    break;
                case "SP":
                    this.apiCurrentActivityService.getSmallProjectsInbox().then((res) => {
                        const smallProjectsInbox = this.prepareResult(res);
                        this.setState({
                            _showSpinner: false,
                            smallProjectsInbox,
                        }, () => handleUserFilterOnSelect(filter.userFilter));
                    });
                    break;
                case "P":
                    this.apiCurrentActivityService.getProjectsInbox().then((res) => {
                        const projectsInbox = this.prepareResult(res);
                        this.setState({
                            _showSpinner: false,
                            projectsInbox,
                        }, () => handleUserFilterOnSelect(filter.userFilter));
                    });
                    break;
                case "TBL":
                    this.apiCurrentActivityService.getToBeLoggedInbox().then((res) => {
                        const toBeLoggedInbox = this.prepareResult(res);
                        if (toBeLoggedInbox.length > 0)
                            this.teams.filter(t => t.code == 'TBL')[0].icon = "fal fa-asterisk";
                        this.setState({
                            _showSpinner: false,
                            toBeLoggedInbox,
                        }, () => handleUserFilterOnSelect(filter.userFilter));
                    });
                    break;
                case "PR":
                    this.apiCurrentActivityService.getPendingReopenedInbox().then((res) => {
                        const pendingReopenedInbox = this.prepareResult(res);
                        if (pendingReopenedInbox.length > 0)
                            this.teams.filter(t => t.code == 'PR')[0].icon = "fal fa-asterisk";
                        this.setState({
                            _showSpinner: false,
                            pendingReopenedInbox,
                        }, () => handleUserFilterOnSelect(filter.userFilter));
                    });
                    break;
                case "OSR":
                    if (this.state.openSrCustomerID)
                        this.getCustomerOpenSR(this.state.openSrCustomerID);
                    break;
            }
        }
    };
    getCustomerOpenSR = (customerID) => {
        const {filter} = this.state;

        if (customerID != null) {
            this.showSpinner();
            this.setState({openSrCustomerID: customerID})
            this.apiCurrentActivityService
                .getCustomerOpenSR(customerID)
                .then((res) => {
                    const openSRInbox = this.prepareResult(res);
                    sort(openSRInbox, "queueNo");

                    this.setState(
                        {
                            _showSpinner: false,
                            openSRInbox,
                        },
                        () => this.handleUserFilterOnSelect(filter.userFilter)
                    );
                });
        }
    }
    // Shared methods
    moveToAnotherTeam = async ({target}, problem, code) => {
        let answer = null;
        if (problem.status === "P") {
            answer = await this.prompt(
                "Please provide a reason for moving this SR into a different queue"
            );
            if (!answer) {
                return;
            }
        }

        this.apiCurrentActivityService
            .changeQueue(problem.problemID, target.value, answer)
            .then((res) => {
                if (res && res.status) {
                    this.loadQueue(code);
                }
            });
    };
    /**
     * Move to another queue
     */
    getMoveElement = (code, problem, defaultValue = null) => {
        const {el, moveToAnotherTeam, teams} = this;
        let options = teams
            .map(t => ({id: t.queueNumber, title: t.code, canMove: t.canMove}))
            .filter((e) => e.title !== code && e.canMove == true);
        return el(
            "select",
            {
                key: "movItem" + problem.callActivityID,
                onChange: (event) => moveToAnotherTeam(event, problem, code),
                defaultValue
            },
            [
                el("option", {value: "", key: "null"}),
                options.map((e) => el("option", {value: e.id, key: e.id}, e.title)),
            ]
        );
    };
    srDescription = (problem) => {
        window.open(
            `Activity.php?action=problemHistoryPopup&problemID=${problem.problemID}&htmlFmt=popup`,
            "reason",
            "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
        );
    };
    srCustomerDescription = (problem) => {
        window.open(
            `Activity.php?action=customerProblemPopup&customerProblemID=${problem.cpCustomerProblemID}&htmlFmt=popup`,
            "reason",
            "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
        );
    }
    allocateAdditionalTime = (problem) => {
        window.location = `Activity.php?action=allocateAdditionalTime&problemID=${problem.problemID}`;
    };
    requestAdditionalTime = async (problem) => {
        var reason = await this.prompt(
            "Please provide your reason for requesting additional time.(Required)"
        );
        if (!reason) {
            return;
        }
        this.apiCurrentActivityService
            .requestAdditionalTime(problem.problemID, reason)
            .then((res) => {
                if (res.ok) this.alert("Additional time has been requested");
            });
    };
    startWork = async (problem, code) => {
        if (problem.lastCallActTypeID != null) {
            this.setState({showFollowOn: true, followOnActivity: problem})
        } else {
            this.alert("Another user is currently working on this SR");
        }
    };
    handleUserOnSelect = (event, problem, code) => {
        const engineerId = event.target.value !== "" ? event.target.value : 0;
        problem.engineerId = engineerId;
        this.apiCurrentActivityService
            .allocateUser(problem.problemID, engineerId)
            .then((res) => {
                if (res.status) {
                    this.loadQueue(code);
                }
            });
    };
    getAllocatedElement = (problem, code) => {
        const {el, handleUserOnSelect} = this;
        const {allocatedUsers} = this.state;
        const teamId = this.getTeamId(code);
        const currentTeam = allocatedUsers.filter((u) => u.teamID == teamId);
        const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamId);
        return el(
            "select",
            {
                key: "allocatedUser",
                value: problem.engineerId || "",
                width: 120,
                onChange: (event) => handleUserOnSelect(event, problem, code),
            },
            [
                el("option", {value: "", key: "allOptions"}, ""),
                ...[...currentTeam, ...otherTeams].map((p) =>
                    el(
                        "option",
                        {
                            value: p.userID,
                            key: "option" + p.userID,
                            className: teamId == p.teamID ? "in-team" : "",
                        },
                        p.userName
                    )
                ),
            ]
        );
    };

    getTeamId(code) {
        return this.teams.filter(t => t.code == code)[0].id;
    }

    prepareResult = (result) => {
        return result.map((problem) => {
            problem.workBtnTitle = getServiceRequestWorkTitle(problem);
            return problem;
        });
    };
    handleUserFilterOnSelect = (userId) => {
        const userFilter = userId;
        let {
            helpDeskInbox,
            smallProjectsInbox,
            projectsInbox,
            salesInbox,
            escalationInbox,
            toBeLoggedInbox,
            pendingReopenedInbox,
            openSRInbox,
            filter,
        } = this.state;
        filter.userFilter = userFilter;
        const helpDeskInboxFiltered = this.filterData(userFilter, helpDeskInbox);
        const smallProjectsInboxFiltered = this.filterData(
            userFilter,
            smallProjectsInbox
        );
        const projectsInboxFiltered = this.filterData(userFilter, projectsInbox);
        const salesInboxFiltered = this.filterData(userFilter, salesInbox);
        const escalationInboxFiltered = this.filterData(
            userFilter,
            escalationInbox
        );

        const openSRInboxFiltered = this.filterData(userFilter, openSRInbox);
        const toBeLoggedInboxFiltered = toBeLoggedInbox;
        const pendingReopenedInboxFiltered = pendingReopenedInbox;
        this.saveFilterToLocalStorage(filter);
        this.setState({
            filter,
            helpDeskInboxFiltered,
            smallProjectsInboxFiltered,
            projectsInboxFiltered,
            salesInboxFiltered,
            escalationInboxFiltered,
            toBeLoggedInboxFiltered,
            pendingReopenedInboxFiltered,
            openSRInboxFiltered
        });
    };
    filterData = (engineerId, data) => {
        return data.filter(
            (p) =>
                p.engineerId == null || p.engineerId == engineerId || engineerId == ""
        );
    };
    getEngineersFilterElement = () => {
        const {el, handleUserFilterOnSelect,} = this;
        const {allocatedUsers, filter} = this.state;

        let code = filter.activeTab;
        const teamId = this.getTeamId(code);
        const currentTeam = allocatedUsers.filter((u) => u.teamID == teamId);
        const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamId);

        return el(
            "select",
            {
                style: {marginTop: "20px", marginRight: "8px"},
                className: "float-right",
                key: "userFilter",
                value: filter.userFilter,
                width: 120,
                onChange: (event) => handleUserFilterOnSelect(event.target.value),
            },
            [
                el("option", {value: "", key: ""}, "All engineers"),
                ...[...currentTeam, ...otherTeams].map((p) =>
                    el(
                        "option",
                        {
                            value: p.userID,
                            key: "option" + p.userID,
                            className: teamId == p.teamID ? "in-team" : "",
                        },
                        p.userName
                    )
                ),
            ]
        );
    };
    deleteSR = (problem, code) => {

        this.apiCurrentActivityService.deleteSR(problem.cpCustomerProblemID).then(res => {
            this.loadQueue(code);
        }, error => {
            this.alert("You don't have permission to delete this SR or somthing wrong")
        })
    }
    createNewSR = (problem, code) => {
        window.location = `LogServiceRequest.php?customerproblemno=${problem.cpCustomerProblemID}`

    }
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

    async assignToRequest(toBeLoggedRequest) {
        const value = await this.prompt("Number of the Service Request to add this update to");
        if (!value) {
            return;
        }
        try {
            await this.apiCurrentActivityService.assignToBeLoggedToServiceRequest(toBeLoggedRequest.cpCustomerProblemID, value);
            this.loadQueue('TBL');
        } catch (err) {
            this.alert(err.toString());
        }
    }
    onCallBack=(problem)=>{        
        console.log(problem);  
        this.setState({showCallBackModal:true,currentProblem:problem});
    }
    getCallBackModal=()=>{
        const {showCallBackModal,currentProblem}=this.state;
        if(!showCallBackModal)
        return null;
        return <CallBackModal key="modal" show={showCallBackModal} 
        onClose={this.handleCallBackClose}
        problem={currentProblem}
        >
        </CallBackModal>
    }
    handleCallBackClose=(callActivityID)=>{
        console.log(callActivityID);
        //const {currentProblem}=this.state;
        this.setState({showCallBackModal:false});
        //if(callActivityID!=null)
        //    window.location=`SRActivity.php?action=editActivity&callActivityID=${callActivityID}&isFollow=1`;
        //window.location=`SRActivity.php?action=displayActivity&serviceRequestId=`+currentProblem.problemID;
    }
    render() {
        const {
            el,
            getTabsElement,
            isActive,
            loadQueue,
            //events
            getMoveElement,
            srDescription,
            allocateAdditionalTime,
            requestAdditionalTime,
            startWork,
            getAllocatedElement,
            getEngineersFilterElement,
            deleteSR,
            createNewSR,
            srCustomerDescription,
            getFollowOnElement,
            getCustomerOpenSR,
            assignToRequest,
            onCallBack
        } = this;
        const {
            helpDeskInboxFiltered,
            escalationInboxFiltered,
            smallProjectsInboxFiltered,
            salesInboxFiltered,
            projectsInboxFiltered,
            toBeLoggedInboxFiltered,
            pendingReopenedInboxFiltered,
            openSRInboxFiltered,
            allocatedUsers,
            currentUser,
            _showSpinner,
            filter,

        } = this.state;
        return el("div", {style: {backgroundColor: "white"}}, [
            <CallBackComponent  key='callback' team={filter.activeTab} customerID={this.state.openSrCustomerID}></CallBackComponent>,
            this.getCallBackModal(),
            this.getConfirm(),
            this.getAlert(),
            this.getPrompt(),
            this.getFollowOnElement(),
            el(Spinner, {key: "spinner", show: _showSpinner}),
            getTabsElement(),
            filter.activeTab !== 'TBL' && filter.activeTab !== "PR" ? getEngineersFilterElement() : null,
            isActive("H")
                ? el(InboxHelpDeskComponent, {
                    key: "help",
                    data: helpDeskInboxFiltered,
                    allocatedUsers,
                    currentUser,
                    loadQueue: loadQueue,
                    getMoveElement,
                    srDescription,
                    startWork,
                    allocateAdditionalTime,
                    requestAdditionalTime,
                    getAllocatedElement,
                    getFollowOnElement,
                    onCallBack
                })
                : null,

            isActive("E")
                ? el(InboxEscalationsComponent, {
                    key: "escalation",
                    data: escalationInboxFiltered,
                    allocatedUsers,
                    currentUser,
                    loadQueue: loadQueue,
                    getMoveElement,
                    srDescription,
                    startWork,
                    allocateAdditionalTime,
                    requestAdditionalTime,
                    getAllocatedElement,
                    onCallBack
                })
                : null,

            isActive("SP")
                ? el(InboxSmallProjectsComponent, {
                    key: "smallProjects",
                    data: smallProjectsInboxFiltered,
                    allocatedUsers,
                    currentUser,
                    loadQueue: loadQueue,
                    getMoveElement,
                    srDescription,
                    startWork,
                    allocateAdditionalTime,
                    requestAdditionalTime,
                    getAllocatedElement,
                    onCallBack
                })
                : null,

            isActive("S")
                ? el(InboxSalesComponent, {
                    key: "salesInbox",
                    data: salesInboxFiltered,
                    allocatedUsers,
                    currentUser,
                    loadQueue: loadQueue,
                    getMoveElement,
                    srDescription,
                    startWork,
                    allocateAdditionalTime,
                    requestAdditionalTime,
                    getAllocatedElement,
                    onCallBack
                })
                : null,

            isActive("P")
                ? el(InboxProjectsComponent, {
                    key: "projects",
                    data: projectsInboxFiltered,
                    allocatedUsers,
                    currentUser,
                    loadQueue: loadQueue,
                    getMoveElement,
                    srDescription,
                    startWork,
                    allocateAdditionalTime,
                    requestAdditionalTime,
                    getAllocatedElement,
                    onCallBack
                })
                : null,

            isActive("TBL")
                ? el(InboxToBeLoggedComponent, {
                    key: "toBeLogged",
                    data: toBeLoggedInboxFiltered,
                    deleteSR,
                    createNewSR,
                    srCustomerDescription,
                    assignToRequest: (toBeLoggedRequest) => this.assignToRequest(toBeLoggedRequest)
                })
                : null,

            isActive("PR")
                ? el(InboxPendingReopenedComponent, {
                    key: "pendingReopend",
                    data: pendingReopenedInboxFiltered,
                    deleteSR,
                    createNewSR,
                    srCustomerDescription,
                    loadQueue
                })
                : null,
            isActive("OSR")
                ? el(InboxOpenSRComponent, {
                    key: "openSR",
                    data: openSRInboxFiltered,
                    allocatedUsers,
                    currentUser,
                    loadQueue: loadQueue,
                    getMoveElement,
                    srDescription,
                    startWork,
                    allocateAdditionalTime,
                    requestAdditionalTime,
                    getAllocatedElement,
                    getFollowOnElement,
                    getCustomerOpenSR,
                    onCallBack
                })
                : null,
        ]);
    }
}

export default CurrentActivityReportComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainCurrentActivity");
    ReactDOM.render(React.createElement(CurrentActivityReportComponent), domContainer);
})

