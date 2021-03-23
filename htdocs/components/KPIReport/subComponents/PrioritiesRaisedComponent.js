"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line } from 'react-chartjs-2';
import { groupBy } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
import { KPIReportHelper } from '../helper/KPIReportHelper';
export default class PrioritiesRaisedComponent extends MainComponent {
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
    this.colors={
      p1:{
        background: "rgb(238, 126, 48)",
        border: "rgb(238, 126, 48)",
      },
      p2:{
        background: "rgb(69, 115, 195)",
        border: "rgb(69, 115, 195)",
      },
      p3:{
        background: "rgb(255, 192, 0)", 
        border: "rgb(255, 192, 0)"
      },
      p4:{
        background: "rgb(172, 172, 172)",
        border: "rgb(172, 172, 172)",
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
        label: "P1",
        data: data.map((d) => d.P1),
        backgroundColor: this.colors.p1.background,
        borderColor: this.colors.p1.border,
        borderWidth,
        fill,
      },
      {
        label: "P2",
        data: data.map((d) => d.P2),
        backgroundColor: this.colors.p2.background,
        borderColor: this.colors.p2.border,
        borderWidth,
        fill,
      },
      {
        label: "P3",
        data: data.map((d) => d.P3),
        backgroundColor: this.colors.p3.background,
        borderColor: this.colors.p3.border,
        borderWidth,
        fill,
      },
      {
        label: "P4",
        data: data.map((d) => d.P4),
        backgroundColor: this.colors.p4.background,
        borderColor: this.colors.p4.border,
        borderWidth,
        fill,
      },
    ];
  }

  getWeeklyLabels(data) {
    return this.helper.getWeeks(data, "P1").map((d) => d.week);
  }
  getWeeklyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "P1",
        data: this.helper.getWeeks(data, "P1").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p1.background,
        borderColor: this.colors.p1.border,
        borderWidth,
        fill,
      },
      {
        label: "P2",
        data: this.helper.getWeeks(data, "P2").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p2.background,
        borderColor: this.colors.p2.border,
        borderWidth,
        fill,
      },
      {
        label: "P3",
        data: this.helper.getWeeks(data, "P3").map((d) => d.value),
        backgroundColor: this.colors.p3.background,
        borderColor: this.colors.p3.border,
        borderWidth,
        fill,
      },
      {
        label: "P4",
        data: this.helper.getWeeks(data, "P4").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p4.background,
        borderColor: this.colors.p4.border,
        borderWidth,
        fill,
      },
    ];
    return teams;
  }
 
  getMonthlyLabels(data) {
    return this.helper.getMonths(data, "P1").map(
      (d) => d.month
    );
  }
  getMonthlyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "P1",
        data: this.helper.getMonths(data, "P1").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p1.background,
        borderColor: this.colors.p1.border,
        borderWidth,
        fill,
      },
      {
        label: "P2",
        data: this.helper.getMonths(data, "P2").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p2.background,
        borderColor: this.colors.p2.border,
        borderWidth,
        fill,
      },
      {
        label: "P3",
        data: this.helper.getMonths(data, "P3").map((d) => d.value),
        backgroundColor: this.colors.p3.background,
        borderColor: this.colors.p3.border,
        borderWidth,
        fill,
      },
      {
        label: "P4",
        data: this.helper.getMonths(data, "P4").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p4.background,
        borderColor: this.colors.p4.border,
        borderWidth,
        fill,
      },
    ];
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
 