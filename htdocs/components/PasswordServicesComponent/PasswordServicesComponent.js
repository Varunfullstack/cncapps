import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIPasswordServices from "./services/APIPasswordServices.js";
import '../style.css';
import './PasswordServicesComponent.css';

class PasswordServicesComponent extends MainComponent {
   api=new APIPasswordServices();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            services:[]   ,
            mode:"new"   ,
            data:{...this.getInitData()},          
        };
    }
    getInitData=()=>{
        return {
          passwordServiceID:'',
            description:'',
            onePerCustomer:false,
            defaultLevel:1,
            sortOrder:0
        };
    }
    componentDidMount() {      
        this.getData();
     }

    getData=()=>{
        this.api.getAllServices().then(res=>{
            this.setState({services:res.data});
            console.log(res);
        });
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
                path: "onePerCustomer",
                label: "",
                hdToolTip: "One Per Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-person-sign color-gray2 pointer",
                sortable: true,
                className: "text-center",      
                content:(service)=><Toggle checked={service.onePerCustomer} disabled={true}></Toggle>
             },
            {
                path: "defaultLevel",
                label: "",
                hdToolTip: "Default Level",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-heart color-gray2 pointer",
                sortable: true,                
                className: "text-center",                
             },
              
             {
                path: "edit",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: this.getEditIcon(),
                sortable: false,                
                className: "text-center",   
                content:(service)=>this.getEditElement(service,()=>this.showEditModal(service)),
             },
             {
                path: "trash",
                label: "",
                hdToolTip: "Delete",
                hdClassName: "text-center",
                icon: this.getDeleteIcon(),
                sortable: false,                
                className: "text-center",   
                content:(service)=>this.getDeleteElement(service,()=>this.handleDelete(service)),             
             }
        ];
    
        return <Table           
        style={{width:900,marginTop:20}}
        onOrderChange={this.handleOrderChange} 
        allowRowOrder={true}
        key="leadStatus"
        pk="passwordServiceID"
        columns={columns}
        data={this.state.services||[]}
        search={true}
        >
        </Table>
    }
    showEditModal=(data)=>{
        console.log(data);
        this.setState({showModal:true,data:{...data},mode:'edit'});
    }
    handleDelete=async (service)=>{
        console.log(service);
        const conf=await this.confirm("Are you sure to delete this service?")
        if(conf)
          this.api.deleteService(service.passwordServiceID).then(res=>{
              if(res.state)
              this.getData();
              else this.alert(res.error);
          });
    }
    handleOrderChange=async (current,next)=>{
        console.log(current,next);
        const {services}=this.state;
        if(next)
        {
            current.sortOrder=next.sortOrder;
            next.sortOrder=current.sortOrder+0.001;
            await this.api.saveService(next);
        }
        if(!next)
        {        
            current.sortOrder=Math.max(...services.map(i=>i.sortOrder))+0.001;
        }     
        console.log(current,next);   
        await this.api.saveService(current);
        this.getData();
    }
    handleNewType=()=>{
        this.setState({mode:"new",showModal:true, data:{...this.getInitData()}});
    }
    hideModal=()=>{
        this.setState({ showModal:false});
    }
    getModalElement=()=>{
        const {mode,data}=this.state;
        return (
          <Modal
            width={500}
            show={this.state.showModal}
            title={mode == "new" ? "Add New Service" : "Edit Service"}
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
                  <label>One Per Customer</label>
                  <Toggle
                            checked={data.onePerCustomer}
                            onChange={() =>
                              this.setValue(
                                "onePerCustomer",
                                !data.onePerCustomer
                              )
                            }
                          ></Toggle>
                </div>
                <div className="form-group">
                  <label>Default Level</label>
                  <select value={data.defaultLevel}   onChange={(event) =>
                      this.setValue("defaultLevel", event.target.value)
                    }>
                    <option value={1}>1</option>
                    <option value={2}>2</option>
                    <option value={3}>3</option>
                    <option value={4}>4</option>
                  </select>
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
        const { data} = this.state;
        if (data.description == "") {
          this.alert("Password Service description required.");
          return;
        }
               
        data.onePerCustomer=data.onePerCustomer?1:0;
        this.api.saveService(data).then((result) => {
            console.log(result);
          if (result.state) {
            this.setState({ showModal: false });
            this.getData();
          } else {
            this.alert(result.error);
          }
        });        
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

export default PasswordServicesComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactPasswordServicesComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(PasswordServicesComponent), domContainer);
});