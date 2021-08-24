"use strict";
import React from 'react';
import { Bar, Line } from 'react-chartjs-2';
import {Colors, groupBy, sumBy} from '../../utils/utils';
import {KPIReportHelper} from '../helper/KPIReportHelper';
import moment from "moment";
import { ReportType } from './../KPIReportComponent';
import { ColorsBig,sort } from './../../utils/utils';

export default class GrossProfitComponent extends React.Component {
    api;
   
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

     


    getChartData = (data, filter) => {
        let filterData = [];
        let dataLabels = [];
         
        if (filter.resultType == ReportType.Monthly) {
            filterData = this.getMonthlyData(data);
            dataLabels = this.getMonthlyLabels(data);
        }
        console.log('filterData',filterData)
        return {filterData, dataLabels};
    };
  
    getMonthlyData(data) {
        const borderWidth = 2;
        const fill = false;
        data.map(i=>i.profit=i.totalSale-i.totalCost);
        return [
            {
                label: "Internet (B)",
                data: this.getSumDataByKey(data, "profit",'B'),
                backgroundColor: "#4472c4",
                borderColor: "#4472c4",
                borderWidth,
                fill,
            },
            {
                label: "Telecom (F)",
                data: this.getSumDataByKey(data, "profit",'F'),
                backgroundColor: "#ffc000",
                borderColor: "#ffc000",
                borderWidth,
                fill,
            },
            {
                label: "Consultancy (G)",
                data: this.getSumDataByKey(data, "profit",'G'),
                backgroundColor: "#5b9bd5",
                borderColor: "#5b9bd5",
                borderWidth,
                fill,
            },
            {
                label: "Hardware (H)",
                data: this.getSumDataByKey(data, "profit",'H'),
                backgroundColor: "#70ad47",
                borderColor: "#70ad47",
                borderWidth,
                fill,
            },
            {
                label: "Managed (J)",
                data: this.getSumDataByKey(data, "profit",'J'),
                backgroundColor: "#264478",
                borderColor: "#264478",
                borderWidth,
                fill,
            },
            {
                label: "Maintenance (M)",
                data: this.getSumDataByKey(data, "profit",'M'),
                backgroundColor: "#9e480e",
                borderColor: "#9e480e",
                borderWidth,
                fill,
            },
            {
                label: "Prey-Pay (J)",
                data: this.getSumDataByKey(data, "profit",'J'),
                backgroundColor: "#636363",
                borderColor: "#636363",
                borderWidth,
                fill,
            },
            {
                label: "Software (S)",
                data: this.getSumDataByKey(data, "profit",'S'),
                backgroundColor: "#997300",
                borderColor: "#997300",
                borderWidth,
                fill,
            },
        ];
    }

    getMonthlyLabels(data) {
        return this.helper.getMonths(data, "date").map(
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
            <Bar data={chartData}
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

    getSumDataByKey(data, key,cat) {
        const groups=groupBy(data.filter(i=>i.stockCat==cat),'date');
        console.log('group',sort(groups,'groupName'));

        const final= groups.map(g=>{
            const total= sumBy(g.items,key).toFixed(2);            
            console.log("total",total)
            return total;
        });   
        console.log('final',final); 
        return final;
    }
   
}
 