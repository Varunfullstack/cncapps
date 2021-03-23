"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import {Line} from 'react-chartjs-2';
import {ReportType} from '../KPIReportComponent';
import {KPIReportHelper} from '../helper/KPIReportHelper';
import moment from "moment";

export default class DailySourceComponent extends MainComponent {
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
            OnSite: {
                background: "rgb(238, 126, 48)",
                border: "rgb(238, 126, 48)",
            },
            Manual: {
                background: "rgb(69, 115, 195)",
                border: "rgb(69, 115, 195)",
            },
            Email: {background: "rgb(255, 192, 0)", border: "rgb(255, 192, 0)"},
            Alert: {
                background: "rgb(172, 172, 172)",
                border: "rgb(172, 172, 172)",
            },
            Phone: {background: "#404041", border: "#404041"},
            Portal: {background: "#00b9f1", border: "#00b9f1"},
            Sales: {background: "#008bbf", border: "#008bbf"},

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
                label: "On Site",
                data: data.map((d) => d.OnSite),
                backgroundColor: this.colors.OnSite.background,
                borderColor: this.colors.OnSite.border,
                borderWidth,
                fill,
            },
            {
                label: "Manual",
                data: data.map((d) => d.Manual),
                backgroundColor: this.colors.Manual.background,
                borderColor: this.colors.Manual.border,
                borderWidth,
                fill,
            },
            {
                label: "Email",
                data: data.map((d) => d.Email),
                backgroundColor: this.colors.Email.background,
                borderColor: this.colors.Email.border,
                borderWidth,
                fill,
            },
            {
                label: "Alert",
                data: data.map((d) => d.Alert),
                backgroundColor: this.colors.Alert.background,
                borderColor: this.colors.Alert.border,
                borderWidth,
                fill,
            },
            {
                label: "Phone",
                data: data.map((d) => d.Phone),
                backgroundColor: this.colors.Phone.background,
                borderColor: this.colors.Phone.border,
                borderWidth,
                fill,
            },
            {
                label: "Portal",
                data: data.map((d) => d.Portal),
                backgroundColor: this.colors.Portal.background,
                borderColor: this.colors.Portal.border,
                borderWidth,
                fill,
            },
            {
                label: "Sales",
                data: data.map((d) => d.Sales),
                backgroundColor: this.colors.Sales.background,
                borderColor: this.colors.Sales.border,
                borderWidth,
                fill,
            },
        ];
    }

    getWeeklyLabels(data) {
        return this.helper.getWeeks(data, "Email").map((d) => d.week);
    }

    getWeeklyData(data) {
        const borderWidth = 2;
        const fill = false;
        return [
            {
                label: "On Site",
                data: this.getWeeklyAveragedData(data, "OnSite"),
                backgroundColor: this.colors.OnSite.background,
                borderColor: this.colors.OnSite.border,
                borderWidth,
                fill,
            },
            {
                label: "Manual",
                data: this.getWeeklyAveragedData(data, "Manual"),
                backgroundColor: this.colors.Manual.background,
                borderColor: this.colors.Manual.border,
                borderWidth,
                fill,
            },
            {
                label: "Email",
                data: this.getWeeklyAveragedData(data, "Email"),
                backgroundColor: this.colors.Email.background,
                borderColor: this.colors.Email.border,
                borderWidth,
                fill,
            },
            {
                label: "Alert",
                data: this.getWeeklyAveragedData(data, "Alert"),
                backgroundColor: this.colors.Alert.background,
                borderColor: this.colors.Alert.border,
                borderWidth,
                fill,
            },
            {
                label: "Phone",
                data: this.getWeeklyAveragedData(data, "Phone"),
                backgroundColor: this.colors.Phone.background,
                borderColor: this.colors.Phone.border,
                borderWidth,
                fill,
            },
            {
                label: "Portal",
                data: this.getWeeklyAveragedData(data, "Portal"),
                backgroundColor: this.colors.Portal.background,
                borderColor: this.colors.Portal.border,
                borderWidth,
                fill,
            },
            {
                label: "Sales",
                data: this.getWeeklyAveragedData(data, "Sales"),
                backgroundColor: this.colors.Sales.background,
                borderColor: this.colors.Sales.border,
                borderWidth,
                fill,
            },
        ];
    }

    getMonthlyLabels(data) {
        return this.helper.getMonths(data, "Email").map(
            (d) => d.month
        );
    }

    getMonthlyData(data) {
        const borderWidth = 2;
        const fill = false;
        return [
            {
                label: "On Site",
                data: this.getMonthlyAveragedData(data, "OnSite"),
                backgroundColor: this.colors.OnSite.background,
                borderColor: this.colors.OnSite.border,
                borderWidth,
                fill,
            },
            {
                label: "Manual",
                data: this.getMonthlyAveragedData(data, "Manual"),
                backgroundColor: this.colors.Manual.background,
                borderColor: this.colors.Manual.border,
                borderWidth,
                fill,
            },
            {
                label: "Email",
                data: this.getMonthlyAveragedData(data, "Email"),
                backgroundColor: this.colors.Email.background,
                borderColor: this.colors.Email.border,
                borderWidth,
                fill,
            },
            {
                label: "Alert",
                data: this.getMonthlyAveragedData(data, "Alert"),
                backgroundColor: this.colors.Alert.background,
                borderColor: this.colors.Alert.border,
                borderWidth,
                fill,
            },
            {
                label: "Phone",
                data: this.getMonthlyAveragedData(data, "Phone"),
                backgroundColor: this.colors.Phone.background,
                borderColor: this.colors.Phone.border,
                borderWidth,
                fill,
            },
            {
                label: "Portal",
                data: this.getMonthlyAveragedData(data, "Portal"),
                backgroundColor: this.colors.Portal.background,
                borderColor: this.colors.Portal.border,
                borderWidth,
                fill,
            },
            {
                label: "Sales",
                data: this.getMonthlyAveragedData(data, "Sales"),
                backgroundColor: this.colors.Sales.background,
                borderColor: this.colors.Sales.border,
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

    render() {
        return (
            <div>
                {this.getChart()}
            </div>
        );
    }
}
 