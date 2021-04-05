"use strict";
import React from 'react';
import {Bar} from 'react-chartjs-2';
import {groupBy, sort} from '../../utils/utils';
import {KPIReportHelper} from '../helper/KPIReportHelper';
import moment from "moment";

export default class BillingConsultancyComponent extends React.Component {
    api;
    colors = [
        "rgb(242,234,94)", "rgb(62,31,132)", "rgb(153,112,127)", "rgb(81,34,165)", "rgb(133,76,93)", "rgb(114,8,41)", "rgb(235,179,149)", "rgb(168,97,103)", "rgb(47,147,51)", "rgb(11,130,44)", "rgb(190,208,244)", "rgb(187,59,48)", "rgb(209,137,215)", "rgb(169,87,182)", "rgb(48,11,22)", "rgb(252,229,185)", "rgb(153,140,250)", "rgb(252,218,103)", "rgb(80,90,87)", "rgb(166,231,210)", "rgb(221,157,11)", "rgb(152,39,248)", "rgb(59,126,25)", "rgb(165,219,24)", "rgb(26,90,131)", "rgb(198,64,21)", "rgb(204,240,107)", "rgb(120,8,87)", "rgb(127,102,18)", "rgb(33,34,123)", "rgb(251,30,23)", "rgb(68,122,155)", "rgb(164,175,166)", "rgb(37,3,160)", "rgb(85,16,223)", "rgb(232,251,163)", "rgb(133,12,217)", "rgb(107,113,86)", "rgb(213,246,36)", "rgb(225,145,134)", "rgb(91,209,241)", "rgb(56,100,154)", "rgb(183,94,70)", "rgb(162,70,55)", "rgb(240,82,232)"
    ];
    helper = new KPIReportHelper();
    engineerTeam = new Map();

    constructor(props, context) {
        super(props, context);
        this.state = {
            months: []
        }
    }

    componentDidMount() {
    }

    componentDidUpdate(prevProps, prevState, snapshot) {

    }

    getEngineerTeam(engineerName) {
        const {consultants} = this.props;
        if (!this.engineerTeam.has(engineerName)) {
            const foundConsultant = consultants.find(c => c.name === engineerName);
            if (!foundConsultant) {
                return null;
            }
            this.engineerTeam.set(engineerName, foundConsultant);
        }
        return this.engineerTeam.get(engineerName).teamId;
    }

    getMonthlyLabels() {
        const from = moment(this.props.filter.from, 'YYYY-MM-DD');
        if (!from.isValid()) {
            return [];
        }
        const to = moment(this.props.filter.to, 'YYYY-MM-DD');
        if (!to.isValid()) {
            return [];
        }
        const monthLabels = [];
        while (from.isSameOrBefore(to, "month")) {
            monthLabels.push(from.format("MMM YYYY"));
            from.add(1, "month");
        }
        return monthLabels;
    }

    getMonthlyData(data) {
        //get data by teams
        const borderWidth = 2;
        const fill = false;
        //group by engineer
        const engineers = groupBy(data, 'engineer');

        let engData = engineers.map((e, index) => {
            return {
                label: e.groupName,
                data: this.getMonthlyLabels().map(monthLabel => {
                    const date = moment(monthLabel, 'MMM YYYY');
                    let value = null;
                    const foundItem = e.items.find(x => x.inh_date_printed_yearmonth === date.format('YYYYMM'));
                    if (foundItem) {
                        value = foundItem.amount;
                    }
                    return value;
                }),
                backgroundColor: this.colors[index],
                borderColor: this.colors[index],
                borderWidth,
                fill,
            };
        });
        engData = sort(engData, 'label');
        return engData;
    }

    inTeam = (teamId, name) => {
        const {consultants} = this.props;
        const user = consultants.find(c => c.teamId == teamId && c.name == name);
        return !!user;
    }
    getChartData = (data, filter) => {
        let filterData = [];
        let dataLabels = [];
        data = this.getDataFilter(data);


        filterData = this.getMonthlyData(data);
        dataLabels = this.getMonthlyLabels(data);

        return {filterData, dataLabels};
    };
    getDataFilter = (data) => {
        const teams = {
            1: 'hd',
            2: 'es',
            4: 'sp',
            5: 'p'
        };
        const {filter} = this.props;
        let filteredData = data;
        if (filter.consName) {
            filteredData = filteredData.filter(d => d.engineer === filter.consName);
        }
        filteredData = filteredData.filter(d => {
            const engineerTeam = this.getEngineerTeam(d.engineer);
            const teamToCheck = teams[engineerTeam];
            if (!teamToCheck) {
                return true;
            }
            return filter.teams[teamToCheck]
        });

        return filteredData;
    }
    getChart = () => {
        const {data, filter} = this.props;
        const {filterData, dataLabels} = this.getChartData(data, filter);
        const chartData = {
            labels: dataLabels,
            datasets: filterData,
        };
        const options = {
            maintainAspectRatio: false,
            scales: {
                yAxes: [
                    {
                        ticks: {
                            beginAtZero: true,
                        },
                    },
                ],
            },
        };

        return <div style={{height: "80vh", width: "85vw"}}>
            <Bar data={chartData}
                 options={options}
            />
        </div>
            ;
    };
    getSummaryElement = () => {
        let {data} = this.props;
        data = this.getDataFilter(data);
        const engineers = groupBy(sort(data, 'engineer'), 'engineer');
        engineers.map(e => {
            e.average = (e.items.reduce((curr, prev) => {
                curr += parseFloat(prev.amount);
                return curr;
            }, 0) / e.items.length).toFixed(1);
        });

        return <div>
            <h3>Monthly Average Over The Selected Period</h3>
            <table className="table table-striped"
                   style={{width: 300}}
            >
                <tbody>
                {engineers.map(e => <tr>
                    <td>{e.groupName}</td>
                    <td>{e.average}</td>
                </tr>)}
                </tbody>
            </table>
        </div>
    }

    render() {
        return (
            <div>
                {this.getChart()}
                {this.getSummaryElement()}
            </div>
        );
    }
}
 