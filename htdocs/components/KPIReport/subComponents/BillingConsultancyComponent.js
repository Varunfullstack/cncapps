"use strict";
import MainComponent from '../../shared/MainComponent'
import React from 'react';
import APIKPIReport from './../services/APIKPIReport';
import {Line,Bar } from 'react-chartjs-2';
import { groupBy, sort } from '../../utils/utils';
import { ReportType } from '../KPIReportComponent';
import { KPIReportHelper } from '../helper/KPIReportHelper';
export default class BillingConsultancyComponent extends MainComponent {
  api;  
  colors;
  helper=new KPIReportHelper();
  constructor(props) {
    super(props);    
    this.state = {
      ...this.state,      
      data: this.props.data,    
      filter:this.props.filter,
     
    };    
    this.colors = [
      "rgb(242,234,94)", "rgb(62,31,132)", "rgb(153,112,127)", "rgb(81,34,165)", "rgb(133,76,93)", "rgb(114,8,41)", "rgb(235,179,149)", "rgb(168,97,103)", "rgb(47,147,51)", "rgb(11,130,44)", "rgb(190,208,244)", "rgb(187,59,48)", "rgb(209,137,215)", "rgb(169,87,182)", "rgb(48,11,22)", "rgb(252,229,185)", "rgb(153,140,250)", "rgb(252,218,103)", "rgb(80,90,87)", "rgb(166,231,210)", "rgb(221,157,11)", "rgb(152,39,248)", "rgb(59,126,25)", "rgb(165,219,24)", "rgb(26,90,131)", "rgb(198,64,21)", "rgb(204,240,107)", "rgb(120,8,87)", "rgb(127,102,18)", "rgb(33,34,123)", "rgb(251,30,23)", "rgb(68,122,155)", "rgb(164,175,166)", "rgb(37,3,160)", "rgb(85,16,223)", "rgb(232,251,163)", "rgb(133,12,217)", "rgb(107,113,86)", "rgb(213,246,36)", "rgb(225,145,134)", "rgb(91,209,241)", "rgb(56,100,154)", "rgb(183,94,70)", "rgb(162,70,55)", "rgb(240,82,232)"
    ];
  }
   
  static getDerivedStateFromProps(props,state)
  {
    return {...state,...props};
  }
  componentDidMount() {    
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
    //group by engineer
    const engineers=groupBy(data,'engineer');
    //console.log('engineers',engineers,data);
     
    let engData= engineers.map((e,index)=>{
      ////console.log('Months',this.helper.getMonths(e.items, "engineer"))
       
     
      return {
        label: e.groupName,
        data: this.helper.getMonths(e.items, "amount").map(
          (d) => d.value
        ),
        backgroundColor: this.colors[index],
        borderColor: this.colors[index],
        borderWidth,
        fill,
      };
    });
    console.log("color",engData.map(e=>e.backgroundColor));
    engData= sort(engData,'label');    
    return engData;    
  }
  inTeam=(teamId,name)=>{
    
    const {consultants}=this.props;
    const user=consultants.find(c=>c.teamId == teamId && c.name == name);
    if(user)
    return true;
    else return false;
  }
  getChartData = (data,filter) => {     
    let filterData = [];
    let dataLabels = [];
    //console.log(filter.resultType);
    data=this.getDataFilter(data);
    
    if (filter.resultType == ReportType.Monthly) {
      filterData = this.getMonthlyData(data);
      dataLabels = this.getMonthlyLabels(data);
    }
   
    return { filterData,  dataLabels };
  };
  getDataFilter=(data)=>{    
    const {filter}=this.props;
    let dataFilter=data.filter(d=>{
      if(filter.teams.hd&&this.inTeam(1,d.engineer)
      ||filter.teams.es&&this.inTeam(2,d.engineer)
      ||filter.teams.sp&&this.inTeam(4,d.engineer)
      ||filter.teams.p&&this.inTeam(5,d.engineer)
      )
      {
        //
        return true;
      }
      else if(filter.teams.hd&&filter.teams.es&&filter.teams.sp&&filter.teams.p)
      return true;
    });
    if(filter.consName!=''&&filter.consName!=null)
      dataFilter= dataFilter.filter(d=>d.engineer==filter.consName); 
    return dataFilter;
  }
  getChart = () => {
    const {data,filter}=this.state;
    const { filterData, dataLabels } = this.getChartData(data,filter);
    ////console.log('chart data',filterData, dataLabels);
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
  getSummaryElement=()=>{
    let {data}=this.props;
    data=this.getDataFilter(data);
    const engineers=groupBy(sort(data,'engineer'),'engineer');
    //console.log('engineers',engineers,data);
    //calc average
    engineers.map(e=>{
     e.average= (e.items.reduce((curr,prev)=>{       
        curr +=parseFloat(prev.amount);
        return curr;
      },0)/e.items.length).toFixed(1);
    });
     
    return <div>
      <h3>Monthly Average Over The Selected Period</h3>
      <table className="table table-striped" style={{width:300}}>
        <tbody>
          {engineers.map(e=><tr>
            <td>{e.groupName}</td>
            <td>{e.average}</td>
          </tr>)}
        </tbody>
      </table>
    </div>
  }
 
  render() {    
    return (
      <div>
        {this.getChart()}        
        {this.getSummaryElement()}
      </div>
    );
  }
}
 