"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line } from 'react-chartjs-2';
import { groupBy } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
import { KPIReportHelper } from '../helper/KPIReportHelper';
export default class ServiceRequestComponent extends MainComponent {
  api;  
  colors;
  helper=new KPIReportHelper();
  constructor(props) {
    super(props);    
    this.state = {
      ...this.state,      
      data: this.props.data,    
      filter:this.props.filter        
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
      startedToday: { background: "rgb(255, 192, 0)", border: "rgb(255, 192, 0)" },
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
   
  static getDerivedStateFromProps(props,state)
  {
    return {...state,...props};
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
 
 
  getChartData = (data,filter) => {     
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
    console.log({ filterData,  dataLabels });
    return { filterData,  dataLabels };
  };
  getWeeklyLabels(data) {
    return this.helper.getWeeks(data, "raisedToday").map((d) => d.week);
  }
  getWeeklyData(data) {
    console.log(data);
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "Unique Customers",
        data: this.helper.getWeeks(data, "uniqueCustomer").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.uniqueCustomer.background,
        borderColor: this.colors.uniqueCustomer.border,
        borderWidth,
        fill,
      },
      {
        label: "Raised Today",
        data: this.helper.getWeeks(data, "raisedToday").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.raisedToday.background,
        borderColor: this.colors.raisedToday.border,
        borderWidth,
        fill,
      },
      {
        label: "Today's Started",
        data: this.helper.getWeeks(data, "startedToday").map((d) => d.value),
        backgroundColor: this.colors.startedToday.background,
        borderColor: this.colors.startedToday.border,
        borderWidth,
        fill,
      },
      {
        label: "Fixed Today",
        data: this.helper.getWeeks(data, "fixedToday").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.fixedToday.background,
        borderColor: this.colors.fixedToday.border,
        borderWidth,
        fill,
      },
      {
        label: "Reopened Today",
        data: this.helper.getWeeks(data, "reopenToday").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.reopenToday.background,
        borderColor: this.colors.reopenToday.border,
        borderWidth,
        fill,
      },
    ];
    //console.log(teams);
    return teams;
  }
  getMonthlyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "Unique Customers",
        data: this.helper.getMonths(data, "uniqueCustomer").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.uniqueCustomer.background,
        borderColor: this.colors.uniqueCustomer.border,
        borderWidth,
        fill,
      },
      {
        label: "Raised Today",
        data: this.helper.getMonths(data, "raisedToday").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.raisedToday.background,
        borderColor: this.colors.raisedToday.border,
        borderWidth,
        fill,
      },
      {
        label: "Today's Started",
        data: this.helper.getMonths(data, "startedToday").map((d) => d.value),
        backgroundColor: this.colors.startedToday.background,
        borderColor: this.colors.startedToday.border,
        borderWidth,
        fill,
      },
      {
        label: "Fixed Today",
        data: this.helper.getMonths(data, "fixedToday").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.fixedToday.background,
        borderColor: this.colors.fixedToday.border,
        borderWidth,
        fill,
      },
      {
        label: "Reopened Today",
        data: this.helper.getMonths(data, "reopenToday").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.reopenToday.background,
        borderColor: this.colors.reopenToday.border,
        borderWidth,
        fill,
      },
    ];
    //console.log(teams);
    return teams;
  }
  getMonthlyLabels(data) {
    return this.helper.getMonths(data, "raisedToday").map(
      (d) => d.month
    );
  }
  getChart = () => {
    const {data,filter}=this.state;
    const { filterData, dataLabels } = this.getChartData(data,filter);
    //console.log('chart data',filterData, dataLabels);
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
    return <div style={{height:"80vh",width:"85vw"}} >
     <Line data={chartData} options={options}  />
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
 