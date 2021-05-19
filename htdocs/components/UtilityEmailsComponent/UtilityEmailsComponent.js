import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIUtilityEmails from "./services/APIUtilityEmails.js";
import '../style.css';
import './UtilityEmailsComponent.css';

class UtilityEmailsComponent extends MainComponent {
   api=new APIUtilityEmails();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            types:[]   ,
            mode:"new"   ,
            data:{...this.getInitData()},           
        };
    }
    getInitData=()=>{
        return {
            id:'',
            firstPart:'',
            lastPart:'*',            
            wildcard:true
        };
    }
    componentDidMount() {      
        this.getData();
    }

    getData=()=>{
        this.api.getAllEmails().then(res=>{
          console.log(res);
            this.setState({types:res.data});
        },error=>this.alert("Error in loading data"));
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "firstPart",
               label: "",
               hdToolTip: "First Part",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-text color-gray2 pointer",
               sortable: true,
               //className: "text-center",                
            },      
            {
              path: "lastPart",
              label: "",
              hdToolTip: "Last Part",
              hdClassName: "text-center",
              icon: "fal fa-2x fa-at color-gray2 pointer",
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
                content:(type)=>this.getEditElement(type,()=>this.showEditModal(type)),
             
             },
             {
                path: "trash",
                label: "",
                hdToolTip: "Delete",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=>this.getDeleteElement(type,()=>this.handleDelete(type),type.canDelete),             
             }
        ];
    
        return <Table           
        style={{width:900,marginTop:20}}        
        key="rootCauseTable"
        pk="id"
        columns={columns}
        data={this.state.types||[]}
        search={true}
        >
        </Table>
    }
    showEditModal=(data)=>{
      if(data.lastPart=="*")
          data.wildcard=true;
          else
          data.wildcard=false;
        this.setState({showModal:true,data:{...data},mode:'edit'});
    }
    handleDelete=async (type)=>{
        const conf=await this.confirm("Are you sure to delete this email?")
        if(conf)
        this.api.deleteEmail(type.id).then(res=>{
            if(res.state)
            this.getData();
            else this.alert(res.error);
        }

        )
    }
   
    handleNewType=()=>{
        this.setState({mode:"new",showModal:true, data:{...this.getInitData()}});
    }
    hideModal=()=>{
        this.setState({ showModal:false});
    }
    getModalElement=()=>{
        const {mode,data}=this.state;
        console.log(data);
        if(!this.state.showModal)
        return null;
        return (
          <Modal
            width={400}
            show={this.state.showModal}
            title={mode == "new" ? "Add New Email" : "Edit Email"}
            onClose={this.hideModal}
            content={
              <div key="content" id="formData">
                <div className="flex-row flex-center">
                  <div className="flex-row flex-center m-3">
                    <label className="mr-1">Wildcard</label>
                    <Toggle onChange={this.handleWildcardChange} checked={data.wildcard}></Toggle>
                  </div>
                  <div className="flex-row flex-center m-3">
                    <label className="mr-1">Specific</label>
                    <Toggle  onChange={this.handleWildcardChange}  checked={!data.wildcard}></Toggle>
                  </div>
                </div>
                <div className="form-group" >
                  <label>Email</label>
                  <div className="flex-row flex-center">
                  <input
                    value={data.firstPart}
                    type="text"
                    name=""
                    id=""
                    className="form-control required"
                    required
                    onChange={(event) =>
                      this.setValue("firstPart", event.target.value.replace(" ",""))
                    }
                  />
                  <i className="fal   fa-at color-gray2 mr2 ml2 white"></i>
                  <input
                    value={data.lastPart}
                    type="text"
                    name=""
                    id=""
                    className="form-control required"
                    required
                    onChange={(event) =>
                      this.setValue("lastPart", event.target.value.replace(" ",""))
                    }
                    disabled={data.wildcard}
                  />
                  </div>
                  
                </div>
                              
              </div>
            }
            footer={
              <div key="footer">
                <button onClick={this.handleSave}>Save</button>
                <button onClick={this.hideModal}>Cancel</button>
              </div>
            }
          ></Modal>
        );
    }
    handleWildcardChange=()=>{
      const {data}=this.state;
      data.wildcard=!data.wildcard;
      if(data.wildcard)
        data.lastPart="*";
      this.setState(data);
      
    }
    handleSave=()=>{
        const { data, mode } = this.state;
        delete data.canDelete;
        if(!this.isFormValid("formData"))
       {
         this.alert("Please add all required data");
         return;
       }
        if (mode == "new") {
          delete data.id;
          this.api.addEmail(data).then((result) => {          
              this.setState({ showModal: false });
              this.getData();            
          },error=>{
            this.alert("Error in save data")
          });
        }
        else if(mode=='edit')
        {
            this.api.updateEmail(data).then((result) => {
              if (result.state) {
                this.setState({ showModal: false });
                this.getData();
              } else {
                this.alert(result.error);
              }
            },error=>{
              this.alert("Error in save data2")
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
            {
            this.getModalElement()
            }
           {this.getDataTable()}
        </div>;
    }
}

export default UtilityEmailsComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactUtilityEmailsComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(UtilityEmailsComponent), domContainer);
});