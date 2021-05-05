import React from 'react';
import ReactDOM from 'react-dom';
import MainComponent from "../shared/MainComponent";
import Spinner from "../shared/Spinner/Spinner";
import APIHeader from "../services/APIHeader";
import './../style.css';
import './HeaderComponent.css';
import ToolTip from '../shared/ToolTip';
 
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
    Time:"time"
  }
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      currentUser: null,
      data: {
        name: "",
        add1:"",
        add2:"",
        add3:"",
        town:"",
        county:"",
        postcode:"",
        phone:"",
        fax:"",
        goodsContact:"",
        gscItemID:"",
        gscItemDescription:"",
        yearlySicknessThresholdWarning:"",
        stdVATCode:"",
        billingEndTime:""
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
      console.log(res);
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
    const { currentUser } = this.state;
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
      default:
        return null;
    }
  };
  getInput = (label, field,type="text", required=false,toolTip="", max="", step="", maxlength="",size="") => {
    const { data } = this.state;
    return (
      <tr>
        <td style={{textAlign:"right"}}>{label}</td>
        <td width="250px">
          <ToolTip title={toolTip}>
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
          </ToolTip>
        </td>
      </tr>
    );
  };
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
      <table className="table table-striped">
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
          {this.getInput("Yearly Sickness Threshold Warning","yearlySicknessThresholdWarning",this.inputType.Number)}
        </tbody>
      </table>
    );
  };
  getBillingTab = () => {    
    let stdVatCodes=[];
    for(let i=0;i<10;i++)
      stdVatCodes.push({id:`T${i}`,text:`T${i}`});
    return (
      <table className="table table-striped">
        <tbody>
          {this.getSelectInput("Standard VAT Code","stdVATCode",stdVatCodes)}   
          {this.getInput("Billing Start Time","billingStartTime",this.inputType.Time,true)}
          {this.getInput("Billing End Time","billingEndTime",this.inputType.Time,true)}
          {this.getInput("Shift Start(Overtime Calculation)","overtimeStartTime",this.inputType.Time,true)}
          {this.getInput("Shift End (Overtime Calculation)","overtimeEndTime",this.inputType.Time,true)}
          {this.getInput("Hourly Labour Cost","hourlyLabourCost",this.inputType.Number,true)}
          {this.getInput("Minimum Overtime Minutes Required","minimumOvertimeMinutesRequired",this.inputType.Number,true)}
          {this.getInput("Days In Advance Expenses Next Month Alert	","daysInAdvanceExpensesNextMonthAlert",this.inputType.Number,true,"The days before Next Month's Processing date that we are going to start sending alerts about unauthorized expenses or overtime")} 
        </tbody>
      </table>
    );
  };
  getServiceDeskTab = () => {
    return (
      <table className="table table-striped">
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
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
          {this.getInput("","stdVATCode",this.inputType.Number,true,"","","",5,5)}   
        
        </tbody>
      </table>
    );
  };
  render() {
    return (
      <div>
        <Spinner key="spinner" show={this.state.showSpinner}></Spinner>
        {this.getAlert()}
        {this.getTabsElement()}
        {this.getActiveTab()}
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
