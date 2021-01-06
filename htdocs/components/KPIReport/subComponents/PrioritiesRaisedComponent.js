"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line } from 'react-chartjs-2';
import { groupBy } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
export default class PrioritiesRaisedComponent extends MainComponent {
  api;  
  colors;
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
  getWeeks(data, property) {
    let gdata = data.map((d) => {
      const dt = moment(d.date);
      return { value: d[property], date: d.date, week: dt.week() + dt.year() };
    }); 
    gdata =groupBy(gdata, "week").map((g, i) => {
      g.week = g.items[0].date;
      g.value = g.items.reduce(
        (prev, current) => prev + parseInt(current.value),
        0
      );
      return g;
    });

    return gdata;
  }
  getWeeklyLabels(data) {
    return this.getWeeks(data, "P1").map((d) => d.week);
  }
  getWeeklyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "P1",
        data: this.getWeeks(data, "P1").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p1.background,
        borderColor: this.colors.p1.border,
        borderWidth,
        fill,
      },
      {
        label: "P2",
        data: this.getWeeks(data, "P2").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p2.background,
        borderColor: this.colors.p2.border,
        borderWidth,
        fill,
      },
      {
        label: "P3",
        data: this.getWeeks(data, "P3").map((d) => d.value),
        backgroundColor: this.colors.p3.background,
        borderColor: this.colors.p3.border,
        borderWidth,
        fill,
      },
      {
        label: "P4",
        data: this.getWeeks(data, "P4").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p4.background,
        borderColor: this.colors.p4.border,
        borderWidth,
        fill,
      },
    ];
    //console.log(teams);
    return teams;
  }
  getMonths(data, property) {
    let gdata = data.map((d) => {
      const dt = moment(d.date);
      return {
        value: d[property],
        date: d.date,
        month: dt.format("MMM") + " " + dt.year(),        
      };
    });
     
    gdata = groupBy(gdata, "month").map((g, i) => {
      g.month = g.groupName;
      g.value = g.items.reduce(
        (prev, current) => prev + parseInt(current.value),
        0
      );
      return g;
    });
   
    return gdata;
  }
  getMonthlyLabels(data) {
    return this.getMonths(data, "P1").map(
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
        data: this.getMonths(data, "P1").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p1.background,
        borderColor: this.colors.p1.border,
        borderWidth,
        fill,
      },
      {
        label: "P2",
        data: this.getMonths(data, "P2").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p2.background,
        borderColor: this.colors.p2.border,
        borderWidth,
        fill,
      },
      {
        label: "P3",
        data: this.getMonths(data, "P3").map((d) => d.value),
        backgroundColor: this.colors.p3.background,
        borderColor: this.colors.p3.border,
        borderWidth,
        fill,
      },
      {
        label: "P4",
        data: this.getMonths(data, "P4").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.p4.background,
        borderColor: this.colors.p4.border,
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
 