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
      order:null,
      recieveAll:false
    };
  }

  componentDidMount() {
    this.getData();
    document.addEventListener("keydown",this.handleKeyDown)
  }
  handleKeyDown=(ev)=>{     
    if(ev.key=="F5")
    {
      this.handleRecieveAll(!this.state.recieveAll);
      ev.preventDefault();
      return false;
    }
    
  }
  componentWillUnmount() {
    document.removeEventListener("keydown",this.handleKeyDown);
  }
  getData = () => {
    const { porheadID,isModal } = this.props;
    if (porheadID)
    {
      if(!isModal)
      this.api.getSearchResult(porheadID,"").then(
        (res) => {
          this.setState({ order: res.data[0] });
        },
        (error) => this.alert("Error in loading data")
      );
      this.api.getOrderLines(porheadID).then(
        (res) => {
          const lines= res.lines;
          lines.map((line,index)=>line.id=index+1);
          this.setState({ lines });
        },
        (error) => this.alert("Error in loading data")
      );     
        
    
    }
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
        content: (item) => (
          <div style={{ display: "flex", justifyContent: "center" }}>
            <input
              disabled={item.lineDisabled}
              type="number"
              className="form-control"
              style={{ width: 100 }}
              defaultValue={item.qtyToReceive}
              id={`qtyToReceive`+item.id}
              //onChange={(event)=>this.handleOrderChange(order,"qtyToReceive",parseInt(event.target.value))}
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
        content: (item) => (
          <div style={{ display: "flex", justifyContent: "center" }}>
            <input
              disabled={item.lineDisabled||item.disabled}
              className="form-control"
              style={{ width: 150 }}
              defaultValue={item.serialNo}
              id={`serialNo`+item.id}
              // onChange={(event)=>this.handleOrderChange(item,"serialNo",event.target.value)}
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
          <select disabled={order.lineDisabled||order.disabled}
          className="form-control" value={order.warrantyID}   onChange={(event)=>this.handleOrderChange(order,"warrantyID",event.target.value)}
          >
            <option>N/A</option>
            {order.warranties.map((w,indx) => (
              <option key={indx} value={w.warrantyID}>{w.warrantyDescription}</option>
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
        content: (order) => <Toggle disabled={order.lineDisabled||order.disabled}
        checked={order.renew}  onChange={(event)=>this.handleOrderChange(order,"renew",!order.renew)}></Toggle>,
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
  handleOrderChange=(item,prop,value)=>{
      const {lines}=this.state;
      if(this.tableTimeChange)
      clearTimeout(this.tableTimeChange);
      this.tableTimeChange=setTimeout(()=>{
        const temp=lines.find(o=>o.id==item.id);
        temp[prop]=value;
        this.setState(lines);
       },500)
     
  }
  
  handleSupplierChange = (supplier) => {
    this.setFilter("supplierID", supplier?.id || "");
  };
  handlePurchaseOrder=()=>{
    const { porheadID } = this.props;
    window.open(`PurchaseOrder.php?action=display&porheadID=${porheadID}`, '_blank');
  }
  updateItems=(lines)=>{
    lines.map(line=>{
      const serialNo=document.getElementById(`serialNo${line.id}`).value;      
      line.serialNo=serialNo;
      const qtyToReceive=document.getElementById(`qtyToReceive${line.id}`).value;      
      line.qtyToReceive=qtyToReceive;
    })
  }
  handleReceive=()=>{
    const {lines}=this.state;
    const { porheadID } = this.props;
    this.updateItems(lines);
    const linesToReceive=lines.filter(l=>l.qtyToReceive>0);
    if(linesToReceive.length==0)
    {
      this.alert("Please enter at least one value to receive");
      return;
    }   
    this.setState({lines});
    lines.map(l=>{
      l.qtyToReceive=parseInt(l.qtyToReceive);
    })
    this.api.receive(porheadID,lines).then(res=>{   
      window.location=`PurchaseOrder.php?action=display&porheadID=${porheadID}`;     
    }).catch(res=>{      
      this.alert(res.error)
    })
  }
  handleRecieveAll=(value)=>{
    let {lines}=this.state;
    this.updateItems(lines);
    lines.map(line=>line.qtyToReceive=value?line.qtyOS:0);
    this.setState({lines,recieveAll:value});
  }
  getOrderDetails=()=>{
    const {order}=this.state;
    if(order)
    {
      return <table className="mt-4">
        <tbody>
          <tr>
            <td className="label">Supplier</td>
            <td>{order.supplierName}</td>
          </tr>
          <tr>
            <td className="label">Purchase Order</td>
            <td>{order.porheadID}</td>
          </tr>
          <tr>
            <td className="label">Customer</td>
            <td>{order.customerName}</td>
          </tr>
          <tr>
            <td className="label">Sales Order</td>
            <td>{order.customerID+"/"+order.ordheadID}</td>
          </tr>
        </tbody>
      </table>
    }
  }
  render() {
    
    return (
      <div>
        <Spinner show={this.state.showSpinner}></Spinner>
        {this.getAlert()}
        {this.getOrderDetails()}
        <div style={{display:"flex","justifyContent":"center","marginBottom":-40,alignItems:"center"}}>
          <label className="mr-2" style={{marginLeft:200}}>Recevive All</label>
          <Toggle checked={this.state.recieveAll} width={30} onChange={this.handleRecieveAll}>

          </Toggle>
        </div>
        {this.getDataTable()}
        <div className="modal-footer">
          <button onClick={this.handleReceive}>Receive</button>
          <button onClick={this.handlePurchaseOrder}>Purchase Order</button>
        </div>
      </div>
    );
  }
}
