import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import APITeam from "./services/APITeam.js";
import '../style.css';
import './TeamComponent.css';
import APIUser from "../services/APIUser.js";
import Toggle from "../shared/Toggle.js";

class TeamComponent extends MainComponent {
   api=new APITeam();
   apiUsers=new APIUser();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            teams:[]   ,
            mode:"new"   ,
            data:{
                teamID:'',
                name:'',     
                teamRoleName:''            ,
                level:'',
                activeFlag:'Y',
                leaderName:'',
                canDelete:false,
                teamRoleID:'',
                leaderId:'',
                
            },
            roles:[],
            users:[]
        };
    }

    componentDidMount() {  
        console.log("start");    
        this.getData();
        this.api.getRoles().then(res=>this.setState({roles:res.data}));
        this.apiUsers.getAllUsers().then(users=>this.setState({users}));
    }

    getData=()=>{
        this.setState({showSpinner:true})
        this.api.getAllTeams().then(res=>{
            
            this.setState({teams:res,showSpinner:false});
            console.log(res);
        });
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "name",
               label: "Name",
               hdToolTip: "Name",
               hdClassName: "text-center",
               //icon: "fal fa-2x fa-text color-gray2 pointer",
               sortable: true,
               //className: "text-center",                
            },  
            {
                path: "teamRoleName",
                label: "Role",
                hdToolTip: "Role",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-text color-gray2 pointer",
                sortable: true,
                //className: "text-center",                
             },  
             {
                path: "leaderName",
                label: "Leader",
                hdToolTip: "Leader",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-text color-gray2 pointer",
                sortable: true,
                //className: "text-center",                
             },   
             {
                path: "level",
                label: "Level",
                hdToolTip: "Level",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-text color-gray2 pointer",
                sortable: true,
                //className: "text-center",                
             },  
             {
                path: "activeFlag",
                label: "Active",
                hdToolTip: "Active",
                hdClassName: "text-center",                
                sortable: true,
                content:(type)=>this.getTrueFalseElement(type.activeFlag=='Y'),
                className: "text-center",  
                          
             },           
            {
                path: "edit",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=>this.getEditElement(type,this.showEditModal),             
             },
             {
                path: "trash",
                label: "",
                hdToolTip: "Delete",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=>this.getDeleteElement(type,this.handleDelete,type.canDelete),             
             }
        ];
    
        return <Table           
        style={{width:700,marginTop:20}}
        key="leadStatus"
        pk="teamID"
        columns={columns}
        data={this.state.teams||[]}
        search={true}
        >
        </Table>
    }
    showEditModal=(data)=>{        
        this.setState({showModal:true,data:{...data},mode:'edit'});
    }
    handleDelete=async (type)=>{
        console.log(type);
        const conf=await this.confirm("Are you sure to delete this team?")
        if(conf)
        this.api.deleteTeam(type.teamID).then(res=>{
            if(res.state)
            this.getData();
            else this.alert(res.error);
        })
    }
     
    handleNewType=()=>{
        this.setState({mode:"new",showModal:true, data:{
            teamID:'',
            name:'',            
        }});
    }
    hideModal=()=>{
        this.setState({ showModal:false});
    }
    getModalElement=()=>{
        const {mode,data,roles,users}=this.state;
        return <Modal 
        width={500}
        show={this.state.showModal}
        title={mode=="new"?"Add New Type":"Edit Type"}
        onClose={this.hideModal}
        content={
            <div key="content">

                <div className="form-group">
                  <label >Name</label>
                  <input value={data.name} type="text" name="" id="" className="form-control required" onChange={(event)=>this.setValue("name",event.target.value)} />                   
                </div>
                <div className="form-group">
                  <label >Level</label>
                  <input type="number" value={data.level}   name="" id="" className="form-control required" onChange={(event)=>this.setValue("level",event.target.value)} />                   
                </div>
                <div className="form-group">
                  <label >Role</label>                  
                  <select value={data.teamRoleID} onChange={(event)=>this.setValue("teamRoleID",event.target.value)}>
                    <option></option>
                    {roles.map(r=><option key={r.id} value={r.id}>{r.name}</option>)}
                  </select>
                </div>
                <div className="form-group">
                  <label >Leader</label>                  
                  <select value={data.leaderId} onChange={(event)=>this.setValue("leaderId",event.target.value)}>
                    <option></option>
                    {users.map(r=><option key={r.id} value={r.id}>{r.name}</option>)}
                  </select>
                </div>
                <div className="form-group">
                  <label >Active</label>
                  <Toggle checked={data.activeFlag=='Y'} onChange={(event)=>this.setValue("activeFlag",data.activeFlag=='Y'?'N':'Y')} ></Toggle>
                </div>
            </div>        
        }
        footer={<div key="footer">
                <button onClick={this.handleSave}>Save</button>
                <button onClick={this.hideModal}>Cancel</button>
            </div>}
        >

        </Modal>
    }
    handleSave=()=>{
        const { data, mode } = this.state;
        console.log(data);
        if (data.name == "") {
          this.alert("Type name required.");
          return;
        }        
        if (mode == "new") {
          this.api.addTeam(data).then((result) => {
              console.log(result);
            if (result.state) {
              this.setState({ showModal: false });
             
            } else {
              this.alert(result.error);
            }
            this.getData();
          });
        }
        else if(mode=='edit')
        {
            this.api.updateTeam(data).then((result) => {
                console.log(result);
              if (result.state) {
                this.setState({ showModal: false });              
              } else {
                this.alert(result.error);
              }
              this.getData();
            });
        }
        console.log(data);
    }
    render() {        
        return <div>
            <Spinner show={this.state.showSpinner}></Spinner>
            <ToolTip title="New Type" width={30}>
                <i className="fal fa-2x fa-plus color-gray1 pointer" onClick={this.handleNewType}></i>
            </ToolTip>
            {this.getConfirm()}
            {this.getAlert()}
            {this.getModalElement()}
           {this.getDataTable()}
        </div>;
    }
}

export default TeamComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactTeamComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(TeamComponent), domContainer);
});