"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line } from 'react-chartjs-2';
import { groupBy } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
import { KPIReportHelper } from '../helper/KPIReportHelper';
export default class SRFixedComponent extends MainComponent {
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
      Escalation: {
        background: "rgb(238, 126, 48)",
        border: "rgb(238, 126, 48)",
      },
      Helpdesk: {
        background: "rgb(69, 115, 195)",
        border: "rgb(69, 115, 195)",
      },
      Project: { background: "rgb(255, 192, 0)", border: "rgb(255, 192, 0)" },
      SmallProject: {
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
        label: "Escalations",
        data: data.map((d) => d.escalationsFixedActivities),
        backgroundColor: this.colors.Escalation.background,
        borderColor: this.colors.Escalation.border,
        borderWidth,
        fill,
      },
      {
        label: "Helpdesk",
        data: data.map((d) => d.helpDeskFixedActivities),
        backgroundColor: this.colors.Helpdesk.background,
        borderColor: this.colors.Helpdesk.border,
        borderWidth,
        fill,
      },
      {
        label: "Projects",
        data: data.map((d) => d.projectsActivities),
        backgroundColor: this.colors.Project.background,
        borderColor: this.colors.Project.border,
        borderWidth,
        fill,
      },
      {
        label: "Small Projects",
        data: data.map((d) => d.smallProjectsActivities),
        backgroundColor: this.colors.SmallProject.background,
        borderColor: this.colors.SmallProject.border,
        borderWidth,
        fill,
      },
    ];
  }
 
  getWeeklyLabels(data) {
    return this.helper.getWeeks(data, "escalationsFixedActivities").map((d) => d.week);
  }
  getWeeklyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "Escalations",
        data: this.helper.getWeeks(data, "escalationsFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Escalation.background,
        borderColor: this.colors.Escalation.border,
        borderWidth,
        fill,
      },
      {
        label: "Helpdesk",
        data: this.helper.getWeeks(data, "helpDeskFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Helpdesk.background,
        borderColor: this.colors.Helpdesk.border,
        borderWidth,
        fill,
      },
      {
        label: "Projects",
        data: this.helper.getWeeks(data, "projectsActivities").map((d) => d.value),
        backgroundColor: this.colors.Project.background,
        borderColor: this.colors.Project.border,
        borderWidth,
        fill,
      },
      {
        label: "Small Projects",
        data: this.helper.getWeeks(data, "smallProjectsActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.SmallProject.background,
        borderColor: this.colors.SmallProject.border,
        borderWidth,
        fill,
      },
    ];
    return teams;
  }
 
  getMonthlyLabels(data) {
    return this.helper.getMonths(data, "escalationsFixedActivities").map(
      (d) => d.month
    );
  }
  getMonthlyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "Escalations",
        data: this.helper.getMonths(data, "escalationsFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Escalation.background,
        borderColor: this.colors.Escalation.border,
        borderWidth,
        fill,
      },
      {
        label: "Helpdesk",
        data: this.helper.getMonths(data, "helpDeskFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Helpdesk.background,
        borderColor: this.colors.Helpdesk.border,
        borderWidth,
        fill,
      },
      {
        label: "Projects",
        data: this.helper.getMonths(data, "projectsActivities").map((d) => d.value),
        backgroundColor: this.colors.Project.background,
        borderColor: this.colors.Project.border,
        borderWidth,
        fill,
      },
      {
        label: "Small Projects",
        data: this.helper.getMonths(data, "smallProjectsActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.SmallProject.background,
        borderColor: this.colors.SmallProject.border,
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
 