
"use strict";
import Spinner from './../utils/spinner.js?v=1';
import Stepper from '../utils/stepper.js?v=1';
import CMPCustomerSearch from './components/CMPCustomerSearch.js?v=1';
import CMPSelectSR from './components/CMPSelectSR.js?v=1';
import CMPCustomerSite from './components/CMPCustomerSite.js?v=1';
import CMPLastStep from './components/CMPLastStep.js?v=1';

export default class CMPLogServiceRequest extends React.Component {
  el = React.createElement;
  steps = [{ id:0, title:"", display:false ,active:false }];
  constructor(props) {
    super(props);  
    this.state={
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
  componentDidMount() {}
   
  setActiveStep=(step)=>{
    const {steps}=this.state;
    const index=steps.map(s=>s.id).findIndex(s=>s===step);
    steps[index].display=true;
    steps[index].active=true;
    steps[index].disabled=false;
    this.setState({steps,activeStep:step});
  }
  updateSRData=(data)=>{
    const newData={...this.state.data,...data};
    this.setActiveStep(newData.nextStep);   
    console.log(newData);
    this.setState({data:newData});
  }
  render() {    
    const { el, getStepper,setActiveStep ,updateSRData } = this;
    let {activeStep,data}=this.state;
    const customer=data.customer;
    //console.log(customer);
    return el("div", {style:{minHeight:"90vh"}}, getStepper(),
    el('div',{style:{marginTop:30}},
      activeStep===1?el(CMPCustomerSearch,{data,updateSRData}):null ,
      activeStep===2?el(CMPSelectSR    ,{data,customerId:customer?.cus_custno,contactId:customer?.con_contno,updateSRData}):null,
      activeStep===3?el(CMPCustomerSite,{data,customerId:customer?.cus_custno,contactId:customer?.con_contno,updateSRData }):null ,
      activeStep===4?el(CMPLastStep    ,{data,customerId:customer?.cus_custno,contactId:customer?.con_contno ,updateSRData}):null ,
    )
    
    );
  }
}

const domContainer = document.querySelector("#reactMainLogServiceRequest");
ReactDOM.render(React.createElement(CMPLogServiceRequest), domContainer);