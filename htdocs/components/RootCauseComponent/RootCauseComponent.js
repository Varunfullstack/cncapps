import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIRootCause from "./services/APIRootCause.js";
import '../style.css';
import './RootCauseComponent.css';
import CNCCKEditor from "../shared/CNCCKEditor.js";

class RootCauseComponent extends MainComponent {
   api=new APIRootCause();
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
          rootCauseID:'',
            description:'',
            canDelete:false,
            longDescription:'',
            fixedExplanation:'',
        };
    }
    componentDidMount() {      
        this.getData();
    }

    getData=()=>{
        this.api.getAllTypes().then(res=>{
            this.setState({types:res.data});
        },error=>this.alert("Error in loading data"));
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "description",
               label: "",
               hdToolTip: "Description",
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
        pk="rootCauseID"
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
        this.api.deleteType(type.rootCauseID).then(res=>{
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
        if(!this.state.showModal)
        return null;
        return (
          <Modal
            width={680}
            show={this.state.showModal}
            title={mode == "new" ? "Add New Type" : "Edit Type"}
            onClose={this.hideModal}
            content={
              <div key="content">
                <div className="form-group">
                  <label>Description</label>
                  <input
                    value={data.description}
                    type="text"
                    name=""
                    id=""
                    className="form-control required"
                    onChange={(event) =>
                      this.setValue("description", event.target.value)
                    }
                  />
                </div>
                <div className="form-group">
                  <label>Long Description </label>
                  <input
                    value={data.longDescription}
                    type="text"
                    name=""
                    id=""
                    className="form-control required"
                    onChange={(event) =>
                      this.setValue("longDescription", event.target.value)
                    }
                  />
                </div>
                <div className="form-group">
                  <label>Default Fixed Explanation</label>
                  <div id="fixedExplanationTop" key="topElement" />
                  <CNCCKEditor
                    key="fixedExplanation"
                    name="fixedExplanation"
                    value={data.fixedExplanation}
                    onChange={(text) => this.setValue("fixedExplanation", text)}
                    className="CNCCKEditor"
                    type="inline"
                    style={{height:100,border:"1px solid #000"}}
                    top="fixedExplanationTop"
                    bottom="fixedExplanationBottom"
                  >
                    <div id="fixedExplanationBottom" key="bottomElement" />
                  </CNCCKEditor>
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
    handleSave=()=>{
        const { data, mode } = this.state;
        delete data.canDelete;
        if (data.description == "") {
          this.alert("Type description required.");
          return;
        }
        if (mode == "new") {
          delete data.rootCauseID;
          this.api.addType(data).then((result) => {          
              this.setState({ showModal: false });
              this.getData();            
          },error=>{
            this.alert("Error in save data")
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

export default RootCauseComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactRootCauseComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(RootCauseComponent), domContainer);
});