import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../services/APIProjects';
import Table from "../../shared/table/table";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import CNCCKEditor from "../../shared/CNCCKEditor";
 
export default class ProjectSummaryComponent extends MainComponent { 
  api = new APIProjects(); 
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,            
      data:{
        engineersSummary:'', 
        projectManagersSummary:'',
        projectClosureNotes:'',
        projectClosureDate:'',
      },
     
    };     
  }

  componentDidMount() {
    this.getData();
  }
  getData(){
    this.api.getProjectSummary(this.props.projectID).then(data=>{
      console.log('summary',data);
      this.setState({data,showModal:false});
    })
  }
  getSummaryElement=()=>{
    const {data}=this.state;
    return <div>
        <div className="form-group">
            <label>Engineers Summary</label>
            <CNCCKEditor type="inline" style={{width:800,minHeight:60}} value={data.engineersSummary} onChange={(event)=>this.setTemplateValue('engineersSummary',event)}></CNCCKEditor>
        </div>
        <div className="form-group">
            <label>Project Managers Summary</label>
            <CNCCKEditor  type="inline" style={{width:800,minHeight:60}}  value={data.projectManagersSummary} onChange={(event)=>this.setTemplateValue('projectManagersSummary',event)}></CNCCKEditor>
        </div>
        <div className="form-group">
            <label>Closure Meeting Date</label>
            <input type="date" value={data.projectClosureDate} style={{width:150,margin:0}} onChange={(event)=>this.setValue('projectClosureDate',event.target.value)}></input>
        </div>
        <div className="form-group">
            <label>Project Closure Notes</label>
            <CNCCKEditor  type="inline" style={{width:800,minHeight:60}}  value={data.projectClosureNotes} onChange={(event)=>this.setTemplateValue('projectClosureNotes',event)}></CNCCKEditor>
        </div>
        <button onClick={this.handleSave}>Save</button>
    </div>
  }
  setTemplateValue=(template,event)=>{
    if(event.editor)
    {
        this.setValue(template,event.editor.getData());
    }
  }
  handleSave=()=>{
    const {data}=this.state;
    console.log(data);
    this.setState({showSpinner:true});
    this.api.updateProjectSummary(this.props.projectID, data).then(result=>{
        console.log(result);
        if(result.status)
        setTimeout(()=>this.setState({showSpinner:false}),1000);
    })
  }
  
  render() {  
    const {mode}  =this.state;
    return <div>
        <Spinner  key="spinner" show= {this.state.showSpinner}></Spinner>
        {this.getAlert()}         
        {this.getSummaryElement()}
        
    </div>
  }

}

  