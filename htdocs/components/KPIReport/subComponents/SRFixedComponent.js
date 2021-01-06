"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line } from 'react-chartjs-2';
import { groupBy } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
export default class SRFixedComponent extends MainComponent {
  api;  
  colors;
  constructor(props) {
    super(props);    
    this.state = {
      ...this.state,      
      data: this.props.data,    
      filter:this.props.filter        
    };    
    this.colors=this.props.colors;
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
    return this.getWeeks(data, "escalationsFixedActivities").map((d) => d.week);
  }
  getWeeklyData(data) {
    //get data by teams
    const borderWidth = 2;
    const fill = false;
    let teams = [
      {
        label: "Escalations",
        data: this.getWeeks(data, "escalationsFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Escalation.background,
        borderColor: this.colors.Escalation.border,
        borderWidth,
        fill,
      },
      {
        label: "Helpdesk",
        data: this.getWeeks(data, "helpDeskFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Helpdesk.background,
        borderColor: this.colors.Helpdesk.border,
        borderWidth,
        fill,
      },
      {
        label: "Projects",
        data: this.getWeeks(data, "projectsActivities").map((d) => d.value),
        backgroundColor: this.colors.Project.background,
        borderColor: this.colors.Project.border,
        borderWidth,
        fill,
      },
      {
        label: "Small Projects",
        data: this.getWeeks(data, "smallProjectsActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.SmallProject.background,
        borderColor: this.colors.SmallProject.border,
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
    return this.getMonths(data, "escalationsFixedActivities").map(
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
        data: this.getMonths(data, "escalationsFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Escalation.background,
        borderColor: this.colors.Escalation.border,
        borderWidth,
        fill,
      },
      {
        label: "Helpdesk",
        data: this.getMonths(data, "helpDeskFixedActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.Helpdesk.background,
        borderColor: this.colors.Helpdesk.border,
        borderWidth,
        fill,
      },
      {
        label: "Projects",
        data: this.getMonths(data, "projectsActivities").map((d) => d.value),
        backgroundColor: this.colors.Project.background,
        borderColor: this.colors.Project.border,
        borderWidth,
        fill,
      },
      {
        label: "Small Projects",
        data: this.getMonths(data, "smallProjectsActivities").map(
          (d) => d.value
        ),
        backgroundColor: this.colors.SmallProject.background,
        borderColor: this.colors.SmallProject.border,
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
 