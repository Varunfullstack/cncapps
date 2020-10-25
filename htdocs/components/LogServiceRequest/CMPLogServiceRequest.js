
"use strict";
import Spinner from './../utils/spinner.js?v=10';
import Stepper from '../utils/stepper.js?v=10';
import CMPCustomerSearch from './components/CMPCustomerSearch.js?v=10';
import CMPSelectSR from './components/CMPSelectSR.js?v=10';
import CMPCustomerSite from './components/CMPCustomerSite.js?v=10';
import CMPLastStep from './components/CMPLastStep.js?v=10';
import APIActivity from '../services/APIActivity.js?v=10';
import MainComponent from '../CMPMainComponent.js?v=10'
 
export default class CMPLogServiceRequest extends MainComponent{
  el = React.createElement;
  steps = [{ id:0, title:"", display:false ,active:false }];
  api =new APIActivity();
  constructor(props) {
    super(props);  
    this.state={
      ...this.state,
        projects:[],
        _showSpinner:false,
        steps:  this.initSteps(),
        activeStep:1,
        customer:null,
        data:{
          nextStep:1,
          customer:null
        }
    }
  }
  initSteps = () => {
    this.steps = [
      { id: 1, title: "Select Customer", display: true ,active:true,disabled:false },
      { id: 2, title: "Select Service Request", display: true ,active:false,disabled:true},
      { id: 3, title: "What is the problem?", display: true ,active:false,disabled:true},
      { id: 4, title: "Finish", display: true ,active:false,disabled:true},
      
    ];
    return this.steps ;
  };
  handleStepChange=(step)=>{
    console.log("step",step);
    this.setState({activeStep:step.id})
    let {steps}=this.state;
    steps=steps.map(s=>{
            s.active=false;
        if(s.id<=step.id)
            s.active=true;
        return s
    });
    this.setState({steps});
  }
  getStepper = () => {
    const { el,handleStepChange } = this;
    const {steps}=this.state;
    return el(
      Stepper,{steps:this.steps,onChange:handleStepChange}
    );
  };
  componentDidMount() {
    
    this.api.getCurrentUser().then(user=>{
      const {data}=this.state;
      data.currentUser=user;
      this.setState({data});
      console.log(user);
    });
   }
   
  setActiveStep=(step)=>{
    const {steps}=this.state;
    const index=steps.map(s=>s.id).findIndex(s=>s===step);
    steps[index].display=true;
    steps[index].active=true;
    steps[index].disabled=false;
    this.setState({steps,activeStep:step});
  }
  updateSRData=async(data,save=false)=>{
    const newData={...this.state.data,...data};
    this.setActiveStep(newData.nextStep);   
    console.log(newData);
    this.setState({data:newData});
    if(save)
    {
      const customData={...newData};
      //delete customData.uploadFiles;
      this.setState({_showSpinner:true});
      if(newData.internalNotes.indexOf(newData.internalNotesAppend)==-1)
        newData.internalNotes +=newData.internalNotesAppend;
      newData.callActTypeID=null;
      console.log(newData);
      //return;
      const result=await this.api.createProblem(customData);     
      console.log(result);
      if(result.status)
      { 
         
        if(newData.uploadFiles.length>0)
          await this.api.uploadFiles(
            `Activity.php?action=uploadFile&problemID=${result.problemID}&callActivityID=${result.callActivityID}`,
            newData.uploadFiles,
            "userfile[]"
          );
          this.setState({_showSpinner:false});
          await  this.alert(`Please advise customer their service request number is: ${result.problemID}`)
        if(result.nextURL)          
            window.location=result.nextURL;
      }
      this.setState({_showSpinner:false});
       
    }
  }
  getProjectsElement=()=>{
    const {data}=this.state;
    const {el}=this;
    if(data&&data.projects&&data.projects.length>0)
    {
        return el('div',{style:{display:"flex",flexDirection:"row",alignItems:"center",marginTop:-20} },
        el('h3',{className:"mr-5"},"Projects "),
        data.projects.map(p=>el("a",{key:p.projectID,href:p.editUrl,className:"link-round"},p.description))
        )
    }
    else return null;
  }
  render() {    
    const { el, getStepper,setActiveStep ,updateSRData } = this;
    let {activeStep,data,_showSpinner}=this.state;
    const customer=data.customer;
    //console.log(customer);
    return el("div",null,    
    el(Spinner,{show:_showSpinner}),
    this.getAlert(),
    el("div", {style:{minHeight:"90vh"}}, 
    this.getProjectsElement(),
    getStepper(),    
    el('div',{style:{marginTop:30}},
      activeStep===1?el(CMPCustomerSearch,{data,updateSRData}):null ,
      activeStep===2?el(CMPSelectSR    ,{data,customerId:customer?.cus_custno,contactId:customer?.con_contno,updateSRData}):null,
      activeStep===3?el(CMPCustomerSite,{data,customerId:customer?.cus_custno,contactId:customer?.con_contno,updateSRData }):null ,
      activeStep===4?el(CMPLastStep    ,{data,customerId:customer?.cus_custno,contactId:customer?.con_contno ,updateSRData}):null ,
    )
    )
    );
  }
}

const domContainer = document.querySelector("#reactMainLogServiceRequest");
ReactDOM.render(React.createElement(CMPLogServiceRequest), domContainer);