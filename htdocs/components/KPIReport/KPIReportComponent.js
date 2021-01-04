"use strict";
import Spinner from './../shared/Spinner/Spinner';
import MainComponent from '../shared/MainComponent'
import React from 'react';
import ReactDOM from 'react-dom';
import './KPIReportComponent.css'
import './../style.css';
import APIKPIReport from './services/APIKPIReport';
import {Bar,Line } from 'react-chartjs-2';
import { groupBy } from '../utils/utils';
export default class KPIReportComponent extends MainComponent {
    api;
    ResultType;
    colors;
    constructor(props) {
        super(props);
        this.ResultType={Daily:'day',Weekly:"week",Monthly:'month'}
        this.state = {
            ...this.state,
            filter:{
                from:'2020-12-01',
                to:'',
                resultType:this.ResultType.Daily
            },
            data:[]
        }
        this.api=new APIKPIReport();
        this.colors={
            Escalation:{background:'rgb(238, 126, 48)',border:'rgb(238, 126, 48)'},
            Helpdesk:{background:'rgb(69, 115, 195)',border:'rgb(69, 115, 195)'},
            Project:{background:'rgb(255, 192, 0)',border:'rgb(255, 192, 0)'},
            SmallProject:{background:'rgb(172, 172, 172)',border:'rgb(172, 172, 172)'},
        }
        moment.locale('en');
        moment.updateLocale('en', {
            week: {
              dow: 5,
            },
          })
    }

    componentDidMount() { 
       
    } 
    setFilter=(field,value)=>{
        const {filter}=this.state;
        filter[field]=value;
        this.setState({filter});
        console.log(filter);
    }
    getFilterElement= ()=>{
        const {filter}=this.state;
        return <div>
            <label>Start date</label>
            <input type="date" value={filter.from} onChange={($event)=>this.setFilter("from",$event.target.value)}></input>
            <label>End date</label>
            <input type="date"  value={filter.to}  onChange={($event)=>this.setFilter("to",$event.target.value)}></input>
            <select  value={filter.resultType}  onChange={($event)=>this.setFilter("resultType",$event.target.value)}>
                <option value="day">Daily</option>
                <option value="week">Weekly</option>
                <option value="month">Monthly</option>                
            </select>
            <button onClick={this.handleReportView}>GO</button>
        </div>
    }
    handleReportView=()=>{
        const {filter}=this.state;
        if(filter.from=='')
        {
            this.alert("You must enter the start date");
            return;
        }
        this.api.getSRFixed(filter.from,filter.to).then(data=>{
            console.log(data);
            this.setState({data})
        })
    }
    processData=(data)=>{
        const teams=[
            {
                label:'Escalations',
                data:[],
                fill:false,
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgba(255, 99, 132, 0.2)',

            }];
    }
    getDailyData(data){
        const borderWidth=2;
        const fill=false;
        return [
            {
              label: 'Escalations',
              data: data.map(d=>d.escalationsFixedActivities),    
              backgroundColor: this.colors.Escalation.background,
              borderColor: this.colors.Escalation.border,            
              borderWidth,
              fill,
            },
            {
              label: 'Helpdesk',
              data: data.map(d=>d.helpDeskFixedActivities),    
              backgroundColor: this.colors.Helpdesk.background,
              borderColor:  this.colors.Helpdesk.border,            
              borderWidth,
              fill,
            },
            {
              label: 'Projects',
              data: data.map(d=>d.projectsActivities),    
              backgroundColor: this.colors.Project.background,
              borderColor: this.colors.Project.border,            
              borderWidth,
              fill,
            },
            {
              label: 'Small Projects',
              data: data.map(d=>d.smallProjectsActivities),
              backgroundColor: this.colors.SmallProject.background,
              borderColor: this.colors.SmallProject.border,
              borderWidth,
              fill,
            },
          ]
    }
    getWeeks(data,property){
        
       let gdata= data.map(d=>
            {
                const dt=moment(d.date);                
                return {value:d[property],date:d.date,week:dt.week()+dt.year()}
            });
        gdata=groupBy(gdata,'week').map((g,i)=>{
            g.week='Week '+(i+1);
            g.value=g.items.reduce((prev,current)=>prev+parseInt(current.value),0) 
            return g;
        });

        return gdata;
    }
    getWeeklyLabels(data){
        return this.getWeeks(data,'escalationsFixedActivities').map(d=>d.week);
    }
    getWeeklyData(data){
        //get data by teams
        const borderWidth=2;
        const fill=false;
        let teams=  [
                {
                  label: 'Escalations',
                  data: this.getWeeks(data,'escalationsFixedActivities').map(d=>d.value),    
                  backgroundColor: this.colors.Escalation.background,
                  borderColor: this.colors.Escalation.border,            
                  borderWidth,
                  fill,
                },
                {
                  label: 'Helpdesk',
                  data:this.getWeeks(data,'helpDeskFixedActivities').map(d=>d.value),    
                  backgroundColor: this.colors.Helpdesk.background,
                  borderColor:  this.colors.Helpdesk.border,            
                  borderWidth,
                  fill,
                },
                {
                  label: 'Projects',
                  data:this.getWeeks(data,'projectsActivities').map(d=>d.value)  ,    
                  backgroundColor: this.colors.Project.background,
                  borderColor: this.colors.Project.border,            
                  borderWidth,
                  fill,
                },
                {
                  label: 'Small Projects',
                  data: this.getWeeks(data,'smallProjectsActivities').map(d=>d.value)  ,
                  backgroundColor: this.colors.SmallProject.background,
                  borderColor: this.colors.SmallProject.border,
                  borderWidth,
                  fill,
                },
              ];
        console.log(teams);
        return teams;        
    }
    getMonths(data,property){        
        let gdata= data.map(d=>
             {
                 const dt=moment(d.date);                
                 return {value:d[property],date:d.date,month:dt.format("MMM")+" "+dt.year()}
             });
        console.log('months',gdata);
         gdata=groupBy(gdata,'month').map((g,i)=>{
             g.month=g.groupName;
             g.value=g.items.reduce((prev,current)=>prev+parseInt(current.value),0) 
             return g;
         });
 
         return gdata;
     }
    getMonthlyLabels(data){
        return this.getMonths(data,'escalationsFixedActivities').map(d=>d.month);
    }
    getMonthlyData(data){
        //get data by teams
        const borderWidth=2;
        const fill=false;
        let teams=  [
                {
                  label: 'Escalations',
                  data: this.getMonths(data,'escalationsFixedActivities').map(d=>d.value),    
                  backgroundColor: this.colors.Escalation.background,
                  borderColor: this.colors.Escalation.border,            
                  borderWidth,
                  fill,
                },
                {
                  label: 'Helpdesk',
                  data:this.getMonths(data,'helpDeskFixedActivities').map(d=>d.value),    
                  backgroundColor: this.colors.Helpdesk.background,
                  borderColor:  this.colors.Helpdesk.border,            
                  borderWidth,
                  fill,
                },
                {
                  label: 'Projects',
                  data:this.getMonths(data,'projectsActivities').map(d=>d.value)  ,    
                  backgroundColor: this.colors.Project.background,
                  borderColor: this.colors.Project.border,            
                  borderWidth,
                  fill,
                },
                {
                  label: 'Small Projects',
                  data: this.getMonths(data,'smallProjectsActivities').map(d=>d.value)  ,
                  backgroundColor: this.colors.SmallProject.background,
                  borderColor: this.colors.SmallProject.border,
                  borderWidth,
                  fill,
                },
              ];
        console.log(teams);
        return teams;        
    }
    getChart=()=>{
        const {data,filter}=this.state;
        let filterData=[];
        let labels=[];
        if(filter.resultType==this.ResultType.Daily)
        {
            filterData=this.getDailyData(data);
            labels= data.map(a=>a.date);
        }
        if(filter.resultType==this.ResultType.Weekly)
        {
            filterData=this.getWeeklyData(data);
            labels=this.getWeeklyLabels(data);
        }
        if(filter.resultType==this.ResultType.Monthly)
        {
            filterData=this.getMonthlyData(data);
            labels=this.getMonthlyLabels(data);
        }
        this.getMonthlyData(data);
        console.log( filterData,labels);
        const chartData = {
            labels: labels,
            datasets:filterData 
          }
          
          const options = {
            scales: {
              yAxes: [
                {
                  ticks: {
                    beginAtZero: true,
                  },
                },
              ],
            },
          }
          return  <Line  data={chartData} options={options} />
    }
     
    render() {        
        const { _showSpinner} = this.state;
        return  <div>
                    <Spinner show={_showSpinner}></Spinner>
                    {this.getAlert()}
                    <h3>Filter Data</h3>
                    {this.getFilterElement()}
                    {this.getChart()}
                </div>;        
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainKPIReport");
    ReactDOM.render(React.createElement(KPIReportComponent), domContainer);
});
