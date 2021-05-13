import React from 'react';
import ReactDOM from 'react-dom';
import MainComponent from "../shared/MainComponent";
import Spinner from "../shared/Spinner/Spinner";
import APIHeader from "../services/APIHeader";
import ToolTip from '../shared/ToolTip';
import Toggle from '../shared/Toggle';
import CNCCKEditor from '../shared/CNCCKEditor';
import './HeaderComponent.css';
import './../style.css';
import '../shared/table/table.css';
import PortalDocumentComponent from './subComponents/PortalDocumentComponent';

class HeaderComponent extends MainComponent {
  el = React.createElement;
  tabs = [];
  api = new APIHeader();
  intervalRef;
  TAB_COMPANY = "COMPANY";
  TAB_BILLING = "BILLING";
  TAB_SERVICEDESK = "SERVICEDESK";
  TAB_GUI = "GUI";
  TAB_PORTAL = "PORTAL";
  TAB_BACKUPS = "BACKUPS";
  TAB_PROJECTS = "PROJECTS";
  TAB_ASSET_MANAGEMENT = "ASSET_MANAGEMENT";
  TAB_SECURITY = "SECURITY";
  TAB_ACCOUNT_MANAGEMENT = "ACCOUNT_MANAGEMENT";
  TAB_CUSTOMERCONTACTFLAGS = "CUSTOMER_CONTACT_FLAGS";
  inputType={
    Text:"text",
    Number:"number",
    Time:"time",
    CheckBox:"checkbox",
    Editor:"editor"
  }
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      currentUser: null,
      data: {
        // name: "",
        // add1:"",
        // add2:"",
        // add3:"",
        // town:"",
        // county:"",
        // postcode:"",
        // phone:"",
        // fax:"",
        // goodsContact:"",
        // gscItemID:"",
        // gscItemDescription:"",
        // yearlySicknessThresholdWarning:"",
        // stdVATCode:"",
        // billingEndTime:""
      },
      filter: {
        activeTab: "",
      },
    };
    this.tabs = [
      { id: this.TAB_COMPANY, title: "Company", icon: null },
      { id: this.TAB_BILLING, title: "Billing", icon: null },
      { id: this.TAB_SERVICEDESK, title: "ServiceDesk", icon: null },
      { id: this.TAB_GUI, title: "GUI", icon: null },
      { id: this.TAB_PORTAL, title: "Portal", icon: null },
      { id: this.TAB_BACKUPS, title: "Backups", icon: null },
      { id: this.TAB_PROJECTS, title: "Projects", icon: null },
      { id: this.TAB_ASSET_MANAGEMENT, title: "Asset Management", icon: null },
      { id: this.TAB_SECURITY, title: "Security", icon: null },
      {
        id: this.TAB_ACCOUNT_MANAGEMENT,
        title: "Account Management",
        icon: null,
      },
      {
        id: this.TAB_CUSTOMERCONTACTFLAGS,
        title: "Customer Contact Flags",
        icon: null,
      },
    ];
  }

  componentDidMount() {
    this.loadFilterFromStorage();
    this.api.getHeaderData().then((res) => {
      //console.log(res);
      if (res.state) this.setState({ data: res.data });
    });
  }

  isActive = (code) => {
    const { filter } = this.state;
    if (filter.activeTab == code) return "active";
    else return "";
  };
  setActiveTab = (code) => {
    const { filter } = this.state;
    filter.activeTab = code;
    this.saveFilter(filter);
    this.setState({ filter, queueData: [] });
  };

  getTabsElement = () => {
    const { el, tabs } = this;
    return el(
      "div",
      {
        key: "tab",
        className: "tab-container",
        style: {
          flexWrap: "wrap",
          justifyContent: "flex-start",
          maxWidth: 1500,
        },
      },
      tabs.map((t) => {
        return el(
          "i",
          {
            key: t.id,
            className: this.isActive(t.id) + " nowrap",
            onClick: () => this.setActiveTab(t.id),
            style: { width: 200 },
          },
          t.title,
          t.icon
            ? el("span", {
                className: t.icon,
                style: {
                  fontSize: "12px",
                  marginTop: "-12px",
                  marginLeft: "-5px",
                  position: "absolute",
                  color: "#000",
                },
              })
            : null
        );
      })
    );
  };
  loadFilterFromStorage = () => {
    let filter = localStorage.getItem("Header");
    if (filter) filter = JSON.parse(filter);
    else filter = this.state.filter;
    this.setState({ filter }, () => {});
  };
  setFilterValue = (property, value) => {
    const { filter } = this.state;
    filter[property] = value;
    this.setState({ filter }, () => this.saveFilter(filter));
  };

  saveFilter(filter) {
    localStorage.setItem("Header", JSON.stringify(filter));
  }
  getActiveTab = () => {
    switch (this.state.filter.activeTab) {
      case this.TAB_COMPANY:
        return this.getCompanyTab();
      case this.TAB_BILLING:
        return this.getBillingTab();
      case this.TAB_SERVICEDESK:
        return this.getServiceDeskTab();
      case this.TAB_GUI:
        return this.getGUITab();
      case this.TAB_PORTAL:
        return this.getPortalTab();
      case this.TAB_BACKUPS:
        return this.getBackupTab();
      case this.TAB_PROJECTS:
        return this.getProjectsTab();
      case this.TAB_ASSET_MANAGEMENT:
        return this.getAssetManagmentTab();
      case this.TAB_SECURITY:
        return this.getSecurityTab();
      case this.TAB_ACCOUNT_MANAGEMENT:
        return this.getAccountManagmentTab();
      case this.TAB_CUSTOMERCONTACTFLAGS:
        return this.getCustomerContactTab();
      default:
        return null;
    }
  };
  getInput = (label, field,type="text", required=false,toolTip="", max="", step="", maxlength="",size="") => {
    return (
      <tr>
        <td style={{ textAlign: "right" }}>{label}</td>
        <td >
          <ToolTip title={toolTip}>
            {this.getInputElement( field,type, required, max, step, maxlength,size)}
          </ToolTip>
        </td>
      </tr>
    );
  };
  getInputElement=( field,type="text", required=false, max="", step="", maxlength="",size="")=>{
    const { data } = this.state;
    switch (type) {
      case this.inputType.CheckBox:
        return (
          <Toggle
            checked={data[field]}
            onChange={() => this.setValue(field, !data[field])}
          ></Toggle>
        );
      case this.inputType.Editor:
        return (
         
          <div>
             <div id="internalNoteTop"
                     key="bottomElement"
                />
            <CNCCKEditor
            key={field}
            name={field}
            value={data[field]}
            onChange={(data) => this.setValue(field, data)}
            className="CNCCKEditor"
            type="inline"
            height="500"
            width="800"
            sharedSpaces={true}
            top="internalNoteTop"
            bottom="internalNoteBottom"
          ></CNCCKEditor>
            <div id="internalNoteBottom"
                     key="bottomElement"
                />
          </div>
          
        );
      default:
        return (
          <input
            type={type}
            required={required}
            className="form-control"
            value={data[field]}
            max={max}
            step={step}
            maxLength={maxlength}
            size={size}
            onChange={(event) => this.setValue(field, event.target.value)}
          ></input>
        );
    }
  }
  getSelectInput = (label, field, items,required=false) => {
    const { data } = this.state;
    return (
      <tr>
        <td style={{textAlign:"right"}}>{label}</td>
        <td width="250px">
          <select            
            required={required}
            className="form-control"
            value={data[field]}
            onChange={(event) => this.setValue(field, event.target.value)}
          >
            {items.map(item=><option key={item.id} value={item.id}>{item.text}</option>)}
            
          </select>
        </td>
      </tr>
    );
  };
  getCompanyTab = () => {    
    return (
      <table className="table table-striped" style={{width:500}}>
        <tbody>
          {this.getInput("Company Name","name",this.inputType.Text,true)}   
          {this.getInput("Address","add1")}    
          {this.getInput("","add2")}
          {this.getInput("","add3")}
          {this.getInput("Town","town")}
          {this.getInput("County","county")}
          {this.getInput("Postcode","postcode")}
          {this.getInput("Phone","phone")}
          {this.getInput("Fax","fax")}
          {this.getInput("Goods Contact","goodsContact")}
          {this.getInput("GSC Item","gscItemDescription")}
          {this.getInput("Yearly Sickness Threshold Warning","yearlySicknessThresholdWarning",this.inputType.Number,true,"Payroll will be alerted if a person has more sick days than this per year")}
        </tbody>
      </table>
    );
  };
  getBillingTab = () => {    
    let stdVatCodes=[];
    for(let i=0;i<10;i++)
      stdVatCodes.push({id:`T${i}`,text:`T${i}`});
    return (
      <table className="table table-striped" style={{width:500}}>
        <tbody>
          {this.getSelectInput("Standard VAT Code","stdVATCode",stdVatCodes)}   
          {this.getInput("Billing Start Time","billingStartTime",this.inputType.Time,true)}
          {this.getInput("Billing End Time","billingEndTime",this.inputType.Time,true)}
          {this.getInput("Shift Start (Overtime Calculation)","overtimeStartTime",this.inputType.Time,true,"Times before this will be counted as overtime")}
          {this.getInput("Shift End (Overtime Calculation)","overtimeEndTime",this.inputType.Time,true,"Times after this will be counted as overtime")}
          {this.getInput("Minimum Overtime Minutes Required","minimumOvertimeMinutesRequired",this.inputType.Number,true,"Activites under this amount won't be considered for overtime")}
          {this.getInput("Hourly Labour Cost","hourlyLabourCost",this.inputType.Number,true,"Hourly internal cost of running CNC")}
		  {this.getInput("Days In Advance Expenses Next Month Alert	","daysInAdvanceExpensesNextMonthAlert",this.inputType.Number,true,"The days before Next Months Processing date that we are going to start sending alerts about unauthorised expenses or overtime")} 
        </tbody>
      </table>
    );
  };
  getServiceDeskTab = () => {
    return (
      <table className="table table-striped" style={{width:500}}>
        <tbody>
          {this.getInput("Fix SLA Breach Warning Hours","fixSLABreachWarningHours",this.inputType.Number,true,
          "",999.9,0.1,5,5)}   
          {this.getInput("SR Auto-complete Threshold Hours","srAutocompleteThresholdHours",this.inputType.Number,true,"Activity hours below which SRs are automatically set to completed by the system","","",5,5)}   
          {this.getInput("Starters / Leavers Auto-complete Threshold Hours","srStartersLeaversAutoCompleteThresholdHours",this.inputType.Number,true,"Activity hours below which SRs are automatically set to completed by the system if reason is starter/leaver","","",5,5)}   
          {this.getInput("P4 SR Prompt for Contract Threshold Hours","srPromptContractThresholdHours",this.inputType.Number,true,"Activity Hours below which to prompt for a contract when fixing SRs of priority greater than 3","","",5,5)}   
          {this.getInput("SR closure reminder after number of days","closureReminderDays",this.inputType.Number,true,"Days after which get a closure reminder","","",5,5)}   
          {this.getInput("Closing SR Buffer Minutes","closingSRBufferMinutes",this.inputType.Number,true,"Time added to the team of the closing engineer","","",5,5)}
          {this.getInput("Helpdesk Team SR Default Limit Minutes","hdTeamLimitMinutes",this.inputType.Number,true,"Default limit in minutes that members of HD team have to solve SRs","","",5,5)}   
          {this.getInput("Helpdesk Team Target Log %","hdTeamTargetLogPercentage",this.inputType.Number,true,"Target % of time to be logged in a day for Help Desk team","","",5,5)}   
          {this.getInput("Helpdesk Team Target SLA Response %","hdTeamTargetSlaPercentage",this.inputType.Number,true,"Target % of SRs to be responded to within SLA","","",5,5)}   
          {this.getInput("Helpdesk Team Target Fix Hours","hdTeamTargetFixHours",this.inputType.Number,true,"Target hours within which to fix an SR","","",5,5)}   
          {this.getInput("Helpdesk Team Target Fix Qty Per Month","hdTeamTargetFixQtyPerMonth",this.inputType.Number,true,"Target number of SRs fix per month","","",5,5)}   
          {this.getInput("Escalation Team Target Log %","esTeamTargetLogPercentage",this.inputType.Number,true,"Target % of time to be logged in a day for Escalation team","","",5,5)}   
          {this.getInput("Escalation Team SR Default Limit Minutes","esTeamLimitMinutes",this.inputType.Number,true,"Default limit in minutes that members of Escalation team have to solve SRs","","",5,5)}   
          {this.getInput("Escalation Team Target SLA Response %","esTeamTargetSlaPercentage",this.inputType.Number,true,"Target % of SRs to be responded to within SLA","","",5,5)}   
          {this.getInput("Escalation Team Target Fix Hours","esTeamTargetFixHours",this.inputType.Number,true,"Target hours for SRs to be fixed to within","","",5,5)}   
          {this.getInput("Escalation Team Target Fix Qty Per Month","esTeamTargetFixQtyPerMonth",this.inputType.Number,true,"Target number of SRs fix per month","","",5,5)}   
          {this.getInput("Small Projects Team Target Log %","smallProjectsTeamTargetLogPercentage",this.inputType.Number,true,"Target % of time to be logged in a day for Small Projects team","","",5,5)}   
          {this.getInput("Small Projects Team SR Default Limit Minutes","smallProjectsTeamLimitMinutes",this.inputType.Number,true,"Default limit in minutes that members of Small Projects team have to solve SRs","","",5,5)}   
          {this.getInput("Small Projects Team Minutes in a day","smallProjectsTeamMinutesInADay",this.inputType.Number,true,"The amount of minutes in a day for the Small Projects Team","","",5,5)}   
          {this.getInput("Small Projects Team Target SLA Response %","smallProjectsTeamTargetSlaPercentage",this.inputType.Number,true,"Target % of SRs to be responded to within SLA","","",5,5)}   
          {this.getInput("Small Projects Team Target Fix Hours","smallProjectsTeamTargetFixHours",this.inputType.Number,true,"Target hours for SRs to be fixed to within","","",5,5)}   
          {this.getInput("Small Projects Team Target Fix Qty Per Month","smallProjectsTeamTargetFixQtyPerMonth",this.inputType.Number,true,"Target number of SRs fix per month","","",5,5)}   
          {this.getInput("Project Team Target Log %","projectTeamTargetLogPercentage",this.inputType.Number,true,"Target % of time to be logged in a day for Project team","","",5,5)}   
          {this.getInput("Project Team SR Default Limit Minutes","projectTeamLimitMinutes",this.inputType.Number,true,"Default limit in minutes that members of Project team have to solve SRs","","",5,5)}   
          {this.getInput("Project Team Minutes in a day","projectTeamMinutesInADay",this.inputType.Number,true,"The amount of minutes in a day for the Project Team","","",5,5)}   
          {this.getInput("Project Team Target SLA Response %","projectTeamTargetSlaPercentage",this.inputType.Number,true,"Target % of SRs to be responded to within SLA","","",5,5)}   
          {this.getInput("Project Team Target Fix Hours","projectTeamTargetFixHours",this.inputType.Number,true,"Target hours for SRs to be fixed to within","","",5,5)}   
          {this.getInput("Project Team Target Fix Qty Per Month","projectTeamTargetFixQtyPerMonth",this.inputType.Number,true,"Target number of SRs fix per month","","",5,5)}   
          {this.getInput("SD Dashboard Engineers In SR Engineers Min Count","SDDashboardEngineersInSREngineersMaxCount",this.inputType.Number,true,"The minimum number of engineers that will be accounted for the SD Dashboard Engineers table","","",5,5)}   
          {this.getInput("SD Dashboard Engineers In SR In Past Hours","SDDashboardEngineersInSRInPastHours",this.inputType.Number,true,"The amount of hours to run the report for in the past (e.g. last 24 hours)","","",5,5)}   
          {this.getInput("SD 24x7 notification option begin","serviceDeskNotification24hBegin",this.inputType.Time,true,"","","",5,5)}   
          {this.getInput("SD 24x7 notification option end","serviceDeskNotification24hEnd",this.inputType.Time,true,"","","",5,5)}   
          {this.getInput("SR High Activity Count Email Trigger","highActivityAlertCount",this.inputType.Number,true,"The number of activities for a Service Request on 1 day that triggers alert email","","",5,5)}   
          {this.getInput("Customer Contact Activity Warn Hours","customerContactWarnHours",this.inputType.Number,true,"Activity hours on customer contact above which to warn user","","",5,5)}   
          {this.getInput("Remote Support Activity Warn Hours","remoteSupportWarnHours",this.inputType.Number,true,"Activity hours on remote support above which to warn user","","",5,5)}   
          {this.getInput("Remote Support Activity Min Warn Hours","RemoteSupportMinWarnHours",this.inputType.Number,true,"Activity hours on remote support below which to warn user","","",5,5)}   
          {this.getInput("Auto critical P1 after XX hours","autoCriticalP1Hours",this.inputType.Number,true,"SR Chargeable hours after which consider SR as critical","",0.01,5,5)}   
          {this.getInput("Auto critical P2 after XX hours","autoCriticalP2Hours",this.inputType.Number,true,"SR Chargeable hours after which consider SR as critical","",0.01,5,5)}   
          {this.getInput("Auto critical P3 after XX hours","autoCriticalP3Hours",this.inputType.Number,true,"SR Chargeable hours after which consider SR as critical","",0.01,5,5)}   
          {this.getInput("HD Team Management Time Approval Required After Minutes","hdTeamManagementTimeApprovalMinutes",this.inputType.Number,true,"Minutes after which assigning more time to an SR in HD team requires Management's approval","",1,5,5)}   
          {this.getInput("ES Team Management Time Approval Required After Minutes","esTeamManagementTimeApprovalMinutes",this.inputType.Number,true,"Minutes after which assigning more time to an SR in HD team requires Management's approval","",1,5,5)} 
          {this.getInput("Small Projects Team Management Time Approval Required After Minutes","smallProjectsTeamManagementTimeApprovalMinutes",this.inputType.Number,true,"Minutes after which assigning more time to an SR in HD team requires Management's approval","",1,5,5)}    
          {this.getInput("7 Day Target","sevenDayerTarget",this.inputType.Number,true,"7 Day Target","","",5,5)}   
          {this.getInput("7 Dayer Amber Days","sevenDayerAmberDays",this.inputType.Number,true,"7 Dayer Amber Days","","",5,5)}   
          {this.getInput("7 Dayer Red Days","sevenDayerRedDays",this.inputType.Number,true,"7 Dayer Red Days","","",5,5)}   
          {this.getInput("Pending Time Limit Action Threshold Minutes","pendingTimeLimitActionThresholdMinutes",this.inputType.Number,true,"Threshold Minutes","","",5,5)}                   
        </tbody>
      </table>
    );
  };
  //          {this.getInput("","",this.inputType.Number,true,"","","",5,5)}   
  getGUITab = () => {
    return (
      <table className="table table-striped"  style={{width:500}}>
        <tbody>        
          {this.getInput("Number Of Allowed Mistakes in Editor","numberOfAllowedMistakes",this.inputType.Number,true,"Text editor grammar and spelling mistake threshold","","",5,5)}     
          {this.getInput("Keyword Matching Likeness %","keywordMatchingPercent",this.inputType.Number,true,"Likeless needed for a match when logging new SRs and comparing to existing","","",5,5)}                 
        </tbody>
      </table>
    );
  }
  getPortalTab = () => {
    return (
      <div>
      <table className="table table-striped"  style={{width:500}}>
        <tbody>        
          {this.getInput("Portal PIN","portalPin",this.inputType.Number,true,"Visible for all cusomter contacts with ServiceDesk or Plus contract","","",5,5)}     
          {this.getInput("Portal 24 Hour Support PIN","portal24HourPin",this.inputType.Number,true,"Visible for Main Contacts only","","",5,5)}                 
        </tbody>
      </table>
      <PortalDocumentComponent>

      </PortalDocumentComponent>
      </div>
    );
  }
  
  getBackupTab = () => {
    return (
      <table className="table table-striped"  style={{width:500}}>
        <tbody>        
          {this.getInput("Backups target success rate","backupTargetSuccessRate",this.inputType.Number,true,"The target % rate of success for backups","","",5,5)}     
          {this.getInput("Backup Replication target success rate","backupReplicationTargetSuccessRate",this.inputType.Number,true,"The target % rate of success for backup replication","","",5,5)}                 
          {this.getInput("OBRS Replication Additional Delay Allowance","secondSiteReplicationAdditionalDelayAllowance",this.inputType.Number,true,"The extra days added to the delay allowance","","",5,5)}                 
        </tbody>
      </table>
    );
  }

  getProjectsTab = () => {
    return (
      <table className="table table-striped"  style={{width:500}}>
        <tbody>        
          {this.getInput("Project Commence Notification","projectCommenceNotification",this.inputType.Number,true,"","","",5,5)}     
          {this.getInput("Hold all SO Small Projects P5s for for QA Review","holdAllSOSmallProjectsP5sforQAReview",this.inputType.CheckBox,true,"Will automatically mark all SRs raised from a Sales Order as Hold for QA for this team if selected","","",5,5)}                 
          {this.getInput("Hold all SO Projects P5s for for QA Review","holdAllSOProjectsP5sforQAReview",this.inputType.CheckBox,true,"Will automatically mark all SRs raised from a Sales Order as Hold for QA for this team if selected","","",5,5)}                 
        </tbody>
      </table>
    );
  }
  getAssetManagmentTab = () => {
    return (
      <table className="table table-striped "  style={{width:500}}>
        <tbody>        
          {this.getInput("OS Support Dates Threshold Days","OSSupportDatesThresholdDays",this.inputType.Number,true,"Threshold days","","",5,5)}
          {this.getInput("Antivirus Out of Date Threshold Days","antivirusOutOfDateThresholdDays",this.inputType.Number,true,"Threshold days","","",5,5)}
          {this.getInput("Offline agent threshold days","offlineAgentThresholdDays",this.inputType.Number,true,"Threshold days","","",5,5)}
          {this.getInput("Office 365 Mailbox Yellow Warning Threshold","office365MailboxYellowWarningThreshold",this.inputType.Number,true,"Threshold Percentage","","",5,5)}
          {this.getInput("Office 365 Mailbox Red Warning Threshold","office365MailboxRedWarningThreshold",this.inputType.Number,true,"Threshold Percentage","","",5,5)}
          {this.getInput("Office 365 Active Sync Warn After X Days","office365ActiveSyncWarnAfterXDays",this.inputType.Number,true,"days after which a warning should be sent","","",5,5)}
          {this.getInput("C Drive Free Space Warning Percentage Threshold","cDriveFreeSpaceWarningPercentageThreshold",this.inputType.Number,true,"C Drive Free Space Warning Percentage Threshold","","",5,5)}
          {this.getInput("Other Drive Free Space Warning Percentage Threshold","otherDriveFreeSpaceWarningPercentageThreshold",this.inputType.Number,true,"Other Drive Free Space Warning Percentage Threshold","","",5,5)}
          {this.getInput("Computers seen online within XX Days","computerLastSeenThresholdDays",this.inputType.Number,true,"Threshold to ignore computers from the report if they have been seen within CWA, CWC & Webroot //console within these days","","",5,5)}
        </tbody>
      </table>
    );
  }
  getSecurityTab = () => {
    return (
      <table className="table table-striped"  style={{width:500}}>
        <tbody>        
          {this.getInput("Allowed client IP pattern","allowedClientIpPattern",this.inputType.Text,true,"Regex pattern for restricting client log in","","",70,70)}
          {this.getInput("Solarwinds Partner Name","solarwindsPartnerName",this.inputType.Text,true,"Solarwinds Partner Name","","",50,50)}
          {this.getInput("Solarwinds Username","solarwindsUsername",this.inputType.Text,true,"Solarwinds Username","","",50,50)}
          {this.getInput("Solarwinds Password","solarwindsPassword",this.inputType.Text,true,"Solarwinds Password","","",50,50)}          
        </tbody>
      </table>
    );
  }
  getAccountManagmentTab = () => {
    return (
      <table className="table "  style={{width:700}}>
        <tbody>        
          {this.getInput("Customer Review Meeting Text","customerReviewMeetingText",this.inputType.Editor,true,"Entries here will show in the Meeting Agenda editor","","",70,70)}          
        </tbody>
      </table>
    );
  }
  getCustomerContactTab = () => {
    return <div>
      <table className="table table-striped"  style={{width:500}}>
        <thead>
          <tr>
            <th style={{width:40}}>Flag</th>
            <th>Description</th>
            <th>Default Setting</th>
          </tr>
        </thead>
        <tbody>        
          <tr>
            <td className="text-center"  style={{width:40}}>2</td>
            <td >
              {this.getInputElement("mailshot2FlagDesc",this.inputType.Text,true,"","",50,50)}
            </td>
            <td className="text-center">
            {this.getInputElement("mailshot2FlagDef",this.inputType.CheckBox)}
            </td>
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>3</td>
            <td>
              {this.getInputElement("mailshot3FlagDesc",this.inputType.Text,true,"","",50,50)}
            </td>
            <td className="text-center">
            {this.getInputElement("mailshot3FlagDef",this.inputType.CheckBox)}
            </td>
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>4</td>
            <td>
              {this.getInputElement("mailshot4FlagDesc",this.inputType.Text,true,"","",50,50)}
            </td>
            <td className="text-center">
            {this.getInputElement("mailshot4FlagDef",this.inputType.CheckBox)}
            </td>
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>8</td>
            <td>
              {this.getInputElement("mailshot8FlagDesc",this.inputType.Text,true,"","",50,50)}
            </td>
            <td className="text-center">
            {this.getInputElement("mailshot8FlagDef",this.inputType.CheckBox)}
            </td>
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>9</td>
            <td>
              {this.getInputElement("mailshot9FlagDesc",this.inputType.Text,true,"","",50,50)}
            </td>
            <td className="text-center">
            {this.getInputElement("mailshot9FlagDef",this.inputType.CheckBox)}
            </td>
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>11</td>
            <td>
              {this.getInputElement("mailshot11FlagDesc",this.inputType.Text,true,"","",50,50)}
            </td>
            <td className="text-center">
            {this.getInputElement("mailshot11FlagDef",this.inputType.CheckBox)}
            </td>
          </tr>
        </tbody>
      </table>
      <table className="table table-striped"   style={{width:500}}>
        <thead>
          <tr>
            <th style={{width:40}}>Priority</th>
            <th>Description</th>             
          </tr>
        </thead>
        <tbody>        
          <tr>
            <td className="text-center"  style={{width:40}}>1</td>
            <td>
              {this.getInputElement("priority1Desc",this.inputType.Text,true,"Priority 1 description, this text oontrols the wording in the portal and CNCAPPS","",65,65)}
            </td>             
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>2</td>
            <td>
              {this.getInputElement("priority2Desc",this.inputType.Text,true,"Priority 2 description, this text oontrols the wording in the portal and CNCAPPS","",65,65)}
            </td>              
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>3</td>
            <td>
              {this.getInputElement("priority3Desc",this.inputType.Text,true,"Priority 3 description, this text oontrols the wording in the portal and CNCAPPS","",65,65)}
            </td>             
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>4</td>
            <td>
              {this.getInputElement("priority4Desc",this.inputType.Text,true,"Priority 4 description, this text oontrols the wording in the portal and CNCAPPS","",65,65)}
            </td>             
          </tr>
          <tr>
            <td className="text-center"  style={{width:40}}>5</td>
            <td>
              {this.getInputElement("priority5Desc",this.inputType.Text,true,"Priority 5 description, this text oontrols the wording in the portal and CNCAPPS","",65,65)}
            </td>              
          </tr>
        </tbody>
      </table>
      </div>;
  }
  handleSave=()=>{
    const {data}=this.state;
    data.mailshot11FlagDef=data.mailshot11FlagDef?"Y":"N";
    data.mailshot9FlagDef=data.mailshot9FlagDef?"Y":"N";
    data.mailshot8FlagDef=data.mailshot8FlagDef?"Y":"N";
    data.mailshot4FlagDef=data.mailshot4FlagDef?"Y":"N";
    data.mailshot3FlagDef=data.mailshot3FlagDef?"Y":"N";
    data.mailshot2FlagDef=data.mailshot2FlagDef?"Y":"N";     
    //console.log(data);
    this.api.updateHeaderData(data).then(res=>{
      //console.log(res);
      // if(res.state)
      // this.alert("data saved");
    },err=>{      
      this.alert("Please enter all required fields")
    })
  }
  render() {
    return (
      <div>
        <Spinner key="spinner" show={this.state.showSpinner}></Spinner>
        {this.getAlert()}
        {this.getTabsElement()}
        {this.getActiveTab()}
        <button onClick={this.handleSave}>Save</button>
      </div>
    );
  }
}

export default HeaderComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactMainHeaderComponent");
        ReactDOM.render(React.createElement(HeaderComponent), domContainer);
    }
)
