"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import {Line} from 'react-chartjs-2';
import {ReportType} from '../KPIReportComponent';
import {KPIReportHelper} from '../helper/KPIReportHelper';
import moment from "moment";

export default class DailyContactComponent extends MainComponent {
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
        this.colors = {
            Inbound: {
                background: "rgb(238, 126, 48)",
                border: "rgb(238, 126, 48)",
            },
            Outbound: {
                background: "rgb(69, 115, 195)",
                border: "rgb(69, 115, 195)",
            },            
        };
    }

    static getDerivedStateFromProps(props, state) {
        return {...state, ...props};
    }

    componentDidMount() {
    }

    getDailyData(data) {
        const borderWidth = 2;
        const fill = false;
        return [
            {
                label: "Inbound",
                data: data.map((d) => d.Inbound),
                backgroundColor: this.colors.Inbound.background,
                borderColor: this.colors.Inbound.border,
                borderWidth,
                fill,
            },
            {
                label: "Outbound",
                data: data.map((d) => d.Outbound),
                backgroundColor: this.colors.Outbound.background,
                borderColor: this.colors.Outbound.border,
                borderWidth,
                fill,
            },            
        ];
    }


    getChartData = (data, filter) => {
        let filterData = [];
        let dataLabels = [];
        if (filter.resultType == ReportType.Daily) {
            filterData = this.getDailyData(data);
            dataLabels = data.map((a) => a.date);
        }
        if (filter.resultType == ReportType.Weekly) {
            filterData = this.getWeeklyData(data);
            dataLabels = this.getWeeklyLabels(data);
        }
        if (filter.resultType == ReportType.Monthly) {
            filterData = this.getMonthlyData(data);
            dataLabels = this.getMonthlyLabels(data);
        }
        console.log(filterData, dataLabels);
        return {filterData, dataLabels};
    };

    getWeeklyLabels(data) {
        return this.helper.getWeeks(data, "Inbound").map((d) => d.week);
    }

    getWeeklyData(data) {
        const borderWidth = 2;
        const fill = false;
        return [
            {
                label: "Inbound",
                data: this.getWeeklyAveragedData(data, "Inbound"),
                backgroundColor: this.colors.Inbound.background,
                borderColor: this.colors.Inbound.border,
                borderWidth,
                fill,
            },
            {
                label: "Outbound",
                data: this.getWeeklyAveragedData(data, "Outbound"),
                backgroundColor: this.colors.Outbound.background,
                borderColor: this.colors.Outbound.border,
                borderWidth,
                fill,
            },            
        ];
    }

    getMonthlyData(data) {
        const borderWidth = 2;
        const fill = false;
        return [
            {
                label: "Inbound",
                data: this.getMonthlyAveragedData(data, "Inbound"),
                backgroundColor: this.colors.Inbound.background,
                borderColor: this.colors.Inbound.border,
                borderWidth,
                fill,
            },
            {
                label: "Outbound",
                data: this.getMonthlyAveragedData(data, "Outbound"),
                backgroundColor: this.colors.Outbound.background,
                borderColor: this.colors.Outbound.border,
                borderWidth,
                fill,
            },            
        ];
    }

    getMonthlyLabels(data) {
        return this.helper.getMonths(data, "raisedToday").map(
            (d) => d.month
        );
    }

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
                            beginAtZero: true,
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

    getAveragedDataByKey(data, property, getProperKey) {
        const countsAndSums = data.reduce((acc, item) => {
            const generatedKey = getProperKey(item);
            if (!(generatedKey in acc)) {
                acc[generatedKey] = {
                    count: 0,
                    sum: 0
                }
            }

            acc[generatedKey].count++;
            acc[generatedKey].sum += item[property];
            return acc;
        }, {});
        return Object.keys(countsAndSums).map(key => {
            if (!countsAndSums[key].count) {
                return 0;
            }
            return countsAndSums[key].sum / countsAndSums[key].count;
        });
    }

    getWeeklyAveragedData(data, property) {
        return this.getAveragedDataByKey(data, property, (item) => {
            const dt = moment(item.date);
            return dt.format('WYYYY');
        })
    }

    getMonthlyAveragedData(data, property) {
        return this.getAveragedDataByKey(data, property, (item) => {
            const dt = moment(item.date);
            return dt.format('MYYYY');
        })
    }
}
 