"use strict";
import Spinner from './../shared/Spinner/Spinner';
import MainComponent from '../shared/MainComponent'
import React from 'react';
import ReactDOM from 'react-dom';
import './KPIReportComponent.css'
import './../style.css';
import './../shared/table/table.css';
import APIKPIReport from './services/APIKPIReport';
import {Bar,Line } from 'react-chartjs-2';
import { groupBy, sort } from '../utils/utils';
import SRFixedComponent from './subComponents/SRFixedComponent';
import CustomerSearch from '../shared/CustomerSearch';
import PrioritiesRaisedComponent from './subComponents/PrioritiesRaisedComponent';
import QuotationConversionComponent from './subComponents/QuotationConversionComponent';
export const ReportType= { Daily: "day", Weekly: "week", Monthly: "month" }

export default class KPIReportComponent extends MainComponent {
  api;
  ResultType;
  colors;
  reports;
  reportParamters;
  REP_SR_FIXED=1;
  REP_PRIORITIES_RAISED=2;
  REP_PRIORITIES_RAISED_ALLOW_SR=3;
  REP_QUOTATION_CONVERSION=4;
  constructor(props) {
    super(props);
    this.ResultType = ReportType;
    this.state = {
      ...this.state,
      filter: {
        from: this.getInitStartDate(),
        to: this.getInitEndDate(),
        resultType: this.ResultType.Weekly,
        customerID:''
      },
      data: [],
      reports:[],
      activeReport:null
    };
    this.api = new APIKPIReport();    
    this.reportParamters={dateFrom:'dateFrom',dateTo:'dateTo',customer:'customer',resultType:'resultType'};
    moment.locale("en");
    moment.updateLocale("en", {
      week: {
        dow: 5,
      },
    });
  }
  
  componentDidMount() {
    this.getReports();
  }
  getReports=()=>{
    let { activeReport } = this.state;
    const reports=[
      {
        id:this.REP_SR_FIXED,
        title:"SR Fixed",
        paramters:[
          this.reportParamters.dateFrom,
          this.reportParamters.dateTo,
          this.reportParamters.customer,
          this.reportParamters.resultType,
        ]
      },
      {
        id:this.REP_PRIORITIES_RAISED,
        title:"Priorities Raised",
        paramters:[
          this.reportParamters.dateFrom,
          this.reportParamters.dateTo,
          this.reportParamters.customer,
          this.reportParamters.resultType,
        ]
      },
      {
        id:this.REP_PRIORITIES_RAISED_ALLOW_SR,
        title:"Priorities Raised Allow SR",
        paramters:[
          this.reportParamters.dateFrom,
          this.reportParamters.dateTo,
          this.reportParamters.customer,
          this.reportParamters.resultType,
        ]
      },
      {
        id:this.REP_QUOTATION_CONVERSION,
        title:"Quotation Conversion",
        paramters:[
          this.reportParamters.dateFrom,
          this.reportParamters.dateTo,
          this.reportParamters.customer,
          this.reportParamters.resultType,
        ]
      }
    ];    
    if(!activeReport)
      activeReport=reports[0];
    this.setState({reports,activeReport},()=> this.handleReportView()) ;
  }
  getInitStartDate(){
    return moment().subtract(6, 'months').format('YYYY-MM-DD');
  }
  getInitEndDate(){
    ////console.log("end date",moment().subtract(1, 'weeks').startOf('w')).format('YYYY-MM-DD');
    return moment().subtract(1, 'weeks').startOf('w').format('YYYY-MM-DD');
  }
  setFilter = (field, value) => {
    const { filter,data } = this.state;
    filter[field] = value;
    this.setState({ filter });
    //console.log(filter);
  };

  getFilterElement = () => {
    const { filter, reports } = this.state;
    return (
      <table>
        <tbody>
          <tr>
            <td>Report</td>
            <td>
              <select style={{ width: 140 }} onChange={this.handleReportChange}>
                {reports.map((r) => (
                  <option key={r.id} value={r.id}>
                    {r.title}
                  </option>
                ))}
              </select>
            </td>
            {this.hasParamter(this.reportParamters.resultType) ? (
              <td>Type</td>
            ) : null}
            {this.hasParamter(this.reportParamters.resultType) ? (
              <td>
                <select
                  style={{ width: 140 }}
                  value={filter.resultType}
                  onChange={($event) =>
                    this.setFilter("resultType", $event.target.value)
                  }
                >
                  <option value="day">Daily</option>
                  <option value="week">Weekly</option>
                  <option value="month">Monthly</option>
                </select>
              </td>
            ) : null}
          </tr>
          <tr>
            {this.hasParamter(this.reportParamters.dateFrom) ? (
              <React.Fragment>
                <td>
                  <label>Start date</label>
                </td>

                <td>
                  <input
                    type="date"
                    value={filter.from}
                    onChange={($event) =>
                      this.setFilter("from", $event.target.value)
                    }
                  ></input>
                </td>
              </React.Fragment>
            ) : null}
            {this.hasParamter(this.reportParamters.dateTo) ? (
              <React.Fragment>
                <td>
                  <label>End date</label>
                </td>
                <td>
                  <input
                    type="date"
                    value={filter.to}
                    onChange={($event) =>
                      this.setFilter("to", $event.target.value)
                    }
                  ></input>
                </td>
              </React.Fragment>
            ) : null}
          </tr>
          <tr>
            {this.hasParamter(this.reportParamters.customer) ? (
              <React.Fragment>
                <td>Customer</td>
                <td colSpan={3}>
                  <CustomerSearch
                    onChange={(customer) =>{
                      if(customer!=null)
                      this.setFilter("customerID", customer.id)
                    }
                    }
                    width={340}
                  ></CustomerSearch>
                </td>
              </React.Fragment>
            ) : null}
            <td>
              <button onClick={this.handleReportView}>GO</button>
            </td>
          </tr>
        </tbody>
      </table>
    );
  };  
  handleReportChange=($event)=>{
    const id=$event.target.value;
    const {reports}=this.state;    
    let activeReport=reports[reports.map(r=>r.id).indexOf(parseInt(id))];
    this.setState({activeReport});
  }
  hasParamter=(paramter)=>{
    const {activeReport}=this.state;    
    return activeReport!=null&&activeReport.paramters.indexOf(paramter)>=0;    
  }
  handleReportView = () => {
    const { filter,activeReport } = this.state;
    this.setState({ _showSpinner: true });
    if (filter.from == "") {
      this.alert("You must enter the start date");
      return;
    }
    switch(activeReport?.id)
    {
      case this.REP_SR_FIXED:
        this.api.getSRFixed(filter.from, filter.to,filter.customerID).then((data) => {      
          this.processData(data);
        });
        break;
      case this.REP_PRIORITIES_RAISED:
        this.api.getPriorityRaised(filter.from, filter.to,filter.customerID).then((data) => {      
          this.processData(data);
        });
      break;
      case this.REP_PRIORITIES_RAISED_ALLOW_SR:
        this.api.getPriorityRaisedAllowSR(filter.from, filter.to,filter.customerID).then((data) => {      
          this.processData(data);
        });
      break;
      case this.REP_QUOTATION_CONVERSION:
        this.api.getQuotationConversion(filter.from, filter.to,filter.customerID).then((data) => {      
          this.processData(data,false);
        });
      break;
    }
    
  };
  processData=(data,skipWeekEnds=true)=>{
    const { filter } = this.state;
    if (skipWeekEnds) {
      data = data.filter((a) => {
        if (moment(a.date).day() == 6 || moment(a.date).day() == 0)
          return false;
        else return true;
      });
    }
    if (data.length <= 100) filter.resultType = this.ResultType.Daily;
    if (data.length > 100 && data.length < 200)
      filter.resultType = this.ResultType.Weekly;
    else filter.resultType = this.ResultType.Monthly;
    console.log(data);
    this.setState({ data, _showSpinner: false, filter });
  }
  getActivChart=()=>{
    const {activeReport, data,filter} = this.state;
    //console.log(activeReport,this.REP_PRIORITIES_RAISED);
    switch(activeReport?.id)
    {
      case this.REP_SR_FIXED:
        return <SRFixedComponent data={data} filter={filter} colors={this.colors}></SRFixedComponent>;
      case this.REP_PRIORITIES_RAISED:
        return <PrioritiesRaisedComponent data={data} filter={filter} ></PrioritiesRaisedComponent>;
      case this.REP_PRIORITIES_RAISED_ALLOW_SR:
        return <PrioritiesRaisedComponent data={data} filter={filter} ></PrioritiesRaisedComponent>;
      case this.REP_QUOTATION_CONVERSION:
        return <QuotationConversionComponent data={data} filter={filter}></QuotationConversionComponent>      
      default:
        return null;
    }
  }
  render() {
    const { _showSpinner,data,filter,reports } = this.state;
    
    return (
      <div>
        <Spinner show={_showSpinner}></Spinner>
        {this.getAlert()}
        <h3>Filter Data</h3>
        {this.getFilterElement()}        
               
        {this.getActivChart()}
      </div>
    );
  }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainKPIReport");
    ReactDOM.render(React.createElement(KPIReportComponent), domContainer);
});
