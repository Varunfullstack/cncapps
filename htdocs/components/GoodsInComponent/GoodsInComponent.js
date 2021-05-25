import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import APIGoodsIn from "./services/APIGoodsIn.js";
import "../style.css";
import "./GoodsInComponent.css";
import { SupplierSearchComponent } from "../SupplierSearchComponent/SupplierSearchComponent.js";
import SupplierSelectorComponent from "../PurchaseOrderSupplierAndContactInputsComponent/subComponents/SupplierSelectorComponent.js";
import Modal from "../shared/Modal/modal.js";
import OrderDetailsComponent from "./subComponents/OrderDetailsComponent.js";
 
class GoodsInComponent extends MainComponent {
  api = new APIGoodsIn();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      orders: [],
      mode: "new",
      filter:{
        supplierID:"",
        porheadID:""
      },
      order:null
    };
  }

  componentDidMount() {
    this.getData();
  }

  getData = () => {
    const {porheadID,supplierID}=this.state.filter;
    this.api.getSearchResult(porheadID,supplierID).then(
      (res) => {
        this.setState({ orders: res.data });
      },
      (error) => this.alert("Error in loading data")
    );
  };

  getDataTable = () => {
    const columns = [
      {
        path: "supplierName",
        label: "",
        hdToolTip: "Supplier",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-warehouse-alt color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "porheadID",
        label: "",
        hdToolTip: "Order No",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-hashtag color-gray2 pointer",
        sortable: true,
        //className: "text-center",
        //content:(order)=><a href={`GoodsIn.php?action=displayGoodsIn&porheadID=${order.porheadID}`}>{order.porheadID}</a>
      },
      {
        path: "ordheadID",
        label: "",
        hdToolTip: "Sales Order",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(order)=><label>{order.customerID}/{order.ordheadID}</label> 
      },
      {
        path: "orderType",
        label: "",
        hdToolTip: "Status",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-text color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "supplierRef",
        label: "",
        hdToolTip: "Supplier Ref",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-user-tag color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "customerName",
        label: "",
        hdToolTip: "Solid to",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "edit",
        label: "",
        hdToolTip: "Edit",
        hdClassName: "text-center",
        sortable: true,
        content:(order)=>this.getEditElement(order,this.handleEdit,true,"Edit order lines")
       },
    ];

    return (
      <Table
        style={{ width: 1200, marginTop: 20 }}
        key="ordersTable"
        pk="porheadID"
        columns={columns}
        data={this.state.orders || []}
        search={true}        
        defaultSortPath="porheadID"
        defaultSortOrder="desc"
      ></Table>
    );
  };
  handleEdit=(order)=>{
   this.setState({showModal:true,order});
  }
  handleSupplierChange=(supplier)=>{
    this.setFilter("supplierID",supplier?.id||"")
  }
  
  getLinesModal=()=>{
    const {order,showModal}=this.state;
    if(!showModal)
    return null;
    return <Modal title="Edit order" width={1200}
    show={showModal}
    onClose={()=>this.setState({showModal:false,order:null})}
    
    >
      <OrderDetailsComponent onClose={this.handleOnClose} porheadID={order.porheadID}></OrderDetailsComponent>
    </Modal>
  }
  handleOnClose=()=>{
    this.getData();
    this.setState({showModal:false})
  }
  render() {
    const {filter}=this.state;
    return (
      <div>
        <Spinner show={this.state.showSpinner}></Spinner>
        {this.getAlert()}
        <table style={{display:"none"}}>
          <tbody>
            <tr>
              <td>
                Supplier
              </td>
              <td>        
                <SupplierSelectorComponent onChange={this.handleSupplierChange}></SupplierSelectorComponent>
              </td>
            </tr>
            <tr>
              <td>Purchase Order No</td>
              <td>
                <input type="number" className="form-control" value={filter.porheadID} onChange={(event)=>this.setFilter("porheadID",event.target.value)}></input>
              </td>
            </tr>
            <tr>
              <td></td>
              <td>
                {this.getSearchElement(this.getData)}
               </td>
            </tr>
          </tbody>
        </table>
        {this.getDataTable()}
        {this.getLinesModal()}
      </div>
    );
  }
}

export default GoodsInComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactGoodsInComponent");
  if (domContainer)
    ReactDOM.render(React.createElement(GoodsInComponent), domContainer);
});
