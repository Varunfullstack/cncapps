import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../services/APIProjects';
import APIUser from '../../services/APIUser';
import CNCCKEditor from "../../shared/CNCCKEditor";
import ProjectsHelper from "../helper/ProjectsHelper";
import { groupBy } from "../../utils/utils";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import CustomerSearch from "../../shared/CustomerSearch";
import ProjectIssuesComponent from "./ProjectIssuesComponent";
import ProjectSummaryComponent from "./ProjectSummaryComponent";
import APIProjectOptions from "../../ProjectOptionsComponent/services/APIProjectOptions";
import ProjectStagesHistoryComponent from "./ProjectStagesHistoryComponent";

export default class ProjectDetailsComponent extends MainComponent {
  tabs = [];
  TAB_DETAILS = 1;
  //TAB_HISTORY = 2;
  TAB_TIME_BREAKDOWN=3;
  TAB_PROJECT_ISSUES=4;
  TAB_PROJECT_SUMMARY=5;
  TAB_PROJECT_STAGES=6;
  api = new APIProjects();
  apiUsers=new APIUser();
  helper=new ProjectsHelper();
  apiProjectOptions=new APIProjectOptions();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,      
      activeTab: this.TAB_DETAILS,
      mode:this.props.mode,      
      data:{
        inHoursQuantity:'',
        outOfHoursQuantity:'',
        inHoursMeasure:'d',
        outOfHoursMeasure:'d',
        newUpdate:'',
        description:'',
        notes:'',
        openedDate:'',
        commenceDate:'',
        completedDate:'',
        projectEngineer:'',
        outOfHoursInternalBudgetDays:'',
        inHoursInternalBudgetDays:'',
        originProjectStageID:null
      },
      budgetData:null,
      users:[],
      files:[],
      currentUser:null,
      disabled:'disabled',
      showSalesOrder:false,
      originalOrder:false,
      newOrdHeadID:'',
      projectStages:[],
      projectTypes:[],
      engineers:[],
      projectStagesHistory:[]
    };
    this.tabs = [
      { id: this.TAB_DETAILS, title: "Details", icon: null,visible:true },
      //{ id: this.TAB_HISTORY, title: "History", icon: null,visible:this.props.mode=='edit' },      
      { id: this.TAB_TIME_BREAKDOWN, title: "Time Breakdown", icon: null,visible:this.props.mode=='edit' },      
      { id: this.TAB_PROJECT_ISSUES, title: "Project Issues", icon: null,visible:this.props.mode=='edit' },      
      { id: this.TAB_PROJECT_SUMMARY, title: "Project Summary", icon: null,visible:this.props.mode=='edit' },      
      { id: this.TAB_PROJECT_STAGES, title: "Project Stages History", icon: null,visible:this.props.mode=='edit' },      
    ];
  }

  componentDidMount() {   
    //console.log(this.state.mode);
    if(this.state.mode=='edit') 
      this.getData();
    this.getUsers();

  }
  getUsers=()=>{
    Promise.all([
      this.api.getCurrentUser(),
      this.apiUsers.getActiveUsers(),
      this.apiProjectOptions.getProjectStages(),
      this.apiProjectOptions.getProjectTypes(),
      this.apiUsers.getActiveUsers()
    ]).then(([currentUser, users,projectStages,projectTypes,engineers]) => {
      //console.log(currentUser,projectStages,projectTypes);
      
      this.setState({ users,disabled:!currentUser.isProjectManager?'disabled':'' ,currentUser,projectStages,projectTypes,engineers
    });
    });
  }
  getData=()=>{
    const {projectID}=this.props;
    console.log('projectID',projectID);
    if(projectID!=null)
    {
        this.setState({showSpinner:true});
        Promise.all([this.api.getProject(projectID),
          this.api.getBudgetData(projectID),
          this.api.getProjectStagesHistory(projectID)
        ])
        .then(([data,budgetData,projectStagesHistory])=>{
            console.log({...this.state.data,...data},budgetData);
            data.inHoursQuantity='';
            data.outOfHoursQuantity='';
            this.setState({data:{...this.state.data,...data},budgetData,showSpinner:false,projectStagesHistory});
        });
    }
    
  }
  isActive = (code) => {
    const { activeTab } = this.state;
    if (activeTab == code) return "active";
    else return "";
  };
  setActiveTab = (activeTab) => {      
    this.setState({ activeTab  });    
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
          maxWidth: 1300,
        },
      },
      tabs.filter(t=>t.visible).map((t) => {
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
  getProjectDetailsElement=()=>{
      const { data,users,disabled,mode ,currentUser,projectStages,projectTypes,engineers} = this.state;
      if(mode=='edit'&&!data.projectID||!currentUser)      
        return null;
      return <div>
          <table  >
              <tbody>
                
                <tr>
                  <td>Customer</td>
                  <td>
                    {
                     mode=='add'?<CustomerSearch onChange={(customer)=>this.handleCustomerChange(customer)}></CustomerSearch>:null 
                    }
                    {
                     mode=='edit'?<input value={data.customerName} disabled='disabled' style={{width:300}}></input>:null 
                    }
                    </td>
                </tr>
                  <tr>
                      <td>Description</td>
                      <td><input disabled={disabled} style={{width:300}} value= {data.description} onChange={(event)=>this.setValue('description',event.target.value)}></input></td>
                  </tr>
                  <tr>
                      <td>Project Summary</td>
                      <td> <CNCCKEditor 
                            readOnly={disabled=='disabled'} 
                            value= {data.notes} 
                            onChange={(event)=>this.handleSummaryChange(event) }
                            type="inline"
                            style={{width:700,minHeight:40}}
                            ></CNCCKEditor>
                          
                          </td>
                  </tr>
                  <tr>
                      <td>
                          Project Type
                      </td>
                      <td>
                        <select  className="input" value={data.projectTypeID} onChange={(event)=>this.setValue('projectTypeID',event.target.value)} >
                          <option value=""></option>
                          {projectTypes.map(s=><option key={s.id} value={s.id}>{s.name}</option>)}
                        </select>                          
                      </td>
                  </tr> 

                  <tr>
                      <td>Project Opened Date</td>
                      <td><input  className="input" disabled={disabled}  type="date" value= {data.openedDate} onChange={(event)=>this.setValue('openedDate',event.target.value)}></input></td>
                  </tr>
                  <tr>
                      <td>Project Planning Date	</td>
                      <td><input  className="input" disabled={disabled}  type="date" value= {data.projectPlanningDate} onChange={(event)=>this.setValue('projectPlanningDate',event.target.value)}></input></td>
                  </tr>
                  <tr>
                      <td>Project Commencement Date	</td>
                      <td><input  className="input" disabled={disabled}  type="date" value= {data.commenceDate} onChange={(event)=>this.setValue('commenceDate',event.target.value)}></input></td>
                  </tr>
                  <tr>
                      <td>Expected handover to QA date	</td>
                      <td><input  className="input" disabled={disabled}  type="date" value= {data.expectedHandoverQADate} onChange={(event)=>this.setValue('expectedHandoverQADate',event.target.value)}></input></td>
                  </tr>
                  <tr>
                      <td>Completed Date		</td>
                      <td><input  className="input"  disabled={disabled}  type="date" value= {data.completedDate} onChange={(event)=>this.setValue('completedDate',event.target.value)}></input></td>
                  </tr> 
                  <tr>
                      <td>
                          Project Manager
                      </td>
                      <td>
                        <select  className="input" value= {data.projectManager} onChange={(event)=>this.setValue('projectManager',event.target.value)} >
                          <option value=''></option>
                          {engineers.map(s=><option key={s.id} value={s.id} >{s.name}</option>)}
                        </select>                          
                      </td>
                  </tr> 
                  <tr>
                      <td>Project Engineer</td>
                      <td>
                          <select  className="input" disabled={disabled}  value={data.projectEngineer} onChange={(event)=>this.setValue('projectEngineer',event.target.value)}>
                            <option value={null}>Select Engineer</option>
                              { users.map(u=><option key={u.id} value={u.id}>{u.name}</option>)}
                          </select>
                      </td>
                  </tr>                    
                  <tr>
                      <td>Project Plan Update</td>
                      <td>
                          <input disabled={disabled}  type="file" onChange={this.handleFilesSelect}></input>
                      </td>
                  </tr> 
                  <tr>
                      <td>Original Quote Document with final agreed document</td>
                      <td>
                          <input  className="input" disabled={disabled} value={data.originalQuoteDocumentFinalAgreed} onChange={(event)=>this.setValue('originalQuoteDocumentFinalAgreed',event.target.value)}></input>
                      </td>
                  </tr>
                  <tr>
                      <td>Last Update	</td>
                      <td>
                          {this.helper.getLatestUpdate(data.lastUpdate)}
                      </td>
                  </tr> 
                  <tr>
                      <td>
                          Add Update
                      </td>
                      <td>
                          <input style={{width:700}} value={data.newUpdate} onChange={(event)=>this.setValue('newUpdate',event.target.value)}></input>
                      </td>
                  </tr> 
                  <tr>
                      <td>
                          Project Stage
                      </td>
                      <td>
                        <select  className="input" value= {data.projectStageID}  
                        onChange={(event)=>this.handleProjectStage(event.target.value)} 
                        >
                          <option value=''></option>
                          {projectStages.map(s=><option key={s.id} value={s.id} className={this.isProjectStageSelectedBefore(s.id)?'disable':''}>{s.name}</option>)}
                        </select>                          
                      </td>
                  </tr> 
              </tbody>
          </table>
          {mode=='edit'?this.getOtherInfoElement():null}
          <div style={{display:'block'}}>
            {mode=='edit'?
              <button onClick={()=>this.handleUpdate()}>Update</button>:null
            }
            {
              mode=='add'?
              <button onClick={()=>this.handleAdd()}>Add</button>:null
            }
            <button onClick={()=>window.location="Projects.php"}>Cancel</button>

        </div>
      </div>;  
  }
  handleProjectStage=async(projectStageID)=>{
    if(!this.isProjectStageSelectedBefore(projectStageID))
    {
    const {data}=this.state;
    const confirm=await this.confirm("Are you sure you want to change the status?");
    if(confirm)
    {      
      if(data.originProjectStageID==null)
        data.originProjectStageID=data.projectStageID;
      data.projectStageID=projectStageID;
      this.setState({data});
    }
  }
  }
  handleSummaryChange=(event)=>{
    if(event)
    this.setValue('notes', event.editor.getData())
  }
  handleCustomerChange=(customer)=>{    
    const {data}=this.state;
    data.customerID=customer.id;
    this.setState({data});
  }
  handleFilesSelect=($event)=>{
    //console.log($event.target.files);
    const files=[...$event.target.files];
    this.setState({files});
  }
  getActiveTab=()=>{
      const {activeTab,currentUser}=this.state;       
      switch (activeTab) {
          case this.TAB_DETAILS :
            return this.getProjectDetailsElement();          
          case this.TAB_TIME_BREAKDOWN :
            return this.getBudgetElement();  
          case this.TAB_PROJECT_ISSUES:
            return <ProjectIssuesComponent currentUser={currentUser} projectID={this.props.projectID}></ProjectIssuesComponent>
          case this.TAB_PROJECT_SUMMARY:
            return <ProjectSummaryComponent currentUser={currentUser} projectID={this.props.projectID}></ProjectSummaryComponent>
          case this.TAB_PROJECT_STAGES:
            return <ProjectStagesHistoryComponent projectID={this.props.projectID}></ProjectStagesHistoryComponent>
          default:
              return null;
      }
 }

 getOtherInfoElement=()=>{
     let { data ,disabled,mode} = this.state;
     
    if(!data.calculatedBudget)
    disabled='disabled';
     return (
       <div style={{ display: "inline-block" }}>
         <div
           className="flex-row mb-3"
           style={{ justifyContent: "space-between" }}
         >
             <div className="card  mr-5">
             <div className="card-header">Allocate Budget</div>
             <div className="card-body">
               <table className="table table-striped">
                 <thead>
                   <tr>
                     <th></th>
                     <th>Grant Extra Time </th>
                     <th>Type</th>
                   </tr>
                 </thead>
                 <tbody>
                   <tr>
                     <td>In Hours</td>
                     <td>
                       <input
                        disabled={disabled} 
                         type="number"
                         value={data.inHoursQuantity}
                         onChange={(event) =>
                           this.setValue("inHoursQuantity", event.target.value)
                         }
                       ></input>
                     </td>
                     <td>
                       <select
                        disabled={disabled} 
                         value={data.inHoursMeasure}
                         onChange={(event) =>
                           this.setValue("inHoursMeasure", event.target.value)
                         }
                       >
                         <option value="d">Days</option>
                         <option value="h">Hours</option>
                       </select>
                     </td>
                   </tr>
                   <tr>
                     <td>Out of Hours</td>
                     <td>
                       <input
                        disabled={disabled} 
                         type="number"
                         value={data.outOfHoursQuantity}
                         onChange={(event) =>
                           this.setValue("outOfHoursQuantity", event.target.value)
                         }
                       ></input>
                     </td>
                     <td>
                       <select
                        disabled={disabled} 
                         value={data.outOfHoursMeasure}
                         onChange={(event) =>
                           this.setValue(
                             "outOfHoursMeasure",
                             event.target.value
                           )
                         }
                       >
                         <option value="d">Days</option>
                         <option value="h">Hours</option>
                       </select>
                     </td>
                   </tr>
                 </tbody>
               </table>
             </div>
           </div>
           <div className="card ml-5 ">
             <div className="card-header">Project Budget</div>
             <div className="card-body">
               <table className="table table-striped" style={{ width: 300 }}>
                 <thead>
                   <tr>
                     <th></th>
                     <th>Allocated</th>
                     <th>Used To Date</th>
                   </tr>
                 </thead>
                 <tbody>
                   <tr>
                     <td>In Hours</td>
                     <td>{data.inHoursBudget} days</td>
                     <td
                       className={this.helper.getRedClass(
                         data.inHoursUsed,
                         data.inHoursBudget
                       )}
                     >
                       {data.inHoursUsed} days
                     </td>
                   </tr>
                   <tr>
                     <td>Out of Hours</td>
                     <td>{data.outHoursBudget} days</td>
                     <td
                       className={this.helper.getRedClass(
                         data.outHoursUsed,
                         data.outHoursBudget
                       )}
                     >
                       {data.outHoursUsed} days
                     </td>
                   </tr>
                 </tbody>
               </table>
             </div>
           </div>           
         </div>         
       </div>
     );
 }
 getBudgetElement=()=>{
     const {budgetData}=this.state;
     let cData=groupBy(budgetData.data,'caa_consno');
     let gActivity=groupBy(budgetData.data,'cat_desc');     
     return <div>
         <label>(Hours)</label>
     <table className="table table-striped" style={{width:750}}>
         <thead>
             <tr>
                 <th width={150}>Activity</th>
                 {cData.map(c=><th key={c.groupName}>{this.getConsName(c.groupName)}</th>)}
                 <th>Total</th>
             </tr>
         </thead>
         <tbody>
             {gActivity.map((a,i)=><tr key={i}>
                 <td>{a.groupName}</td>
                 {cData.map((c,k)=><td key={k}>{this.getConsValue(a.groupName,c.groupName)}</td>)}
                 <th>{this.getActivityTotal(a.groupName)}</th>
             </tr>)}
             <br></br>
             <tr>
                 <td>In hours Total</td>
                 {cData.map((c,k)=><td key={k}>{this.getConsInHoursTotal(c.groupName)}</td>)}
             </tr>
             <tr>
                 <td>Out of hours Total</td>
                 {cData.map((c,k)=><td key={k}>{this.getConsOutHoursTotal(c.groupName)}</td>)}
             </tr>
             
             <br></br>
             <tr>
                 <td>Chargeable Total (in hours)</td>
                 <td>{this.getChargableInHoursTotal()}</td>
             </tr>
             <tr>
                 <td>Chargeable Total (out of hours)</td>
                 <td>{this.getChargableOutHoursTotal()}</td>
             </tr>
             <br></br>
             <tr>
                 <td>Grand Total (in hours)		</td>
                 <td>{this.getinHoursQuantityTotal()}</td>
             </tr>
             <tr>
                 <td>Grand Total (out hours)		</td>
                 <td>{this.getoutOfHoursQuantityTotal()}</td>
             </tr>
             <br></br>
             <tr>
                 <td>Expenses Total:</td>
                 <td>{budgetData.stats.expenses}</td>
             </tr>             
         </tbody>
     </table>
     </div>
 }
 getConsName=(consId)=>{
    const {users}=this.state;
    const user=users.find(u=>u.id==consId);
    return user&&(user.name.split(" ")[0][0]+user.name.split(" ")[1][0]);
 }
 getConsValue=(activity,custId)=>{
    const {budgetData}=this.state;
    const obj=budgetData.data.find(b=>b.cat_desc==activity&&b.caa_consno==custId);        
    return obj&&obj.inHours||"0.00";
 }
 getActivityTotal=(activity)=>{
    const {budgetData}=this.state;
    const obj=budgetData.data.filter(b=>b.cat_desc==activity)
    .map(a=>parseFloat(a.inHours))
    .reduce((prev,curr)=>prev+curr,0)
    .toFixed(2);
    return obj||0;
 }
 getConsInHoursTotal=(cust)=>{
    const {budgetData}=this.state;
    const obj=budgetData.data.filter(b=>b.caa_consno==cust)
    .map(a=>parseFloat(a.inHours))
    .reduce((prev,curr)=>prev+curr,0)
    .toFixed(2);    
    return obj||0;
 }
 getConsOutHoursTotal=(cust)=>{
    const {budgetData}=this.state;
    const obj=budgetData.data.filter(b=>b.caa_consno==cust)
    .map(a=>parseFloat(a.outHours))
    .reduce((prev,curr)=>prev+curr,0)
    .toFixed(2);    
    return obj||0;
 }
 getinHoursQuantityTotal=()=>{
    const {budgetData}=this.state;
    const obj=budgetData.data
    .map(a=>parseFloat(a.inHours))
    .reduce((prev,curr)=>prev+curr,0)
    .toFixed(2);
    return obj||0;
 }
 getoutOfHoursQuantityTotal=()=>{
    const {budgetData}=this.state;
    const obj=budgetData.data
    .map(a=>parseFloat(a.outHours))
    .reduce((prev,curr)=>prev+curr,0)
    .toFixed(2);    
    return obj||0;
 }
 getChargableInHoursTotal=()=>{
    const {budgetData}=this.state;
    const obj=budgetData.data.filter(d=>d.caa_callacttypeno=="4"||d.caa_callacttypeno=="8")
    .map(a=>parseFloat(a.inHours))
    .reduce((prev,curr)=>prev+curr,0)
    .toFixed(2);
    return obj||0;  
 }
 getChargableOutHoursTotal=()=>{
    const {budgetData}=this.state;
    const obj=budgetData.data.filter(d=>d.caa_callacttypeno=="4"||d.caa_callacttypeno=="8")
    .map(a=>parseFloat(a.outHours))
    .reduce((prev,curr)=>prev+curr,0)
    .toFixed(2);
    return obj||0;    
 }
 isDataValid=()=>{
   const {data}=this.state;   
   if(!data.customerID)
   {
     this.alert("Please select Customer");
     return false;
   }
   
   if(!data.description)
   {
     this.alert("Please enter project description");
     return false;
   }
   
   if(!data.openedDate)
   {
     this.alert("Please enter project open date");
     return false;
   }
   if(!data.projectEngineer)
   {
     this.alert("Please select project engineer");
     return false;
   }
   return true;
 }
handleUpdate=async()=>{
    const { data, files } = this.state;
    if (this.isDataValid()) {
      await this.api.updateProject(data);
      data.originProjectStageID=null;
      if (files.length > 0) {
        const overrite = await this.confirm(
          "The previous project plan file will be overwritten, are you sure?"
        );
        if (overrite) await this.api.uploadProjectFiles(data.projectID, files);
      }
      this.setState({data});
     // document.location = "Projects.php";
    }
    //this.getData();    
}
handleAdd=async()=>{
  const { data, files } = this.state;
  if (this.isDataValid()) {
    const ret = await this.api.addProject(data);
    if (files.length > 0 && ret.projectID) {
      let overwrite = true;
      if (data.hasProjectPlan) {
        overwrite = await this.confirm(
          "The previous project plan file will be overwritten, are you sure?"
        );
      }
      if (overwrite) await this.api.uploadProjectFiles(ret.projectID, files);
    }
    document.location = `Projects.php`;
  }
  //this.getData();    
}
getActionsElement=()=>{
  const {data,currentUser}=this.state;
  //console.log('calc',!data.calculatedBudget&&currentUser?.isProjectManager);
  return (
    <div className="flex-row" style={{ alignItems: "center" }}>
      <ToolTip title="Customer" width={30}>
        <a
          href={`Customer.php?customerID=${data.customerID}&action=dispEdit`}
          target="_blank"
        >
          <i className="fal fa-building fa-2x icon pointer"></i>
        </a>
      </ToolTip>
      {data.ordHeadID ? (
        <ToolTip title="Unlink Sales Order" width={30}>
          <i
            className="fal fa-unlink fa-2x m-5 pointer icon"
            onClick={this.handleUnlinkSalesOrder}
          ></i>
        </ToolTip>
      ) : null}
      {data.ordHeadID ?  <ToolTip title="Sales Order" width={30}>
        <a href={`SalesOrder.php?action=displaySalesOrder&ordheadID=${data.ordHeadID}`}   target="_blank">
          <i
            className="fal fa-tag fa-2x m-5 pointer icon"            
          ></i>
          </a>
        </ToolTip>:null
      }
      {!data.ordHeadID ? 
            <ToolTip title="Sales Order" width={30}>
              <i
                className="fal fa-tag fa-2x m-5 pointer icon"
                onClick={()=>this.handleSalesOrder(false)}
              ></i>
            </ToolTip>:null
      }

      <div style={{width:15}}></div>
{data.ordOriginalHeadID ? (
        <ToolTip title="Unlink Sales Order" width={30}>
          <i
            className="fa fa-unlink fa-2x m-5 pointer icon"
            onClick={()=>this.handleUnlinkSalesOrder(true)}
          ></i>
        </ToolTip>
      ) : null}
      {data.ordOriginalHeadID ?  <ToolTip title="Sales Order" width={30}>
        <a href={`SalesOrder.php?action=displaySalesOrder&ordheadID=${data.ordOriginalHeadID}`}   target="_blank">
          <i
            className="fa fa-tag fa-2x m-5 pointer icon"            
          ></i>
          </a>
        </ToolTip>:null
      }
      {!data.ordOriginalHeadID ? 
            <ToolTip title="Original Sales Order" width={30}>
              <i
                className="fa fa-tag fa-2x m-5 pointer icon"
                onClick={()=>this.handleSalesOrder(true)}
              ></i>
            </ToolTip>:null
      }


      {data.ordHeadID ? (
        <ToolTip title="SR" width={30}>
          <a href={`Activity.php?action=search&linkedSalesOrderID=${data.ordHeadID}`} target="_blank">
            <i className="fal fa-hashtag fa-2x m-5 pointer icon"></i>
          </a>
          
        </ToolTip>
      ) : null}
      {(data.ordHeadID&&!data.calculatedBudget&&currentUser?.isProjectManager) ? (
        <ToolTip title="Calculate Budget" width={30}>
          <i className="fal fa-calculator fa-2x m-5 pointer icon" onClick={this.handlecalculateBudget}></i>
        </ToolTip>
      ) : null}
      {data.hasProjectPlan?
      <ToolTip title="Project Plan" width={30}>
        <a  href={`/Projects.php?action=projectFiles&projectID=${data.projectID}`}>
          <i className="fal fa-chart-pie-alt fa-2x m-5 pointer icon"></i>
        </a>        
      </ToolTip>:null
      }
      { !this.isEmpty(data.originalQuoteDocumentFinalAgreed)?
      <ToolTip title="Original Quote Document with final agreed document" width={30}>
        <a  href={data.originalQuoteDocumentFinalAgreed} target="_blank">
          <i className="fa fa-chart-pie-alt fa-2x m-5 pointer icon"></i>
        </a>        
      </ToolTip>:null
      }
    </div>
  );
}
handlecalculateBudget=()=>{
  const {data}=this.state;
  this.api.calculateBudget(data.projectID).then(result=>{
    //console.log(result);
    this.getData();
  }).catch(ex=>{
    //console.log(ex);
    this.alert(ex);
  });
  
}
handleUnlinkSalesOrder=async (originalOrder=false)=>{
  const {data}=this.state; 
  await this.api.unlinkSalesOrder(data.projectID,originalOrder);
  this.getData();
}

handleSalesOrder=(originalOrder=false)=>{
  this.setState({showSalesOrder:true,originalOrder})
}
getSalesOrderModal=()=>{
  const {showSalesOrder,newOrdHeadID,originalOrder}=this.state;
  ////console.log(showSalesOrder);
  let title="Linked to Sales Order";
  if(originalOrder)
  title="Linked to original Sales Order";
  return (
    <Modal
      key="orderModal"
      title={title}
      onClose={() => this.setState({ showSalesOrder: false })}
      width={400}
      show={showSalesOrder}
      content={
        <div key="content">
          <div className="flex-row">
            <label>Order Number </label>
            <input  type="number" value={newOrdHeadID} onChange={(event)=>this.setState({newOrdHeadID:event.target.value})}></input>
          </div>
        </div>
      }
      footer={
        <div key="footer">
          <button   onClick={this.handleUpdateSalesOrder}>Update</button>
          <button  onClick={() => this.setState({ showSalesOrder: false })}>Cancel</button>
        </div>
      }
    ></Modal>
  );
}
handleUpdateSalesOrder=()=>{
  const {newOrdHeadID,data,originalOrder}=this.state;
  if(newOrdHeadID=='')
  {
    this.alert("Please Enter Order Number");
    return;
  }
  this.api.linkSalesOrder(data.projectID,newOrdHeadID,originalOrder).then(result=>{
    //console.log(result);
    if(result.status)
    {
      if(!originalOrder)      
        data.ordHeadID=newOrdHeadID;
      else 
        data.ordOriginalHeadID=newOrdHeadID;
      this.setState({newOrdHeadID:'',data,showSalesOrder: false});
    }
    else {
      this.alert(result.error);
    }
  }).catch(ex=>{
    console.log(ex);
    this.alert("Failed to save order");
  });

}
isProjectStageSelectedBefore=(stageID)=>{
  const {projectStagesHistory}=this.state;
  return projectStagesHistory.map(h=>h.id).indexOf(stageID)>=0;
}
  render() {  
    const {mode}  =this.state;
    return <div>
        <Spinner  key="spinner" show= {this.state.showSpinner}></Spinner>
        {this.getAlert()}
        {this.getConfirm()}
        {this.getSalesOrderModal()}
        {mode=='edit'?this.getActionsElement():null}
        {mode=='edit'?this.getTabsElement():null}
        {this.getActiveTab()}
      
    </div>    
  }
}

  