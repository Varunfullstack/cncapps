import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './LeadStatusTypesComponent.css';
import APILeadStatusTypes from "./services/APILeadStatusTypes.js";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";

class LeadStatusTypesComponent extends MainComponent {
   api=new APILeadStatusTypes();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            types:[]   ,
            mode:"new"   ,
            data:{
                id:'',
                name:'',
                appearOnScreen:false,
                sortOrder:0
            }
        };
    }

    componentDidMount() {      
        this.getData();
    }

    getData=()=>{
        this.api.getAllTypes().then(types=>{
            this.setState({types});
        });
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "name",
               label: "",
               hdToolTip: "Name",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-text color-gray2 pointer",
               sortable: true,
               //className: "text-center",                
            },
            {
                path: "appearOnScreen",
                label: "",
                hdToolTip: "Appear On Lead Status Screen",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.appearOnScreen?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
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
                content:(type)=> <i className="fal fa-2x fa-edit color-gray pointer" onClick={()=>this.showEditModal(type)}></i>,
             
             },
             {
                path: "trash",
                label: "",
                hdToolTip: "Delete",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=> <i className="fal fa-2x fa-trash-alt color-gray pointer" onClick={()=>this.handleDelete(type)}></i>,
             
             }
        ];
    
        return <Table           
        style={{width:500,marginTop:20}}
        onOrderChange={this.handleOrderChange} 
        allowRowOrder={true}
        key="leadStatus"
        pk="id"
        columns={columns}
        data={this.state.types||[]}
        search={true}
        >
        </Table>
    }
    showEditModal=(data)=>{
        this.setState({showModal:true,data,mode:'edit'});
    }
    handleDelete=async (type)=>{
        const conf=await this.confirm("Are you sure to delete this type?")
        if(conf)
        this.api.deleteType(type.id).then(res=>{
            if(res.state)
            this.getData();
            else this.alert(res.error);
        }

        )
    }
    handleOrderChange=async (current,next)=>{
        const {types}=this.state;
        if(next)
        {
            current.sortOrder=next.sortOrder;
            next.sortOrder=current.sortOrder+0.001;
            await this.api.updateType(next);
        }
        if(!next)
        {        
            current.sortOrder=Math.max(...types.map(i=>i.sortOrder))+0.001;
        }     

        await this.api.updateType(current);
        this.getData();
    }
    handleNewType=()=>{
        this.setState({mode:"new",showModal:true, data:{
            id:'',
            name:'',
            appearOnScreen:false,
            sortOrder:0
        }});
    }
    hideModal=()=>{
        this.setState({ showModal:false});
    }
    getModalElement=()=>{
        const {mode,data}=this.state;
        return <Modal 
        width={500}
        show={this.state.showModal}
        title={mode=="new"?"Add New Type":"Edit Type"}
        onClose={this.hideModal}
        content={
            <div key="content">

                <div className="form-group">
                  <label  >Name</label>
                  <input value={data.name} type="text" name="" id="" className="form-control required" onChange={(event)=>this.setValue("name",event.target.value)} />                   
                </div>

                <div className="form-group">
                  <label  >Appear On Lead Status Screen</label>                  
                  <Toggle checked={data.appearOnScreen} onChange={()=>this.setValue("appearOnScreen",!data.appearOnScreen)} ></Toggle>            
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
        if (data.name == "") {
          this.alert("Type name required.");
          return;
        }
        if (mode == "new") {
          this.api.addType(data).then((result) => {
            if (result.state) {
              this.setState({ showModal: false });
              this.getData();
            } else {
              this.alert(result.error);
            }
          });
        }
        else if(mode=='edit')
        {
            this.api.updateType(data).then((result) => {
              if (result.state) {
                this.setState({ showModal: false });
                this.getData();
              } else {
                this.alert(result.error);
              }
            });
        }
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

export default LeadStatusTypesComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactLeadStatusTypesComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(LeadStatusTypesComponent), domContainer);
});