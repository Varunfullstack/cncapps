"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import {Line} from 'react-chartjs-2';
import {ReportType} from '../KPIReportComponent';
import {KPIReportHelper} from '../helper/KPIReportHelper';

const BORDER_WIDTH = 2;
const FILL = false;
const COLORS_LIST = ["#7B241C", "#633974", "#1A5276", "#117864", "#117864", "#9A7D0A", "#935116", "#979A9A", "#5F6A6A", "#212F3C", "#943126", "#5B2C6F", "#21618C", "#0E6655", "#1D8348", "#873600", "#797D7F", "#515A5A", "#1C2833",];


export default class ServiceRequestsRaisedByContract extends MainComponent {
    api;
    colors;
    helper = new KPIReportHelper();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            data: this.props.data,
            filter: this.props.filter
        };
    }

    static getDerivedStateFromProps(props, state) {
        return {...state, ...props};
    }

    componentDidMount() {
    }

    getContracts(data) {
        const contractsMap = data.reduce((acc, item) => {
            if (!(item.contractItem in acc)) {
                acc[item.contractItem] = {
                    contractItemId: item.contractItem,
                    contractDescription: item.contractDescription,
                };
            }
            return acc;
        }, {});
        return Object.keys(contractsMap).sort((a, b) => a.localeCompare(b)).map(key => contractsMap[key]);
    }

    getDataForContract(data, contractId, dates) {
        const valuesMap = data.filter(x => x.contractItem === contractId).reduce((acc, item) => {
            if (!(item.date in acc)) {
                acc[item.date] = item.total;
            }
            return acc;
        }, {});
        return dates.map(x => valuesMap[x] || 0);
    }


    getDailyData(data, contracts, dates) {
        return this.getContractData(data, contracts, dates, this.getDataForContract);
    }


    getContractData(data, contracts, dates, contractDataFunction) {
        return contracts.map((contractItem, index) => {
            const contractData = contractDataFunction(data, contractItem.contractItemId, dates);
            return this.getDataItem(contractItem, contractData, COLORS_LIST[index % COLORS_LIST.length]);
        })
    }

    getWeeklyLabels(data) {
        const weekMap = data.reduce((acc, item) => {
            const dt = moment(item.date);
            const weekDate = dt.week() + dt.year();
            if (!(weekDate in acc)) {
                acc[weekDate] = item.date;
            }
            return acc;
        }, {});
        return Object.keys(weekMap).map(x => ({value: x, label: weekMap[x]}));
    }

    getWeeklyDataForContract(data, contractId, weeklyDates) {
        const valuesMap = data.filter(x => x.contractItem === contractId).reduce((acc, item) => {
            const dt = moment(item.date);
            const weekLabel = dt.week() + dt.year()
            if (!(weekLabel in acc)) {
                acc[weekLabel] = 0;
            }
            acc[weekLabel] += item.total;
            return acc;
        }, {});
        return weeklyDates.map(x => valuesMap[x] || 0);
    }

    getWeeklyData(data, contracts, weeklyDates) {
        return this.getContractData(data, contracts, weeklyDates, this.getWeeklyDataForContract);
    }

    getDataItem(contractItem, contractData, color) {
        return {
            label: contractItem.contractDescription,
            data: contractData,
            backgroundColor: color,
            borderColor: color,
            borderWidth: BORDER_WIDTH,
            fill: FILL
        }
    }

    getMonthlyLabels(data) {
        return Object.keys(data.reduce((acc, item) => {
                const dt = moment(item.date);
                const monthLabel = dt.format("MMM YYYY");
                if (!(monthLabel in acc)) {
                    acc[monthLabel] = true;
                }
                return acc;
            }, {})
        );
    }

    getMonthlyDataForContract(data, contractId, monthlyDates) {
        const valuesMap = data.filter(x => x.contractItem === contractId).reduce((acc, item) => {
            const dt = moment(item.date);
            const monthLabel = dt.format("MMM YYYY");
            if (!(monthLabel in acc)) {
                acc[monthLabel] = 0;
            }
            acc[monthLabel] += item.total;
            return acc;
        }, {});
        return monthlyDates.map(x => valuesMap[x] || 0);
    }

    getMonthlyData(data, contracts, monthlyDates) {
        return this.getContractData(data, contracts, monthlyDates, this.getMonthlyDataForContract);
    }

    getDates(data) {
        const datesMap = data.reduce((acc, item) => {
            if (!(item.date in acc)) {
                acc[item.date] = true;
            }
            return acc;
        }, {});
        return Object.keys(datesMap);
    }

    getChartData = (data, filter) => {
        let filterData = [];
        let dataLabels = [];
        const contracts = this.getContracts(data);

        if (filter.resultType === ReportType.Daily) {
            dataLabels = this.getDates(data);
            filterData = this.getDailyData(data, contracts, dataLabels);
        }
        if (filter.resultType === ReportType.Weekly) {
            const weeklyDates = this.getWeeklyLabels(data);
            dataLabels = weeklyDates.map(x => x.label);
            filterData = this.getWeeklyData(data, contracts, weeklyDates.map(x => x.value));

        }
        if (filter.resultType === ReportType.Monthly) {
            dataLabels = this.getMonthlyLabels(data);
            filterData = this.getMonthlyData(data, contracts, dataLabels);
        }
        return {filterData, dataLabels};
    };
    getChart = () => {
        const {data, filter} = this.state;
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
                            beginAtZero: false,
                        },
                    },
                ],
            },
        };
        return <div style={{height: "80vh", width: "85vw"}}>
            <Line data={chartData}
                  options={options}
            />
        </div>

            ;
    };

    render() {
        return (
            <div>
                {this.getChart()}
            </div>
        );
    }
}
