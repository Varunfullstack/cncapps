"use strict";
import Spinner from './../shared/Spinner/Spinner';
import MainComponent from '../shared/MainComponent'
import React from 'react';
import ReactDOM from 'react-dom';
import './KPIReportComponent.css'
import './../style.css';
import './../shared/table/table.css';
import APIKPIReport from './services/APIKPIReport';
import SRFixedComponent from './subComponents/SRFixedComponent';
import CustomerSearch from '../shared/CustomerSearch';
import PrioritiesRaisedComponent from './subComponents/PrioritiesRaisedComponent';
import QuotationConversionComponent from './subComponents/QuotationConversionComponent';
import ServiceRequestsRaisedByContract from "./subComponents/ServiceRequestsRaisedByContract";
import BillingConsultancyComponent from './subComponents/BillingConsultancyComponent';
import APIUser from '../services/APIUser';
import Toggle from '../shared/Toggle';

export const ReportType = {Daily: "day", Weekly: "week", Monthly: "month"}

export default class KPIReportComponent extends MainComponent {
    api;
    apiUsers=new APIUser();
    ResultType;
    colors;
    reports;
    reportparameters;
    REP_SR_FIXED = 1;
    REP_PRIORITIES_RAISED = 2;
    SRS_BY_CONTRACTS = 3;
    REP_QUOTATION_CONVERSION = 4;
    REP_CONFIRMED_BILLED_PER_ENGINEER=5;

    /**
     * SRS_BY_CONTRAC
     * @param props
     */



    constructor(props) {
        super(props);
        this.ResultType = ReportType;
        this.state = {
            ...this.state,
            filter: {
                from: this.getInitStartDate(),
                to: this.getInitEndDate(),
                resultType: this.ResultType.Weekly,
                customerID: '',
                consName:'',
                teams:{
                    hd:true,
                    es:true,
                    sp:true,
                    p:true
                }
            },
            data: [],
            reports: [],
            activeReport: null,
            consultants:[]
        };
        this.api = new APIKPIReport();
        this.reportparameters = {
            dateFrom: 'dateFrom',
            dateTo: 'dateTo',
            customer: 'customer',
            resultType: 'resultType',
            consName:'consName',
            teams:'teams'
        };
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

    getReports = () => {
        let {activeReport} = this.state;
        const reports = [
            {
                id: this.REP_SR_FIXED,
                title: "SR Fixed",
                parameters: [
                    this.reportparameters.dateFrom,
                    this.reportparameters.dateTo,
                    this.reportparameters.customer,
                    this.reportparameters.resultType,
                ]
            },
            {
                id: this.REP_PRIORITIES_RAISED,
                title: "Priorities Raised",
                parameters: [
                    this.reportparameters.dateFrom,
                    this.reportparameters.dateTo,
                    this.reportparameters.customer,
                    this.reportparameters.resultType,
                ]
            },
            {
                id: this.SRS_BY_CONTRACTS,
                title: "SRs Raised By Contract",
                parameters: [
                    this.reportparameters.dateFrom,
                    this.reportparameters.dateTo,
                    this.reportparameters.customer,
                    this.reportparameters.resultType,
                ]
            },
            {
                id: this.REP_QUOTATION_CONVERSION,
                title: "Quotation Conversion",
                parameters: [
                    this.reportparameters.dateFrom,
                    this.reportparameters.dateTo,
                    this.reportparameters.customer,
                    this.reportparameters.resultType,
                ]
            }
            ,
            {
                id: this.REP_CONFIRMED_BILLED_PER_ENGINEER,
                title: "Billed Consultancy By Person",
                parameters: [
                    this.reportparameters.dateFrom,
                    this.reportparameters.dateTo, 
                    this.reportparameters.consName,
                    this.reportparameters.teams
                ]
            }
        ];
        if (!activeReport)
            activeReport = reports[4];
        this.setState({reports, activeReport}, () => this.handleReportView());
    }

    getInitStartDate() {
        return moment().subtract(6, 'months').format('YYYY-MM-DD');
    }

    getInitEndDate() {
        //////console.log("end date",moment().subtract(1, 'weeks').startOf('w')).format('YYYY-MM-DD');
        return moment().subtract(1, 'weeks').startOf('w').format('YYYY-MM-DD');
    }

    setFilter = (field, value) => {
        const {filter, data} = this.state;
        filter[field] = value;
        this.setState({filter});
        ////console.log(filter);
    };

    getFilterElement = () => {
        const {filter, reports,activeReport,consultants} = this.state;
        return (
            <table>
                <tbody>
                <tr>
                    <td>Report</td>
                    <td colSpan="5">
                        <select style={{width: 180}}
                                onChange={this.handleReportChange}
                                value={activeReport?.id}
                        >
                            {reports.map((r) => (
                                <option key={r.id}
                                        value={r.id}
                                >
                                    {r.title}
                                </option>
                            ))}
                        </select>
                    </td>

                </tr>
                <tr>
                    {this.hasParameter(this.reportparameters.dateFrom) ? (
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
                    {this.hasParameter(this.reportparameters.dateTo) ? (
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
                    {this.hasParameter(this.reportparameters.resultType) ? (
                        <td>Type</td>
                    ) : null}
                    {this.hasParameter(this.reportparameters.resultType) ? (
                        <td>
                            <select
                                style={{width: 140}}
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
                    {this.hasParameter(this.reportparameters.teams) ? (
                        <React.Fragment>
                            <td>Teams</td>
                            <td colSpan={3}>
                               <div>
                                   
                            <label className="mr-3 ml-5">HD</label>
                            <Toggle checked={filter.teams.hd}
                                    onChange={(value) => this.setFilterTeam("hd", !filter.teams.hd)}
                            />
                            <label className="mr-3 ml-5">ES</label>
                            <Toggle checked={filter.teams.es}
                                    onChange={(value) => this.setFilterTeam("es", !filter.teams.es)}
                            />
                            <label className="mr-3 ml-5">SP</label>
                            <Toggle checked={filter.teams.sp}
                                    onChange={(value) => this.setFilterTeam("sp", !filter.teams.sp)}
                            />
                            <label className="mr-3 ml-5">P</label>
                            <Toggle checked={filter.teams.p}
                                    onChange={(value) => this.setFilterTeam("p", !filter.teams.p)}
                            />
                             
                               </div>
                            </td>
                        </React.Fragment>
                    ) : null}
                    <td>
                     </td>
                </tr>
                <tr>
                    {this.hasParameter(this.reportparameters.consName) ? (
                        <React.Fragment>
                            <td>Consultant</td>
                            <td colSpan={3}>
                               <select style={{width:340}} value={filter.consName} 
                               onChange={(event)=>this.setFilter('consName',event.target.value)}
                               >
                                <option>All</option>
                                {consultants.map(c=><option key={c.id} value={c.name}>{c.name}</option>)}
                               </select>
                            </td>
                        </React.Fragment>
                    ) : null}
                    <td>
                     </td>
                </tr>
                <tr>
                    {this.hasParameter(this.reportparameters.customer) ? (
                        <React.Fragment>
                            <td>Customer</td>
                            <td colSpan={3}>
                                <CustomerSearch
                                    onChange={(customer) => {
                                        if (customer != null)
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
    setFilterTeam=(team,value)=>{
        const {filter}=this.state;
        filter.teams[team]=value;
        this.setState({filter});
    }
    handleReportChange = ($event) => {
        const id = $event.target.value;
        const {reports} = this.state;
        let activeReport = reports[reports.map(r => r.id).indexOf(parseInt(id))];
        this.setState({activeReport,data: []});
    }
    hasParameter = (parameter) => {
        const {activeReport} = this.state;
        return activeReport != null && activeReport.parameters.indexOf(parameter) >= 0;
    }
    handleReportView = async() => {
        let {filter, activeReport,consultants} = this.state;
 
        this.setState({_showSpinner: true});
        if (filter.from == "") {
            this.alert("You must enter the start date");
            return;
        }
        if(activeReport&&activeReport.parameters.indexOf(this.reportparameters.consName)>=0&&consultants.length==0)
        {
            //console.log('have users');
            consultants=await this.apiUsers.getActiveUsers();
            this.setState({consultants});
        }

        switch (activeReport?.id) {
            case this.REP_SR_FIXED:
                this.api.getSRFixed(filter.from, filter.to, filter.customerID).then((data) => {
                    this.processData(data);
                });
                break;
            case this.REP_PRIORITIES_RAISED:
                this.api.getPriorityRaised(filter.from, filter.to, filter.customerID).then((data) => {
                    this.processData(data);
                });
                break;
            case this.SRS_BY_CONTRACTS:
                this.api.getServiceRequestsRaisedByContract(filter.from, filter.to, filter.customerID).then((data) => {
                    this.processData(data);
                });
                break;
            case this.REP_QUOTATION_CONVERSION:
                this.api.getQuotationConversion(filter.from, filter.to, filter.customerID).then((data) => {
                    this.processData(data, false);
                });
                break;
            case this.REP_CONFIRMED_BILLED_PER_ENGINEER:
                this.api.getEngineerMonthlyBilling(filter.from, filter.to).then((data) => {
                    ////console.log('Engineer Billing',data);                   
                    this.processData(data, false,false,false);
                    //console.log('Engineer Billing',data,this.state.filter);
                });
                break;
        }
       

    };
    processData = (data, skipWeekEnds = true,daily=true,weekly=true,monthly=true) => {
        const {filter} = this.state;
        if (skipWeekEnds) {
            data = data.filter((a) => {
                if (moment(a.date).day() == 6 || moment(a.date).day() == 0)
                    return false;
                else return true;
            });
        }
        if (data.length <= 100&&daily) filter.resultType = this.ResultType.Daily;
        if (data.length > 100 && data.length < 200&&weekly)
            filter.resultType = this.ResultType.Weekly;
        else if(monthly)
        filter.resultType = this.ResultType.Monthly;
        this.setState({data, _showSpinner: false, filter});
    }
    getActiveChart = () => {
        const {activeReport, data, filter,consultants} = this.state;
        switch (activeReport?.id) {
            case this.REP_SR_FIXED:
                return <SRFixedComponent data={data}
                                         filter={filter}
                                         colors={this.colors}
                ></SRFixedComponent>;
            case this.REP_PRIORITIES_RAISED:
                return <PrioritiesRaisedComponent data={data}
                                                  filter={filter}
                ></PrioritiesRaisedComponent>;
            case this.SRS_BY_CONTRACTS:
                return <ServiceRequestsRaisedByContract data={data}
                                                        filter={filter}
                ></ServiceRequestsRaisedByContract>;
            case this.REP_QUOTATION_CONVERSION:
                return <QuotationConversionComponent data={data}
                                                     filter={filter}
                ></QuotationConversionComponent>;
            case this.REP_CONFIRMED_BILLED_PER_ENGINEER:
                return <BillingConsultancyComponent data={data}
                        filter={filter} consultants={consultants}>
                        </BillingConsultancyComponent>;
            default:
                return null;
        }
    }

    render() {
        const {_showSpinner} = this.state;

        return (
            <div>
                <Spinner show={_showSpinner}></Spinner>
                {this.getAlert()}
                <h3>Filter Data</h3>
                {this.getFilterElement()}

                {this.getActiveChart()}
            </div>
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainKPIReport");
    ReactDOM.render(React.createElement(KPIReportComponent), domContainer);
});
