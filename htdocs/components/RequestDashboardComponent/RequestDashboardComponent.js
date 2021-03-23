import MainComponent from "../shared/MainComponent";
import Toggle from "../shared/Toggle";
import React from 'react';
import './../style.css';
import './RequestDashboardComponent.css';
import Spinner from "../shared/Spinner/Spinner";
import ReactDOM from 'react-dom';

import TimeRequestComponent from "./subComponents/TimeRequestComponent";
import ChangeRequestComponent from "./subComponents/ChangeRequestComponent";
import SalesRequestComponent from "./subComponents/SalesRequestComponent";
import APIRequestDashboard from "./services/APIRequestDashboard";

class RequestDashboardComponent extends MainComponent {
    el = React.createElement;
    tabs = [];
    intervalRef;


    TIME_REQUEST = 1;
    CHANGE_REQUEST = 2;
    SALES_REQUEST = 3;
    AUTO_RELOAD_TIME = 60 * 1000;

    api;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            filter: {
                hd: true,
                es: true,
                sp: true,
                p: true,
                activeTab: 1,
                limit: 10
            },
            queueData: [],
            allocatedUsers: [],
            salesRequests: [],
            changRequests: [],
            timeRequests: [],
            tabs: [
                {id: this.TIME_REQUEST, title: "Time Requests", icon: null, hasTeamsFilters: true},
                {id: this.CHANGE_REQUEST, title: "Change Requests", icon: null, hasTeamsFilters: true},
                {id: this.SALES_REQUEST, title: "Sales Requests", icon: null, hasTeamsFilters: false},
            ]
        };

        this.api = new APIRequestDashboard();
    }

    componentDidMount() {
        this.loadFilterFromStorage();

    }

    componentWillUnmount() {
        if (this.intervalRef)
            clearInterval(this.intervalRef);
    }

    setIcon = (tab, icon) => {
        const {tabs} = this.state;
        const indx = tabs.map(t => t.id).indexOf(tab);
        tabs[indx].icon = icon;
        this.setState({tabs});
    }
    checkHaveData = () => {
        if (this.state.timeRequests.length > 0)
            this.setIcon(this.TIME_REQUEST, "fal fa-asterisk");
        if (this.state.changRequests.length > 0)
            this.setIcon(this.CHANGE_REQUEST, "fal fa-asterisk");
        if (this.state.salesRequests.length > 0)
            this.setIcon(this.SALES_REQUEST, "fal fa-asterisk");
    }
    loadData = () => {
        const {filter} = this.state;
        this.setState({showSpinner: true});
        Promise.all([
            this.api.getTimeRequest(filter),
            this.api.getSalesRequest(filter),
            this.api.getChangeRequest(filter)
        ]).then(([timeRequests, salesRequests, changRequests]) => {
            this.setState({timeRequests, salesRequests, changRequests, showSpinner: false}, () => this.checkHaveData());
        })

    }
    loadTab = () => {
        const {filter} = this.state;
        switch (filter.activeTab) {
            case this.TIME_REQUEST:
                this.getTimeRequests();
                break;
            case this.CHANGE_REQUEST:
                this.getChangeRequests();
                break;
            case this.SALES_REQUEST:
                this.getSalesRequests();
        }

    }
    getTimeRequests = () => {
        const {filter} = this.state;
        if (filter != null) {
            this.setState({showSpinner: true});
            this.api.getTimeRequest(filter).then(timeRequests => {
                this.setState({timeRequests, showSpinner: false});
            })
        }
    }
    getChangeRequests = () => {
        const {filter} = this.state;
        if (filter != null) {
            this.setState({showSpinner: true});
            this.api.getChangeRequest(filter).then(changRequests => {
                this.setState({changRequests, showSpinner: false});
            })
        }
    }
    getSalesRequests = () => {
        const {filter} = this.state;
        if (filter != null) {
            this.setState({showSpinner: true});
            this.api.getSalesRequest(filter).then(salesRequests => {
                this.setState({salesRequests, showSpinner: false});
            })
        }
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
            this.loadData();
        }, this.AUTO_RELOAD_TIME)
    }

    getTabsElement = () => {
        const {el} = this;
        const {tabs} = this.state;
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
                                marginTop: "-15px",
                                marginRight: "-5px",

                                color: "#000",
                            },
                        })
                        : null
                );
            })
        );
    };
    loadFilterFromStorage = () => {
        let filter = localStorage.getItem("RequestDashboardFilter");
        if (filter) filter = JSON.parse(filter);
        else filter = this.state.filter;
        this.setState({filter}, () => {
            this.checkAutoReloading();
            this.loadData();
        });
    };
    setFilterValue = (property, value) => {
        const {filter} = this.state;
        filter[property] = value;
        this.setState({filter}, () => this.saveFilter(filter));
    };

    saveFilter(filter) {
        localStorage.setItem("RequestDashboardFilter", JSON.stringify(filter));
        this.loadTab();
    }

    getFilterElement = () => {
        const {filter, tabs} = this.state;
        const tab = tabs.find(t => t.id == filter.activeTab);

        return (
            <div className="m-5">
                {
                    tab.hasTeamsFilters ? <React.Fragment>
                        <label className="mr-3 ml-5">HD</label>
                        <Toggle checked={filter.hd}
                                onChange={() => this.setFilterValue("hd", !filter.hd)}
                        />
                        <label className="mr-3 ml-5">ES</label>
                        <Toggle checked={filter.es}
                                onChange={() => this.setFilterValue("es", !filter.es)}
                        />
                        <label className="mr-3 ml-5">SP</label>
                        <Toggle checked={filter.sp}
                                onChange={() => this.setFilterValue("sp", !filter.sp)}
                        />
                        <label className="mr-3 ml-5">P</label>
                        <Toggle checked={filter.p}
                                onChange={() => this.setFilterValue("p", !filter.p)}
                        />
                    </React.Fragment> : null
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
                </select>
            </div>
        );
    }


    addToolTip = (element, title) => {
        return this.el(
            "div",
            {className: "tooltip"},
            element,
            this.el("div", {className: "tooltiptext tooltip-bottom"}, title)
        );
    };

    getActiveTab = () => {
        const {filter, timeRequests, changRequests, salesRequests} = this.state;
        switch (filter.activeTab) {
            case this.TIME_REQUEST:
                return <TimeRequestComponent filter={filter}
                                             activities={timeRequests}
                                             onRefresh={this.loadTab}
                />
            case this.CHANGE_REQUEST:
                return <ChangeRequestComponent filter={filter}
                                               activities={changRequests}
                                               onRefresh={this.loadTab}
                />
            case this.SALES_REQUEST:
                return <SalesRequestComponent filter={filter}
                                              activities={salesRequests}
                                              onRefresh={this.loadTab}
                />
        }
    }

    render() {

        return (
            <div>

                <Spinner key="spinner"
                         show={this.state.showSpinner}
                >
                </Spinner>
                {this.getFilterElement()}
                {this.getTabsElement()}
                {this.getActiveTab()}
            </div>
        );
    }
}

export default RequestDashboardComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactRequestDashboard");
        ReactDOM.render(React.createElement(RequestDashboardComponent), domContainer);
    }
)
