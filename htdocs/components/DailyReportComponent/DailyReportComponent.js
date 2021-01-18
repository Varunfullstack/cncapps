import MainComponent from "../shared/MainComponent";
import Toggle from "../shared/Toggle";
import React from 'react';
import ReactDOM from 'react-dom';
import Spinner from "../shared/Spinner/Spinner";
import APIDailyReport from "./services/APIDailyReport";
 
import './../style.css';
import 'DailyReportComponent.css';

class DailyReportComponent extends MainComponent {
    el = React.createElement;
    tabs = [];
    api = new APIDailyReport(); 
    TAB_DETAILS=1;
    TAB_SUMMARY=2;       
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
            {id: this.TAB_DETAILS, title: "Detials", icon: null},
            {id: this.TAB_SUMMARY, title: "Summary", icon: null},            
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
        let filter = localStorage.getItem("SDManagerDashboardFilter");
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
                </select>
            </div>
        );
    }
    loadTab = (id) => {
        switch(id){
            case this.TAB_DETAILS:
                console.log('load details');
                break;
            case this.TAB_SUMMARY:
                break;
        }

    };    
    
    render() {
        const {el} = this;
        return el("div", null,
            el(Spinner, {key: "spinner", show: this.state.showSpinner}),
            this.getAlert(),            
            this.getFilterElement(),
            this.getTabsElement(),            
        );
    }
}

export default DailyReportComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainDailyReport");
        ReactDOM.render(React.createElement(DailyReportComponent), domContainer);
    }
)
