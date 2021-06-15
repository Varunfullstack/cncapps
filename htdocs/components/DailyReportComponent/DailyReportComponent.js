import MainComponent from "../shared/MainComponent";
import Toggle from "../shared/Toggle";
import React from 'react';
import ReactDOM from 'react-dom';
import Spinner from "../shared/Spinner/Spinner";
import APIDailyReport from "./services/APIDailyReport";

import './../style.css';
import './DailyReportComponent.css';
import DetailsComponent from "./subComponents/DetailsComponent";
import SummaryComponent from "./subComponents/SummaryComponent";

class DailyReportComponent extends MainComponent {
    el = React.createElement;
    tabs = [];
    api = new APIDailyReport();
    TAB_DETAILS = 1;
    TAB_SUMMARY = 2;
    TAB_GRAPH = 3;

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
                p5: true,
                activeTab: 1,
                daysAgo: 7,
            },
            data: [],

        };
        this.tabs = [
            {id: this.TAB_DETAILS, title: "Details", icon: null},
            {id: this.TAB_SUMMARY, title: "Summary", icon: null},
            {id: this.TAB_GRAPH, title: "Graph", icon: null},
        ];
    }

    componentDidMount() {
        this.loadFilterFromStorage();
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
    loadFilterFromStorage = () => {
        let filter = localStorage.getItem("DailyReport");
        if (filter) filter = JSON.parse(filter);
        else filter = this.state.filter;
        this.setState({filter}, () => {
            this.loadTab(filter.activeTab)
        });
    };
    setFilterValue = (property, value) => {
        const {filter} = this.state;
        filter[property] = value;
        this.setState({filter}, () => this.saveFilter(filter));
    };

    saveFilter(filter) {
        localStorage.setItem("DailyReport", JSON.stringify(filter));
        this.loadTab(filter.activeTab);
    }

    getFilterElement = () => {
        const {filter} = this.state;
        const shouldBeHidden = [].findIndex(x => x === filter.activeTab) > -1;

        return (
            <div className="m-5">
                {
                    shouldBeHidden ? '' :
                        <React.Fragment>

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
                            <label className="mr-3 ml-5">Open for at least X days</label>
                            <select value={filter.daysAgo}
                                    onChange={() => this.setFilterValue("daysAgo", event.target.value)}
                            >
                                {
                                    [0, 1, 2, 3, 4, 5, 6, 7].reverse().map(x => (
                                            <option key={x}
                                                    value={x}
                                            >{x}</option>

                                        )
                                    )
                                }

                            </select>

                        </React.Fragment>
                }

            </div>
        );
    }
    loadTab = async (id) => {
        this.setState({data: [], showSpinner: true});
        const {filter} = this.state;
        switch (id) {
            case this.TAB_DETAILS:
                this.api.getOutStandingIncidents(filter).then(data => {
                    this.setState({data, showSpinner: false});
                });
                break;
            case this.TAB_SUMMARY:
                const years = await this.api.getYears();
                const data = {years}
                this.setState({data, showSpinner: false});
                break;
            case this.TAB_GRAPH:
                this.setState({showSpinner: false});
                break;
        }


    };
    getActiveTab = () => {
        const {filter, data} = this.state;
        switch (filter.activeTab) {
            case this.TAB_DETAILS:
                return <DetailsComponent data={data}
                                         onChange={() => {
                                             this.loadTab(filter.activeTab)
                                         }}
                />
            case this.TAB_SUMMARY:
                return <SummaryComponent data={data}/>
            case this.TAB_GRAPH:
                return <iframe src="/DailyReport.php?action=showGraphs&popup"
                               style={{border: 0, width: 1200, height: 600}}
                />
        }
    }

    render() {
        const {el} = this;
        const {filter} = this.state;
        return el("div", null,
            el(Spinner, {key: "spinner", show: this.state.showSpinner}),
            this.getAlert(),
            this.getTabsElement(),
            filter.activeTab == 1 ? this.getFilterElement() : null,
            this.getActiveTab()
        );
    }
}

export default DailyReportComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainDailyReport");
        ReactDOM.render(React.createElement(DailyReportComponent), domContainer);
    }
)
