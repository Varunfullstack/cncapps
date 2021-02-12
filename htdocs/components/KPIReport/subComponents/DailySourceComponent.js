"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line } from 'react-chartjs-2';
import { groupBy } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
import { KPIReportHelper } from '../helper/KPIReportHelper';
export default class DailySourceComponent extends MainComponent {
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
      OnSite: {
        background: "rgb(238, 126, 48)",
        border: "rgb(238, 126, 48)",
      },
      Manual: {
        background: "rgb(69, 115, 195)",
        border: "rgb(69, 115, 195)",
      },
      Email: { background: "rgb(255, 192, 0)", border: "rgb(255, 192, 0)" },
      Alert: {
        background: "rgb(172, 172, 172)",
        border: "rgb(172, 172, 172)",
      },
      Phone: { background: "#404041", border: "#404041" },
      Portal: { background: "#00b9f1", border: "#00b9f1" },
      Sales: { background: "#008bbf", border: "#008bbf" },

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
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "On Site",
        data: this.helper.getWeeks(data, "OnSite").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.OnSite.background,
        borderColor: this.colors.OnSite.border,
        borderWidth,
        fill,
      },
      {
        label: "Manual",
        data: this.helper.getWeeks(data, "Manual").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Manual.background,
        borderColor: this.colors.Manual.border,
        borderWidth,
        fill,
      },
      {
        label: "Email",
        data: this.helper.getWeeks(data, "Email").map((d) => d.value),
        backgroundColor: this.colors.Email.background,
        borderColor: this.colors.Email.border,
        borderWidth,
        fill,
      },
      {
        label: "Alert",
        data: this.helper.getWeeks(data, "Alert").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Alert.background,
        borderColor: this.colors.Alert.border,
        borderWidth,
        fill,
      },
      {
        label: "Phone",
        data: this.helper.getWeeks(data, "Phone").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Phone.background,
        borderColor: this.colors.Phone.border,
        borderWidth,
        fill,
      },
      {
        label: "Portal",
        data: this.helper.getWeeks(data, "Portal").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Portal.background,
        borderColor: this.colors.Portal.border,
        borderWidth,
        fill,
      },
      {
        label: "Sales",
        data: this.helper.getWeeks(data, "Sales").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Sales.background,
        borderColor: this.colors.Sales.border,
        borderWidth,
        fill,
      },
    ];
    //console.log(teams);
    return teams;
  }
 
  getMonthlyLabels(data) {
    return this.helper.getMonths(data, "Email").map(
      (d) => d.month
    );
  }
  getMonthlyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "On Site",
        data: this.helper.getMonths(data, "OnSite").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.OnSite.background,
        borderColor: this.colors.OnSite.border,
        borderWidth,
        fill,
      },
      {
        label: "Manual",
        data: this.helper.getMonths(data, "Manual").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Manual.background,
        borderColor: this.colors.Manual.border,
        borderWidth,
        fill,
      },
      {
        label: "Email",
        data: this.helper.getMonths(data, "Email").map((d) => d.value),
        backgroundColor: this.colors.Email.background,
        borderColor: this.colors.Email.border,
        borderWidth,
        fill,
      },
      {
        label: "Alert",
        data: this.helper.getMonths(data, "Alert").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Alert.background,
        borderColor: this.colors.Alert.border,
        borderWidth,
        fill,
      },
      {
        label: "Phone",
        data: this.helper.getMonths(data, "Phone").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Phone.background,
        borderColor: this.colors.Phone.border,
        borderWidth,
        fill,
      },
      {
        label: "Portal",
        data: this.helper.getMonths(data, "Portal").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Portal.background,
        borderColor: this.colors.Portal.border,
        borderWidth,
        fill,
      },
      {
        label: "Sales",
        data: this.helper.getMonths(data, "Sales").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Sales.background,
        borderColor: this.colors.Sales.border,
        borderWidth,
        fill,
      },
    ];
    //console.log(teams);
    return teams;
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
    return { filterData,  dataLabels };
  };
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
 