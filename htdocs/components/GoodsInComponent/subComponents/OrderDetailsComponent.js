import React from "react";
import MainComponent from "../../shared/MainComponent.js";
import Spinner from "../../shared/Spinner/Spinner";
import Table from "../../shared/table/table.js";
import Toggle from "../../shared/Toggle.js";
import APIGoodsIn from "../services/APIGoodsIn.js";

export default class OrderDetailsComponent extends MainComponent {
  api = new APIGoodsIn();
  tableTimeChange;
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      lines: [],
    };
  }

  componentDidMount() {
    this.getData();
  }

  getData = () => {
    const { porheadID } = this.props;
    if (porheadID)
      this.api.getOrderLines(porheadID).then(
        (res) => {
          console.log(res);
          this.setState({ lines: res.lines });
        },
        (error) => this.alert("Error in loading data")
      );
  };

  getDataTable = () => {
    const columns = [
      {
        path: "description",
        label: "Description",
        //hdToolTip: "Description",
        hdClassName: "text-center",
        //icon: "fal  fa-file-alt color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "partNo",
        label: "Part No",
        //hdToolTip: "Part No",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-hashtag color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "qtyOrdered",
        label: "Ordered",
        // hdToolTip: "Ordered",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-text color-gray2 pointer",
        sortable: true,
        className: "text-center",
      },
      {
        path: "qtyOS",
        label: "Outstanding",
        //hdToolTip: "Outstanding",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-user-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
      },
      {
        path: "qtyToReceive",
        label: "Receive",
        //hdToolTip: "Receive",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content: (order) => (
          <div style={{ display: "flex", justifyContent: "center" }}>
            <input
              type="number"
              className="form-control"
              style={{ width: 100 }}
              defaultValue={order.qtyToReceive}
              onChange={(event)=>this.handleOrderChange(order,"qtyToReceive",event.target.value)}
            ></input>
          </div>
        ),
      },
      {
        path: "serialNo",
        label: "Serial No",
        //hdToolTip: "Serial No",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        //className: "text-center",
        content: (order) => (
          <div style={{ display: "flex", justifyContent: "center" }}>
            <input
              className="form-control"
              style={{ width: 150 }}
              value={order.serialNo}
              onChange={(event)=>this.handleOrderChange(order,"serialNo",event.target.value)}
            ></input>
          </div>
        ),
      },
      {
        path: "warrantyID",
        label: "Warranty",
        // hdToolTip: "Warranty",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        //className: "text-center",
        content: (order) => (
          <select className="form-control" value={order.warrantyID}   onChange={(event)=>this.handleOrderChange(order,"warrantyID",event.target.value)}
          >
            <option>N/A</option>
            {order.warranties.map((w) => (
              <option key={w.warrantyID} value={w.warrantyID}>{w.warrantyDescription}</option>
            ))}
          </select>
        ),
      },
      {
        path: "renew",
        label: "Renew",
        //hdToolTip: "Renew",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content: (order) => <Toggle checked={order.renew}  onChange={(event)=>this.handleOrderChange(order,"renew",!order.renew)}></Toggle>,
      },
    ];

    return (
      <Table
        style={{ marginTop: 20 }}
        key="ordersTable"
        pk="itemID"
        columns={columns}
        data={this.state.lines || []}
        search={true}
        hover={false}
      ></Table>
    );
  };
  handleOrderChange=(order,prop,value)=>{
      const {lines}=this.state;
      if(this.tableTimeChange)
      clearTimeout(this.tableTimeChange);
      this.tableTimeChange=setTimeout(()=>{
        const temp=lines.find(o=>o.itemID==order.itemID);
        console.log("temp",temp);
        temp[prop]=value;        
       },500)
     
  }
  
  handleSupplierChange = (supplier) => {
    console.log(supplier);
    this.setFilter("supplierID", supplier?.id || "");
  };

  render() {
    return (
      <div>
        <Spinner show={this.state.showSpinner}></Spinner>
        {this.getAlert()}
        {this.getDataTable()}
        <div className="modal-footer">
          <button>Receive</button>
          <button>Purchase Order</button>
        </div>
      </div>
    );
  }
}
