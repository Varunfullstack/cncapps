import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import APIManufacturer from "./services/APIManufacturer.js";
import '../style.css';
import './ManufacturerComponent.css';

class ManufacturerComponent extends MainComponent {
   api=new APIManufacturer();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            types:[]   ,
            mode:"new"   ,
            data:{
                manufacturerID:'',
                name:'',                 
            }
        };
    }

    componentDidMount() {  
        this.getData();
    }

    getData=()=>{
        this.setState({showSpinner:true})
        this.api.getAllTypes().then(res=>{
            if(res.state)
            this.setState({types:res.data,showSpinner:false});
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
                content:(type)=>type.canDelete? <i className="fal fa-2x fa-trash-alt color-gray pointer" onClick={()=>this.handleDelete(type)}></i>:null,             
             }
        ];
    
        return <Table           
        style={{width:500,marginTop:20}}
        key="leadStatus"
        pk="manufacturerID"
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
        this.api.deleteType(type.manufacturerID).then(res=>{
            if(res.state)
            this.getData();
            else this.alert(res.error);
        })
    }
     
    handleNewType=()=>{
        this.setState({mode:"new",showModal:true, data:{
            manufacturerID:'',
            name:'',            
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
                  <label >Name</label>
                  <input value={data.name} type="text" name="" id="" className="form-control required" onChange={(event)=>this.setValue("name",event.target.value)} />                   
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
             
            } else {
              this.alert(result.error);
            }
            this.getData();
          });
        }
        else if(mode=='edit')
        {
            this.api.updateType(data).then((result) => {
              if (result.state) {
                this.setState({ showModal: false });              
              } else {
                this.alert(result.error);
              }
              this.getData();
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

export default ManufacturerComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactSectorManufacturerComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(ManufacturerComponent), domContainer);
});