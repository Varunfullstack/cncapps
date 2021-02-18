"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import {Line} from 'react-chartjs-2';
import {ReportType} from '../KPIReportComponent';
import {KPIReportHelper} from '../helper/KPIReportHelper';
import moment from "moment";

export default class ServiceRequestComponent extends MainComponent {
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
            uniqueCustomer: {
                background: "rgb(238, 126, 48)",
                border: "rgb(238, 126, 48)",
            },
            raisedToday: {
                background: "rgb(69, 115, 195)",
                border: "rgb(69, 115, 195)",
            },
            startedToday: {background: "rgb(255, 192, 0)", border: "rgb(255, 192, 0)"},
            fixedToday: {
                background: "rgb(172, 172, 172)",
                border: "rgb(172, 172, 172)",
            },
            reopenToday: {
                background: "rgb(165, 42, 42)",
                border: "rgb(165, 42, 42)",
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
                label: "Unique Customers",
                data: data.map((d) => d.uniqueCustomer),
                backgroundColor: this.colors.uniqueCustomer.background,
                borderColor: this.colors.uniqueCustomer.border,
                borderWidth,
                fill,
            },
            {
                label: "Raised Today",
                data: data.map((d) => d.raisedToday),
                backgroundColor: this.colors.raisedToday.background,
                borderColor: this.colors.raisedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Today's Started",
                data: data.map((d) => d.startedToday),
                backgroundColor: this.colors.startedToday.background,
                borderColor: this.colors.startedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Fixed Today",
                data: data.map((d) => d.fixedToday),
                backgroundColor: this.colors.fixedToday.background,
                borderColor: this.colors.fixedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Reopened Today",
                data: data.map((d) => d.reopenToday),
                backgroundColor: this.colors.reopenToday.background,
                borderColor: this.colors.reopenToday.border,
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
        return {filterData, dataLabels};
    };

    getWeeklyLabels(data) {
        return this.helper.getWeeks(data, "raisedToday").map((d) => d.week);
    }

    getWeeklyData(data) {
        const borderWidth = 2;
        const fill = false;
        return [
            {
                label: "Unique Customers",
                data: this.getWeeklyAveragedData(data, "uniqueCustomer"),
                backgroundColor: this.colors.uniqueCustomer.background,
                borderColor: this.colors.uniqueCustomer.border,
                borderWidth,
                fill,
            },
            {
                label: "Raised Today",
                data: this.getWeeklyAveragedData(data, "raisedToday"),
                backgroundColor: this.colors.raisedToday.background,
                borderColor: this.colors.raisedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Today's Started",
                data: this.getWeeklyAveragedData(data, "startedToday"),
                backgroundColor: this.colors.startedToday.background,
                borderColor: this.colors.startedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Fixed Today",
                data: this.getWeeklyAveragedData(data, "fixedToday"),
                backgroundColor: this.colors.fixedToday.background,
                borderColor: this.colors.fixedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Reopened Today",
                data: this.getWeeklyAveragedData(data, "reopenToday"),
                backgroundColor: this.colors.reopenToday.background,
                borderColor: this.colors.reopenToday.border,
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
                label: "Unique Customers",
                data: this.getMonthlyAveragedData(data, "uniqueCustomer"),
                backgroundColor: this.colors.uniqueCustomer.background,
                borderColor: this.colors.uniqueCustomer.border,
                borderWidth,
                fill,
            },
            {
                label: "Raised Today",
                data: this.getMonthlyAveragedData(data, "raisedToday"),
                backgroundColor: this.colors.raisedToday.background,
                borderColor: this.colors.raisedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Today's Started",
                data: this.getMonthlyAveragedData(data, "startedToday"),
                backgroundColor: this.colors.startedToday.background,
                borderColor: this.colors.startedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Fixed Today",
                data: this.getMonthlyAveragedData(data, "fixedToday"),
                backgroundColor: this.colors.fixedToday.background,
                borderColor: this.colors.fixedToday.border,
                borderWidth,
                fill,
            },
            {
                label: "Reopened Today",
                data: this.getMonthlyAveragedData(data, "reopenToday"),
                backgroundColor: this.colors.reopenToday.background,
                borderColor: this.colors.reopenToday.border,
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
 