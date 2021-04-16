import MainComponent from "../../shared/MainComponent";
import React from "react";
import APICustomerInfo from "../services/APICustomerInfo";
import Spinner from "../../shared/Spinner/Spinner";
import "../../shared/table/table.css";
import {groupBy} from "../../utils/utils";
import moment from "moment";

class TwentyFourHoursSupportComponent extends MainComponent {
    el = React.createElement;
    api = new APICustomerInfo();

    constructor(props) {
        super(props);
        this.state = {
            showSpinner: false,
            customers: [],
            callOutYears: [],
            selectedYear: "",
            outOfHours: []
        };
    }

    componentWillUnmount() {
    }

    componentDidMount() {
        this.getData();
    }

    getCallOutData = async () => {
        const selectedYear = this.state.selectedYear;
        let outOfHours = [];
        const start = moment(selectedYear, "YYYY")
            .startOf("year")
            .format("YYYY-MM-DD");
        const end = moment(selectedYear, "YYYY").endOf("year").format("YYYY-MM-DD");
        outOfHours = await this.api.getOutOfHours(start, end).then(data => {
            data.map(d => d.month = moment(d.createdAt).format("MMM"));
            const customers = groupBy(data, 'customerName');
            return customers.map(c => {
                c.months = groupBy(c.items, 'month')
                return c;
            })
        });
        this.setState({outOfHours});
    }

    getData = async () => {
        this.setState({showSpinner: true});
        const customers = await this.api.get24HourSupportCustomers();
        const callOutYears = await this.api.getCallOutYears();
        this.setState({
            showSpinner: false,
            customers,
            callOutYears,
            selectedYear: callOutYears[0]["years"]
        }, () => {
            this.getCallOutData();
        });
    };

    getCustomersElement = () => {
        const {customers} = this.state;
        return (
            <div>
                <p>
                    Shows the list of customers with out of hours support.

                </p>
                <p className="mb-4">
                    A PIN number will display in the portal allowing them access to this support service.
                </p>
                <table className="table table-striped">
                    <tbody>
                    {customers.map((c) => (
                        <tr key={c.customerID}>
                            <td>
                                <a
                                    target="_blank"
                                    href={`Customer.php?action=dispEdit&customerID=${c.customerID}`}
                                >
                                    {c.customerName}
                                </a>
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            </div>
        );
    };

    onYearChanged = (event) => {
        this.setState({selectedYear: event.target.value}, () => {
            this.getCallOutData();
        })
    }

    getCallOutHistory = () => {
        const {selectedYear, callOutYears, outOfHours} = this.state;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return <div>
            <h3>Call Out History</h3>
            <select id='years'
                    value={selectedYear}
                    onChange={this.onYearChanged}
            >
                {
                    callOutYears.map(y => <option value={y.years}
                                                  key={y.years}
                    >{y.years}</option>)
                }
            </select>
            <table className="table table-striped"
                   style={{width: 800}}
            >
                <thead>
                <tr>
                    <th>Customer</th>
                    {months.map(m => <th key={m}>{m}</th>)}
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                {
                    outOfHours.map(c => <tr key={c.groupName}>
                            <td>{c.groupName}</td>
                            {months.map(m => <td key={m}>{this.getMonthValue(c.months, m)}</td>)}
                            <th>{c.items.length}</th>
                        </tr>
                    )
                }
                </tbody>
                <tfoot>
                <tr>
                    <th>Total</th>
                    {months.map(m => <th key={m}>{this.getMonthTotal(m)}</th>)}
                    <th>{this.getTotal()}</th>
                </tr>
                </tfoot>
            </table>
        </div>
    }

    getMonthValue(items, month) {
        const monthItems = items.find(i => i.groupName == month);
        if (monthItems)
            return monthItems.items.length;
        else return 0;
    }

    getMonthTotal(month) {
        const {outOfHours} = this.state;
        const allitems = [].concat(...outOfHours.map(o => o.items));
        return allitems.filter(i => i.month == month).length || 0;
    }

    getTotal() {
        const {outOfHours} = this.state;
        const allitems = [].concat(...outOfHours.map(o => o.items));
        return allitems.length || 0;
    }

    render() {
        return (
            <div>
                <Spinner key="spinner"
                         show={this.state.showSpinner}
                ></Spinner>
                <div style={{display: 'flex', flexDirection: 'row', justifyContent: 'space-between', maxWidth: 1300}}>
                    {this.getCustomersElement()}
                    {this.getCallOutHistory()}
                </div>

            </div>
        );
    }
}

export default TwentyFourHoursSupportComponent;
