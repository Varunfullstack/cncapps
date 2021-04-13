import React from 'react';
import APIProjectOptions from '../ProjectOptionsComponent/services/APIProjectOptions';
 import APIUser from '../services/APIUser';
import CustomerSearch from '../shared/CustomerSearch';
import MainComponent from "../shared/MainComponent";
import {  sort } from '../utils/utils';
import './../style.css';
import './ReportsComponent.css';
import APIReports from './services/APIReports';
import RepCallbackSearch from './subComponents/RepCallbackSearch';
import RepProjects from './subComponents/RepProjects';
import RepProjectsByConsultant from './subComponents/RepProjectsByConsultant';
import RepProjectsByConsultantInProgress from './subComponents/RepProjectsByConsultantInProgress';
import RepProjectsByCustomerStageFallsStartEnd from './subComponents/RepProjectsByCustomerStageFallsStartEnd';
import RepProjectsWithoutClousureMeeting from './subComponents/RepProjectsWithoutClousureMeeting';
//categoryID=1&&hideCategories=true
class AppReport extends MainComponent {  
  api=new APIReports();
  apiUsers=new APIUser();
  apiProjectOptions=new APIProjectOptions();
  components={};
  constructor(props) {
    super(props);
    this.state = {
        ...this.state,
        categories:[],
        reports:[],
        currentCategoryID:'',
        currentReportID:'',
        paramters:[],
        compParamters:[],
        data:{},
        consultants:[],
        hideCategories:false,
        projectStages:[],
        projectTypes:[]
    };    
    this.components = {
      RepProjectsByConsultant:RepProjectsByConsultant,
      RepProjectsByConsultantInProgress:RepProjectsByConsultantInProgress,
      RepProjectsByCustomerStageFallsStartEnd:RepProjectsByCustomerStageFallsStartEnd,
      RepProjects:RepProjects,
      RepProjectsWithoutClousureMeeting:RepProjectsWithoutClousureMeeting,
      RepCallbackSearch:RepCallbackSearch
  };
  }

  componentDidMount() { 
    const currentCategoryID=this.props.categoryID;
    const hideCategories=this.props.hideCategories;
    if(currentCategoryID)
    {
        this.handleCategoryChange(currentCategoryID);      

    }     
    this.setState({hideCategories});
    if(!hideCategories)
    this.getReportCategories();
     
  }
  getReportCategories=()=>{
    this.api.getReportCategoriesActive().then(categories=>{
       this.setState({categories});
   });   
  }
  loadConsultants=()=>{
    this.apiUsers.getActiveUsers().then(consultants=>{
      this.setState({consultants})
    });
  }
  getCategoriesElement=()=>{
    const {categories,currentCategoryID,hideCategories}=this.state;
    if(hideCategories)
      return null;
    return <tr>
        <td>Report Categories</td>
        <td>
            <select style={{width:300}} value={currentCategoryID} 
            onChange={(event)=>this.handleCategoryChange(event.target.value)}

            >
                <option value="">Select Category</option>
                {categories.map(c=><option key={c.id} value={c.id}>{c.title}</option>)}
            </select>
        </td>
    </tr>
  }
  handleCategoryChange=(categoryID)=>{
    this.api.getCategoryReports(categoryID).then(reports=>{
      this.setState({currentCategoryID:categoryID,reports});

    })
  }
  getReportsElement=()=>{
    const {reports,currentReportID}=this.state;
    return <tr>
        <td>Reports</td>
        <td>
            <select style={{width:300}} value={currentReportID} 
            onChange={(event)=>this.handleReportChange(event.target.value)}

            >
                <option value="">Select Report</option>
                {reports.map(c=><option key={c.id} value={c.id}>{c.title}</option>)}
            </select>
        </td>
    </tr>
  }
  loadParamtersData=(paramters)=>{
    for (let i = 0; i < paramters.length; i++) {
      switch (paramters[i].name) {
        case "consID":
          this.loadConsultants();
          break;
        case "projectStageID":
          this.loadProjectStages();
          break;
        case "projectTypeID":
          this.loadProjectTypes();
          break;
        default:
          break;
      }
    }
  }
  loadProjectStages=()=>{
    this.apiProjectOptions.getProjectStages().then(projectStages=>{
      this.setState({projectStages});
    })
  }
  loadProjectTypes=()=>{
    this.apiProjectOptions.getProjectTypes().then(projectTypes=>{
      this.setState({projectTypes});
    })
  }
  handleReportChange=(reportID)=>{
    if(reportID)
    this.api.getReportParamters(reportID)
    .then(paramters=>{
      if(paramters.length>0){
        paramters=  paramters.map(p=>{p.value=''; return p;})  ;
        this.loadParamtersData(paramters);
        paramters=sort(paramters,'paramterOrder');
        this.setState({paramters});
      }
     
    });
    this.setState({currentReportID:reportID,paramters:[]});
  }
  getParamtersElement=()=>{
    const {paramters}=this.state;
    if(paramters.length==0)
    return null;
    return paramters.map(p=><tr key={p.id}>
      <td>{p.title||p.defaultTitle}</td>
      <td>{this.getParamter(p)}</td>
    </tr>)
  }
  setParamterValue=(paramter,value)=>{
    const {paramters}=this.state;
    const indx=paramters.map(p=>p.id).indexOf(paramter.id);
    if(indx>=0)
    {
      paramters[indx].value=value;
    };
    this.setState({paramters});
  }
  getParamterValue=(paramter)=>{
    const {paramters}=this.state;
    const indx=paramters.map(p=>p.id).indexOf(paramter.id);
    if(indx>=0)
    {
      return paramters[indx].value;
    };
    return '';
  }
  getParamter=(paramter)=>{
    const {consultants,projectStages,projectTypes}=this.state;
    switch (paramter.name) {
      case 'consID':
        return <select   required={paramter.required} value={paramter.value} onChange={(event)=>this.setParamterValue(paramter,event.target.value)} >
          <option value=""></option>
          {consultants.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}
        </select>;
      case 'projectStageID':
        return <select   required={paramter.required} value={paramter.value} onChange={(event)=>this.setParamterValue(paramter,event.target.value)} >
          <option value=""></option>
          {projectStages.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}
        </select>;
      case 'projectTypeID':
        return <select   required={paramter.required} value={paramter.value} onChange={(event)=>this.setParamterValue(paramter,event.target.value)} >
          <option value=""></option>
          {projectTypes.map(c=><option key={c.id} value={c.id}>{c.name}</option>)}
        </select>;
      case 'dateFrom':
        return <input type="date" required={paramter.required} value={paramter.value} onChange={(event)=>this.setParamterValue(paramter,event.target.value)} ></input>;       
      case 'dateTo':
        return <input type="date"  required={paramter.required} value={ paramter.value} onChange={(event)=>this.setParamterValue(paramter,event.target.value)} ></input>;       
      case 'customerID':
        return <CustomerSearch onChange={(customer)=>this.setParamterValue(paramter,customer.id)}></CustomerSearch>
        case 'callbackStatus':
        return <select required={paramter.required} value={paramter.value} onChange={(event)=>this.setParamterValue(paramter,event.target.value)} >
          <option value="">All</option>
          <option value="awaiting">Awaiting</option>
          <option value="contacted">Contacted</option>
          <option value="canceled">Canceled</option>
        </select>
      default:
        break;
    }
  }
  isParamtersValid=()=>{
    const {paramters}=this.state;
    for(let i=0;i<paramters.length;i++){
      if(paramters[i].required&&paramters[i].value=='')
      {
        return false;
      }
    }    
    return true;
  }
  handleGo=()=>{
    const {paramters,currentReportID}=this.state;
    if(!currentReportID)
    {
      this.alert("Please select the report");
      return;
    }
    if(!this.isParamtersValid())
    {
      this.alert("Please enter required fields");
      return;
    }
    const compParamters={};
    for(let i=0;i<paramters.length;i++)
    {
      compParamters[paramters[i].name]=paramters[i].value;
    }
    this.setState({compParamters});
 
  }
  getCurrentReportComponent=()=>{
    const {currentReportID,reports,compParamters}=this.state;
     if(currentReportID=='')
    return null;
    const report=reports.find(r=>r.id==currentReportID);
    const RepComponent= this.components[report.component];
    return <RepComponent {...compParamters}></RepComponent>;
  }
  handleClear=()=>{
    const {paramters}=this.state;
    paramters.map(p=>{
      p.value='';
      return p;
    });
    this.setState({paramters});
  }
  render() {
   return<div>
     {this.getAlert()}
    <table>
       <tbody>
           {this.getCategoriesElement()}
           {this.getReportsElement()}
           {this.getParamtersElement()}
           <tr>
             <td>
               <button onClick={this.handleGo}>Go</button>
               <button onClick={this.handleClear}>Clear</button>
             </td>
             <td></td>
           </tr>           
       </tbody>
   </table>
   {this.getCurrentReportComponent()}
   </div>;
  }
}
export default AppReport;

 