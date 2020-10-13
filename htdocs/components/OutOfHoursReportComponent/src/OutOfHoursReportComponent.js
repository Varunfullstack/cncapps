import React from "react";
import ReactDOM from 'react-dom';
import moment from 'moment';

const MYSQL_DATETIME_FORMAT = 'YYYY-MM-DD HH:mm:ss';

class OutOfHoursReportComponent extends React.Component {
    MYSQL_DATE_FORMAT = 'YYYY-MM-DD';
    monthNames = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ]

    constructor(props) {
        super(props);
        this.state = {
            selectedYear: '',
            reportData: [],
            years: []
        };
    }

    componentDidMount() {
        this.fetchYears();
    }

    fetchYears() {
        fetch(`?action=getCallOutYears`)
            .then(res => res.json())
            .then(response => {
                if (response.error) {
                    throw new Error(response.message);
                }
                this.setState({
                    years: response.data,
                    selectedYear: response.data[response.data.length - 1]
                })
            })
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (this.state.selectedYear !== prevState.selectedYear) {
            console.log(this.state.selectedYear, prevState.selectedYear);
            this.fetchData();
        }
    }

    fetchData() {
        const {selectedYear} = this.state;
        const startDate = moment().year(selectedYear).startOf('year');
        const endDate = moment().year(selectedYear).endOf('year');
        fetch(`?action=getOutOfHoursData&startDate=${startDate.format(this.MYSQL_DATE_FORMAT)}&endDate=${endDate.format(this.MYSQL_DATE_FORMAT)}`)
            .then(res => res.json())
            .then(response => {
                if (response.error) {
                    throw new Error(response.message);
                }
                this.setState({
                    reportData: response.data
                })
            })
            .catch(error => {
                alert(`Failed put retrieve data: ${error}`);
            })
    }

    renderReportData() {
        const {reportData} = this.state;
        const totalRow = {
            monthData: new Array(12).fill(0),
            total: 0
        }
        const dataTable = reportData.reduce((acc, outOfHoursItem) => {

                const callOutDate = moment(outOfHoursItem.createdAt, MYSQL_DATETIME_FORMAT)

                if (!(outOfHoursItem.customerName in acc)) {
                    acc[outOfHoursItem.customerName] = {
                        monthData: new Array(12).fill(0),
                        total: 0
                    }
                }

                const currentDataStructure = acc[outOfHoursItem.customerName];
                currentDataStructure.monthData[callOutDate.month()]++;
                currentDataStructure.total++;
                totalRow.monthData[callOutDate.month()]++;
                totalRow.total++;
                return acc;
            },
            {}
        );
        console.log(dataTable);
        return [
            ...Object.keys(dataTable).map(customerName => {
                return (
                    <tr key={customerName}>
                        <td key='customerName'>{customerName}</td>
                        {dataTable[customerName].monthData.map((x, index) => (<td key={index}>{x}</td>))}
                        <td key='customerTotal'>{dataTable[customerName].total}</td>
                    </tr>
                )
            }),
            <tr key="totalRow">
                <td key='totalRow'>Total</td>
                {totalRow.monthData.map((x, index) => (<td key={index}>{x}</td>))}
                <td key='grandTotal'>{totalRow.total}</td>
            </tr>
        ];
    }

    render() {
        const {selectedYear, years} = this.state;

        return (
            <div>
                <div>

                    <label>
                        <select value={selectedYear}
                                onChange={($event) => {
                                    this.setState({selectedYear: $event.currentTarget.value})
                                }}
                        >
                            {years.map(year => (<option key={year}
                                                        value={year}
                            >{year}</option>))}
                        </select>
                        <span>
                    Year
                </span>
                    </label>

                </div>
                <br/>
                <table>
                    <thead>
                    <tr>
                        <th key="customer">
                            Customer
                        </th>
                        {this.monthNames.map(mn => {
                            return <th key={mn}>{mn}</th>
                        })}
                        <th key='Customer Total'>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    {this.renderReportData()}
                    </tbody>
                </table>
            </div>
        )
    }
}


export default OutOfHoursReportComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#react-out-of-hours-report');
    ReactDOM.render(React.createElement(OutOfHoursReportComponent), domContainer);
})