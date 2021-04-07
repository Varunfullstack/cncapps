import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import APIExpenseType from "./services/APIExpenseType.js";
import '../style.css';
import './ExpenseTypeComponent.css';
import Toggle from "../shared/Toggle.js";
import APICallactType from "../services/APICallactType.js";

class ExpenseTypeComponent extends MainComponent {
   api=new APIExpenseType();
   apiCallactType=new APICallactType();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            types:[]   ,
            mode:"new"   ,
            data:{
                ...this.getDataInit()
            },
            ActivityTypes:[]
        };
    }

    componentDidMount() {  
        console.log("start");    
        this.getData();
        this.apiCallactType.getAll().then(ActivityTypes=>this.setState({ActivityTypes}));
    }

    getData=()=>{
        this.api.getAllTypes().then(res=>{
            if(res.state)
            this.setState({types:res.data});
            console.log(res);
        });
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "description",
               label: "",
               hdToolTip: "Name",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-text color-gray2 pointer",
               sortable: true,
               //className: "text-center",                
            },     
            {
                path: "taxable",
                label: "Paye Taxable",
                hdToolTip: "Paye Taxable",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.taxable?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             },     
             {
                path: "approvalRequired",
                label: "Approval Required	",
                hdToolTip: "Approval Required	",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.approvalRequired?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             }, 
             {
                path: "receiptRequired",
                label: "Receipt Required",
                hdToolTip: "Receipt Required",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.receiptRequired?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             }, 
             {
                path: "mileageFlag",
                label: "Mileage",
                hdToolTip: "Mileage",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.mileageFlag?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             }, 
             {
                path: "vatFlag",
                label: "VAT included",
                hdToolTip: "VAT included",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.vatFlag?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             }, 
             {
                path: "maximumAutoApprovalAmount",
                label: "Maximum Auto Approval Amount £	",
                hdToolTip: "Maximum Auto Approval Amount £	",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
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
                content:(type)=>type.canDelete? <i className="fal fa-2x fa-trash-alt color-gray pointer" onClick={()=>this.handleDelete(type)}></i>:null,             
             }
        ];
    
        return <Table           
        style={{width:1000,marginTop:20}}
        key="leadStatus"
        pk="expenseTypeID"
        columns={columns}
        data={this.state.types||[]}
        search={true}
        >
        </Table>
    }
    showEditModal=async (data)=>{        
        const activityTypes=await this.api.getExpenseActivityTypes(data.expenseTypeID);
        data.activityTypes=[...activityTypes];
        this.setState({showModal:true,data:{...data},mode:'edit'});
    }
    handleDelete=async (type)=>{
        console.log(type);
        const conf=await this.confirm("Are you sure to delete this type?")
        if(conf)
        this.api.deleteType(type.expenseTypeID).then(res=>{
            if(res.state)
            this.getData();
            else this.alert(res.error);
        })
    }
    getDataInit=()=>{
        return {
                expenseTypeID:'',
                description:'', 
                canDelete:false,
                taxable:0,
                approvalRequired:0,
                receiptRequired:0,
                mileageFlag:0,
                vatFlag:0,
                maximumAutoApprovalAmount:0,
                activityTypes:[]
        }
    }
    handleNewType=()=>{
        this.setState({mode:"new",showModal:true, data:{
            ...this.getDataInit()          
        }});
    }
    hideModal=()=>{
        this.setState({ showModal:false});
    }
    getModalElement=()=>{
        const {mode,data,ActivityTypes}=this.state;
        return (
          <Modal
            width={500}
            show={this.state.showModal}
            title={mode == "new" ? "Add New Type" : "Edit Type"}
            onClose={this.hideModal}
            content={
              <div key="content">
                <table>
                  <tbody>
                    <tr>
                      <td>Description</td>
                      <td>
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
                      </td>
                    </tr>
                    <tr>
                      <td>Maximum Auto Approval Amount £ </td>
                      <td>
                        <input
                          value={data.maximumAutoApprovalAmount}
                          type="number"
                          name=""
                          id=""
                          className="form-control required"
                          onChange={(event) =>
                            this.setValue(
                              "maximumAutoApprovalAmount",
                              event.target.value
                            )
                          }
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>Activity Applicability	</td>
                      <td>
                          <select className="form-control required" value={data.activityTypes} multiple onChange={(event)=>this.handleActivityTypesSelect(event.target.selectedOptions)}>
                        {ActivityTypes.map(a=><option key={a.id} value={a.id}>{a.description}</option>)}
                          
                          </select>
                       
                      </td>
                    </tr>
                  </tbody>
                </table>
                <table className="table">
                  <tbody>
                    <tr>
                      <td>
                        <div className="form-group">
                          <Toggle
                            checked={data.taxable}
                            onChange={() =>
                              this.setValue("taxable", !data.taxable)
                            }
                          ></Toggle>
                          <label>PAYE Taxable</label>
                        </div>
                      </td>
                      <td>
                        <div className="form-group">
                          <Toggle
                            checked={data.approvalRequired}
                            onChange={() =>
                              this.setValue(
                                "approvalRequired",
                                !data.approvalRequired
                              )
                            }
                          ></Toggle>
                          <label>Approval Required</label>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div className="form-group">
                          <Toggle
                            checked={data.receiptRequired}
                            onChange={() =>
                              this.setValue(
                                "receiptRequired",
                                !data.receiptRequired
                              )
                            }
                          ></Toggle>
                          <label>Receipt Required </label>
                        </div>
                      </td>
                      <td>
                        <div className="form-group">
                          <Toggle
                            checked={data.mileageFlag}
                            onChange={() =>
                              this.setValue("mileageFlag", !data.mileageFlag)
                            }
                          ></Toggle>

                          <label>Mileage</label>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div className="form-group">
                          <Toggle
                            checked={data.vatFlag}
                            onChange={() =>
                              this.setValue("vatFlag", !data.vatFlag)
                            }
                          ></Toggle>
                          <label>VAT included</label>
                        </div>
                      </td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
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
    handleActivityTypesSelect=(options)=>{
        
        const values=Array.from(options, option => option.value);
        console.log(values);
        this.setValue("activityTypes",values);
    }
    handleSave=()=>{
        const { data, mode } = this.state;
        if (data.description == "") {
          this.alert("Type name required.");
          return;
        }
        data.approvalRequired=data.approvalRequired?1:0;        
        data.receiptRequired=data.receiptRequired?1:0;
        data.taxable=data.taxable?1:0;
        data.vatFlag=data.vatFlag?'Y':'N';
        data.mileageFlag=data.mileageFlag?'Y':'N';
        console.log(data);         
        if (mode == "new") {
          this.api.addType(data).then((result) => {
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
            this.api.updateType(data).then((result) => {
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

export default ExpenseTypeComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactExpenseTypeComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(ExpenseTypeComponent), domContainer);
});