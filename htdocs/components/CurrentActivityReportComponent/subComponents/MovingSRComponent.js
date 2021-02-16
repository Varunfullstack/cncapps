import APICustomers from "../../services/APICustomers";
import AutoComplete from "../../shared/AutoComplete/autoComplete.js";
import Table from "./../../shared/table/table";
import CurrentActivityService from "../services/CurrentActivityService";
import React from 'react';
import {ColumnRenderer} from "./ColumnRenderer";
import Modal from "../../shared/Modal/modal";
import CNCCKEditor from "../../shared/CNCCKEditor";
import APIActivity from "../../services/APIActivity";
import APIStandardText from "../../services/APIStandardText";
import MainComponent from "../../shared/MainComponent";

class MovingSRComponent extends MainComponent {
    apiStandardText = new APIStandardText();
    apiActivity= new APIActivity();
    apiCurrentActivityService = new CurrentActivityService();
    constructor(props) {
        super(props);
         this.state = {
            ...this.state,
            show:false,
            changeQueuData:{
                priorityTemplateText:"",
                priorities:[],
                priorityReasons:[],
                priorityId:'',
                priorityTemplate:'',
                movingSrReason:"",
                problem:null,
                newTeam:'',
                queue:''
            }
        }
    }

    componentDidMount=async ()=> {
        const {changeQueuData}=this.state;
        const priorityReasons=await this.apiStandardText.getOptionsByType("Priority Change Reason");
        const priorities = await this.apiActivity.getPriorities();
        changeQueuData.priorities=priorities;
        changeQueuData.priorityReasons=priorityReasons;
        this.setState({changeQueuData});
    }
    static getDerivedStateFromProps(props, current_state) {     
        //console.log('props change');   
        if(props.show!=current_state.show)
        {
            current_state.changeQueuData.problem=props.problem;
            current_state.changeQueuData.queue=props.code;
            current_state.changeQueuData.newTeam=props.newTeam;
            current_state.show=props.show;        
            current_state.changeQueuData.priorityTemplateText="";
            current_state.changeQueuData.priorityTemplate="";
            current_state.changeQueuData.priorityId=props.problem.priority;
            current_state.changeQueuData.movingSrReason="";
        }
        return current_state;
    }
    setChangeQueueData=(field,value)=>{
        const {changeQueuData}=this.state;
        changeQueuData[field]=value;
        this.setState({changeQueuData});
    }
    getAssignTeamModal=()=>{        
        const {changeQueuData,show}=this.state;
        if(!show)
            return null;        
        return <Modal key="modal"
        onClose={this.handleCancel}
        width={650}
        show={show}
        title="Change queue / priority"
        content={
        <div key="content">
            <div className="form-group">
                <label>Reason for moving this SR to another queue</label>
                <textarea style={{border:"1px solid white",minHeight:50,backgroundColor: "transparent", color: "white", fontSize: 15}}  onChange={(event)=>this.setChangeQueueData("movingSrReason",event.target.value)}></textarea>                
            </div>
            <div className="form-group">
                <label>Priority</label>
                <select style={{width:360}} value={changeQueuData.priorityId} onChange={(event)=>this.setChangeQueueData("priorityId",event.target.value)}>
                    <option></option>
                    {changeQueuData.priorities.map(r=><option key={r.id} value={r.id}>{r.name}</option>)}
                </select>
            </div>
            <div className="form-group">
                <label>Priority change template</label>
                <select style={{width:360}} value={changeQueuData.priorityTemplate?.id} onChange={(event)=>this.handleTemplateChange(event.target.value)}>
                    <option></option>
                    {changeQueuData.priorityReasons.map(r=><option key={r.id} value={r.id}>{r.name}</option>)}
                </select>
            </div>
            <div className="form-group">
                <label>Reason for changing this SR priority</label>
                <CNCCKEditor value={changeQueuData.priorityTemplateText} type="inline"  style={{border:"1px solid white",minHeight:50}} onChange={(text)=>this.setChangeQueueData("priorityTemplateText",text)}></CNCCKEditor>
            </div>
        </div>
        }
        footer={<div key="footer">
            <button onClick={()=>this.handleSaveMovingSR()}>Save</button>
            <button onClick={()=>this.handleCancel()}>Cancel</button>
        </div>}
        >

        </Modal>
    }
    handleSaveMovingSR=()=>{
        const {changeQueuData}=this.state;        
        let queueChanged=false,priorityChange=false;
        const callApis=[];
        console.log(changeQueuData.problem.status ,changeQueuData.movingSrReason=="");     
        if(changeQueuData.problem.status=="P"&&changeQueuData.movingSrReason=="")
        { 
            this.alert("A reason for moving queues is required because this request has been started");
            return;
        } else if (changeQueuData.problem.status=="I"||changeQueuData.movingSrReason!=""){
            queueChanged=true;
        }   
        
        if(changeQueuData.priorityId!=changeQueuData.problem.priority && changeQueuData.priorityTemplateText=="")
        {
            this.alert("A reason for changing the priority is required");
            return;
        } else if (changeQueuData.priorityId!=changeQueuData.problem.priority && changeQueuData.priorityTemplateText!=""){
            priorityChange=true;
        }

        //console.log(changeQueuData);
        if(queueChanged)
            callApis.push(this.apiCurrentActivityService
            .changeQueue(changeQueuData.problem.problemID, changeQueuData.newTeam, changeQueuData.movingSrReason)
            );

        if(priorityChange)
        {
            //update priority
            const payload = {
                callActivityID: parseInt(changeQueuData.problem.callActivityID),
                priorityChangeReason: changeQueuData.priorityTemplateText,
                priority: parseInt(changeQueuData.priorityId)
            }
            //console.log(payload);
            callApis.push(this.apiActivity.changeProblemPriority(payload));
            
        }
        Promise.all(callApis).then(([changeQueue,changePriority])=>{
            this.setState({show:false});
            if(this.props.onClose)
                this.props.onClose();
        });
    }
    handleCancel=()=>{
        this.setState({show:false});
        if(this.props.onClose)
            this.props.onClose(false);
    }
    handleTemplateChange=(templateId)=>{
        const {changeQueuData}=this.state;        
        const priorityTemplate=changeQueuData.priorityReasons.find(p=>p.id==templateId);
        changeQueuData.priorityTemplate=priorityTemplate;
        changeQueuData.priorityTemplateText=priorityTemplate.template;
        this.setState({changeQueuData});

        //this.setState({priorityTemplate,priorityTemplateText:priorityTemplate.template});
    }    
    
    render() {
        return <div>            
            {this.getAlert()}      
            {  this.getAssignTeamModal()}
        </div>      
    }
}

export default MovingSRComponent;
