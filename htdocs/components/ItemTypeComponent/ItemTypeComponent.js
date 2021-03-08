import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIItemTypes from "./services/APIItemTypeComponent.js";
import '../style.css';
import './ItemTypeComponent.css';

class ItemTypeComponent extends MainComponent {
   api=new APIItemTypes();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            types:[]   ,
            mode:"new"   ,
            data:{...this.getInitData()},
            StockCat:[]
        };
    }
    getInitData=()=>{
        return {
            id:'',
            description:'',
            active:false,
            allowGlobalPriceUpdate:false,
            reoccurring:false,
            showInCustomerReview:false,
            stockcat:'',
            sortOrder:0
        };
    }
    componentDidMount() {      
        this.getData();
        this.api.getStockCat().then(StockCat=>this.setState({StockCat}));
    }

    getData=()=>{
        this.api.getAllTypes().then(res=>{
            this.setState({types:res.data});
            console.log(res);
        });
    }

    getDataTable=()=>{
        const columns=[
            {
               path: "description",
               label: "Description",
               hdToolTip: "Description",
               hdClassName: "text-center",
               //icon: "fal fa-2x fa-text color-gray2 pointer",
               sortable: true,
               //className: "text-center",                
            },
            {
                path: "stockcat",
                label: "Stock Cat",
                hdToolTip: "Stock Cat",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-text color-gray2 pointer",
                sortable: true,
                //className: "text-center",                
             },
            {
                path: "reoccurring",
                label: "Reoccurring",
                hdToolTip: "Reoccurring",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.reoccurring?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             },
             {
                path: "active",
                label: "Active",
                hdToolTip: "Active",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.active?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             },
             {
                path: "showInCustomerReview",
                label: "Show In Customer Review",
                hdToolTip: "Show In Customer Review",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.showInCustomerReview?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
                className: "text-center",                
             },
             {
                path: "allowGlobalPriceUpdate",
                label: "Allow global Price Update",
                hdToolTip: "Allow global Price Update",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,
                content:(type)=>type.allowGlobalPriceUpdate?<i className="fal fa-2x fa-check color-gray "></i>:<i className="fal fa-2x fa-times color-gray "></i>,
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
            //  {
            //     path: "trash",
            //     label: "",
            //     hdToolTip: "Delete",
            //     hdClassName: "text-center",
            //     icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
            //     sortable: false,                
            //     className: "text-center",   
            //     content:(type)=> <i className="fal fa-2x fa-trash-alt color-gray pointer" onClick={()=>this.handleDelete(type)}></i>,
             
            //  }
        ];
    
        return <Table           
        style={{width:900,marginTop:20}}
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
        console.log(data);
        this.setState({showModal:true,data,mode:'edit'});
    }
    handleDelete=async (type)=>{
        console.log(type);
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
        console.log(current,next);
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
        console.log(current,next);
   
        await this.api.updateType(current);
        this.getData();
    }
    handleNewType=()=>{
        this.setState({mode:"new",showModal:true, data:{...this.getInitData()}});
    }
    hideModal=()=>{
        this.setState({ showModal:false});
    }
    getModalElement=()=>{
        const {mode,data,StockCat}=this.state;
        return (
          <Modal
            width={500}
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
                <table className="table">
                  <tbody>
                    <tr>
                      <td>                        
                        <div className="form-group">
                        <Toggle
                            checked={data.active}
                            onChange={() =>
                              this.setValue(
                                "active",
                                !data.active
                              )
                            }
                          ></Toggle>
                          <label>Active</label>                          
                        </div>
                      </td>
                      <td>
                        <div className="form-group">
                        <Toggle
                            checked={data.reoccurring}
                            onChange={() =>
                              this.setValue(
                                "reoccurring",
                                !data.reoccurring
                              )
                            }
                          ></Toggle>
                          <label>Reoccurring</label>                          
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>                        
                        <div className="form-group">
                        <Toggle
                            checked={data.showInCustomerReview}
                            onChange={() =>
                              this.setValue(
                                "showInCustomerReview",
                                !data.showInCustomerReview
                              )
                            }
                          ></Toggle>
                          <label>Show In Customer Review</label>
                         
                        </div>
                      </td>
                      <td>
                        <div className="form-group">
                        <Toggle
                            checked={data.allowGlobalPriceUpdate}
                            onChange={() =>
                              this.setValue(
                                "allowGlobalPriceUpdate",
                                !data.allowGlobalPriceUpdate
                              )
                            }
                          ></Toggle>

                          <label>Allow Global Price Update</label>
                         
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <div className="form-group">
                  <label>Stock Cat</label>
                  <select 
                  style={{width:100}}
                  value={data.stockcat}
                  onChange={(event)=>this.setValue('stockcat',event.target.value)}
                  >
                    <option></option>
                    {StockCat.map((s) => (
                      <option key={s.stockcat} value={s.stockcat}>
                        {s.stockcat}
                      </option>
                    ))}
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
        const { data, mode } = this.state;
        if (data.description == "") {
          this.alert("Type description required.");
          return;
        }
        if (data.stockcat == "") {
            this.alert("Please select stock category");
            return;
          }
        data.active=data.active?1:0;
        data.allowGlobalPriceUpdate=data.allowGlobalPriceUpdate?1:0;
        data.reoccurring=data.reoccurring?1:0;
        data.showInCustomerReview=data.showInCustomerReview?1:0;        
        if (mode == "new") {
          this.api.addType(data).then((result) => {
              console.log(result);
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
                console.log(result);
              if (result.state) {
                this.setState({ showModal: false });
                this.getData();
              } else {
                this.alert(result.error);
              }
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

export default ItemTypeComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactItemTypeComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(ItemTypeComponent), domContainer);
});