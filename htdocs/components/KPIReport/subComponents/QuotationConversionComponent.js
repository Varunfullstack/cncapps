"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line,Bar } from 'react-chartjs-2';
import { groupBy } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
import { KPIReportHelper } from '../helper/KPIReportHelper';
export default class QuotationConversionComponent extends MainComponent {
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
      quote:{
        background: "rgb(238, 126, 48)",
        border: "rgb(238, 126, 48)",
      },
      conversion:{
        background: "rgb(69, 115, 195)",
        border: "rgb(69, 115, 195)",
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
        label: "Quote",
        data: data.map((d) => d.quote),
        backgroundColor: this.colors.quote.background,
        borderColor: this.colors.quote.border,
        borderWidth,
        fill,
      },
      {
        label: "Conversion",
        data: data.map((d) => d.conversion),
        backgroundColor: this.colors.conversion.background,
        borderColor: this.colors.conversion.border,
        borderWidth,
        fill,
      }
    ];
  }
   
  getWeeklyLabels(data) {
    return this.helper.getWeeks(data, "quote").map((d) => d.week);
  }
  getWeeklyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "Quote",
        data: this.helper.getWeeks(data, "quote").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.quote.background,
        borderColor: this.colors.quote.border,
        borderWidth,
        fill,
      },
      {
        label: "Conversion",
        data: this.helper.getWeeks(data, "conversion").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.conversion.background,
        borderColor: this.colors.conversion.border,
        borderWidth,
        fill,
      }
    ];
    return teams;
  }
  
  getMonthlyLabels(data) {
    return this.helper.getMonths(data, "quote").map(
      (d) => d.month
    );
  }
  getMonthlyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "Quote",
        data: this.helper.getMonths(data, "quote").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.quote.background,
        borderColor: this.colors.quote.border,
        borderWidth,
        fill,
      },
      {
        label: "Conversion",
        data: this.helper.getMonths(data, "conversion").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.conversion.background,
        borderColor: this.colors.conversion.border,
        borderWidth,
        fill,
      }
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
              beginAtZero: true,
            },
          },
        ],
      },
    };
    return <div style={{height:"80vh",width:"85vw"}} >
     <Bar data={chartData} options={options}  />
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
 