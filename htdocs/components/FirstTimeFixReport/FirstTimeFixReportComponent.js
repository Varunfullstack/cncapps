import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import APIActivity from "../services/APIActivity.js";
import CustomerSearch from "../shared/CustomerSearch";
import APIUser from "../services/APIUser.js";
import APIFirstTimeFixReport from "./services/APIFirstTimeFixReport.js";
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';

class FirstTimeFixReportComponent extends MainComponent {
    el = React.createElement;
    apiActivity = new APIActivity();
    apiUser = new APIUser();
    apiFirstTimeFixReport = new APIFirstTimeFixReport();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            loading: false,
            engineers: [],
            filter: {
                engineerID: '',
                customerID: '',
                startDate: '',
                endDate: '',
            },
        };
    }

    setFilter = (field, value) => {
        const {filter} = this.state;
        filter[field] = value;
        this.setState({filter});
    };

    componentDidMount() {
        this.apiUser.getUsersByTeamLevel(1).then((engineers) => {
            this.setState({engineers});
        });
    }

    getSearchElement = () => {
        const {engineers} = this.state;
        return (
            <table>
                <tbody>
                <tr>
                    <td>Customer Name</td>
                    <td>
                        <CustomerSearch
                            onChange={(customer) => {
                                if (customer) {
                                    this.setFilter("customerID", customer.id)
                                }
                            }
                            }
                        ></CustomerSearch>
                    </td>
                </tr>
                <tr>
                    <td>Engineer</td>
                    <td>
                        <select
                            className="form-control"
                            value={this.state.filter.engineerID}
                            onChange={(event) =>
                                this.setFilter("engineerID", event.target.value)
                            }
                        >
                            <option value="">All</option>
                            {engineers.map((e) => (
                                <option key={e.userID}
                                        value={e.userID}
                                >
                                    {e.userName}
                                </option>
                            ))}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Start Date</td>
                    <td>
                        <input
                            className="form-control"
                            type="date"
                            onChange={(event) =>
                                this.setFilter("startDate", event.target.value)
                            }
                        ></input>
                    </td>
                </tr>
                <tr>
                    <td>End Date</td>
                    <td>
                        <input
                            className="form-control"
                            type="date"
                            onChange={(event) =>
                                this.setFilter("endDate", event.target.value)
                            }
                        ></input>
                    </td>
                </tr>
                <tr>
                    <td>
                        <button onClick={this.handleSearch}>Search</button>
                    </td>
                </tr>
                </tbody>
            </table>
        );
    };
    handleSearch = () => {
        const {filter} = this.state;
        this.setState({loading: true});
        if (this.isValid())
            Promise.all([
                this.apiFirstTimeFixReport.search(
                    filter.startDate,
                    filter.endDate,
                    filter.customerID,
                    filter.engineerID),
                this.apiActivity.getNotAttemptFirstTimeFix(
                    filter.startDate,
                    filter.endDate,
                    filter.customerID,
                    filter.engineerID
                ),
            ]).then(([firstTimeData, notAttemptFirstTimeFixData]) => {
                this.setState({firstTimeData, notAttemptFirstTimeFixData, loading: false});
            });
    };
    getFirstTimeElement = () => {
        const {firstTimeData} = this.state;
        return <table className="table table-striped"
                      style={{width: 600}}
        >
            <thead>
            <tr>
                <th>Name</th>
                <th>Raised</th>
                <th>Attempted</th>
                <th>Achieved</th>
            </tr>
            </thead>
            <tbody>
            {firstTimeData?.engineers.map(e =>
                <tr>
                    <td>{e.name}</td>
                    <td>{e.totalRaised}</td>
                    <td>{e.attemptedFirstTimeFix}</td>
                    <td>{e.firstTimeFix}</td>
                </tr>
            )}
            </tbody>
            <tfoot>
            <tr>
                <th>Total</th>
                <th>{firstTimeData?.phonedThroughRequests}</th>
                <th>{firstTimeData?.firstTimeFixAttemptedPct}%</th>
                <th>{firstTimeData?.firstTimeFixAchievedPct}%</th>
            </tr>
            </tfoot>
        </table>
    }
    getNotFirstTimeElement = () => {
        const {notAttemptFirstTimeFixData} = this.state;
        return <table className="table table-striped"
                      style={{width: 1200}}
        >
            <thead>
            <tr>
                <th style={{width: 100}}>Name</th>
                <th style={{width: 250}}>Customer</th>
                <th style={{width: 50}}>Problem</th>
                <th>Reason</th>
            </tr>
            </thead>
            <tbody>
            {notAttemptFirstTimeFixData?.map(e =>
                <tr>
                    <td>{e.userName}</td>
                    <td>{e.customerName}</td>
                    <td>
                        <a href={`SRActivity.php?serviceRequestId=${e.problemID}`}
                           target={"_blank"}
                        > {e.problemID}</a>
                    </td>
                    <td dangerouslySetInnerHTML={{__html: e.reason}}></td>
                </tr>
            )}
            </tbody>

        </table>
    }
    isValid = () => {
        const {filter} = this.state;
        if (filter.engineerID == "" && filter.customerID == "" && filter.startDate == "" && filter.endDate == "") {
            this.alert("You must enter at least one input for search")
            return false;
        }
        return true;
    }

    render() {
        return <div>
            {this.getAlert()}
            <Spinner show={this.state.loading}></Spinner>
            {this.getSearchElement()}
            <h3>Staff Figures</h3>
            {this.getFirstTimeElement()}
            <h3>FTF Not Offered Reasons</h3>
            {this.getNotFirstTimeElement()}
        </div>;
    }
}

export default FirstTimeFixReportComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactFirstTimeFixReport");
    if (domContainer)
        ReactDOM.render(React.createElement(FirstTimeFixReportComponent), domContainer);
});