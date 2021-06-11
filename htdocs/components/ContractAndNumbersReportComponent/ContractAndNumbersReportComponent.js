import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import Table, { CellType } from "../shared/table/table.js"; 
import APIContractAndNumbersReport from "./services/APIContractAndNumbersReport.js";
import ToolTip from "../shared/ToolTip";
import "../style.css";
import "./ContractAndNumbersReportComponent.css";
import { exportCSV } from "../utils/utils.js";
import ReactTooltip from 'react-tooltip';


class ContractAndNumbersReportComponent extends MainComponent {
  api = new APIContractAndNumbersReport();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      mode: "new",
      items: [],
      totalPrePay:0
    };
  }
 
  componentDidMount() {
    this.getData();
  }

  getData = () => {
    this.setState({showSpinner:true})
    this.api.getReport().then((res) => {
      
      this.setState({ items: res.data.contracts.map((item,index)=>{
        item.id=index;
        return item
      }),totalPrePay:res.data.totalPrePay });
      this.setState({showSpinner:false})
    });
  };

  getDataTable = () => {
    const columns = [
      {
        path: "customerName",
        label: "Customer",
        hdToolTip: "Customer",
        //hdClassName: "text-left",
       // icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        //className: "text-center",
        cellType:CellType.Text,
      },
      {
        path: "serviceDeskProduct",
        label: "ServiceDesk Product",
        hdToolTip: "ServiceDesk Product",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        //className: "text-center",
        cellType:CellType.Text,
      },
      {
        path: "serviceDeskUsers",
        label: "ServiceDesk Users",
        hdToolTip: "ServiceDesk Users",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        footerContent:(contract)=><label>Total: {this.getTotal("serviceDeskUsers")}</label>,
        footerClass:"text-center",
        cellType:CellType.Number,
      },
      {
        path: "supportedUsers",
        label: "Supported Users",
        hdToolTip: "Supported Users",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        classNameColumn: "moreThanExpectedClass",
        cellType:CellType.Text,
      },
      {
        path: "serviceDeskContract",
        label: "ServiceDesk Contract",
        hdToolTip: "ServiceDesk Contract",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(contract)=><label>£{contract.serviceDeskContract}</label>,
        footerContent:(contract)=><label>Total: £{this.getTotal("serviceDeskContract")}</label>,
        footerClass:"text-center",
        cellType:CellType.Money,
      },
      {
        path: "serviceDeskCostPerUserMonth",
        label: " Cost per user per month",
        hdToolTip: " Cost per user per month",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(contract)=><label>£{contract.serviceDeskCostPerUserMonth}</label>,
        footerContent:(contract)=><label>AVG: £{this.getAvg("serviceDeskCostPerUserMonth")}</label>,
        footerClass:"text-center",
        cellType:CellType.Money,
      },
      {
        path: "serverCareProduct",
        label: " ServerCare Product",
        hdToolTip: " ServerCare Product",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        //className: "text-center",
        cellType:CellType.Text,
      },
      {
        path: "physicalServers",
        label: "Physical Servers",
        hdToolTip: "Physical Servers",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        footerContent:(contract)=><label>Total: {this.getTotal("physicalServers")}</label>,
        footerClass:"text-center",
        cellType:CellType.Number,
      },
      {
        path: "virtualServers",
        label: "Virtual Servers",
        hdToolTip: "Virtual Servers",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        footerContent:(contract)=><label>Total: {this.getTotal("virtualServers")}</label>,
        footerClass:"text-center",
        cellType:CellType.Number,
      },
      {
        path: "serverCareContract",
        label: "ServerCare Contract",
        hdToolTip: "ServerCare Contract",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(contract)=><label>£{contract.serverCareContract}</label>,
        footerContent:(contract)=><label>Total: £{this.getTotal("virtualServers")}</label>,
        footerClass:"text-center",
        cellType:CellType.Money,

      },
       
    ];    
    return (
      <Table
        style={{ }}
        key="items"
        pk="id"
        columns={columns}
        data={this.state.items || []}
        search={true}
        hasFooter={true}
      ></Table>
    );
  };

  getTotal=(prop)=>{     
    const {items}=this.state;
    return items.reduce((total,cur)=>total+cur[prop],0);
  }
  getAvg=(prop)=>{     
    const {items}=this.state;
    return (items.reduce((total,cur)=>total+cur[prop],0)/items.length).toFixed(2);
  }
  handleExportCsv=()=>{
    const {items}=this.state;
    items.map(i=>{
      delete i.id;
      delete i.moreThanExpectedClass;
      return i;
    })
    exportCSV(items,"ServiceContractsRatio.csv");
  }
  render() {
    const {items,totalPrePay}=this.state;
    return (
      <div >
        <div style={{display:"flex",justifyContent:"center",alignItems:"center",flexDirection:"row"}}>
        <div id="tool" className="mr-5">
           <span data-tip="hello new tooltip1">Test Tooltip</span>
           <ReactTooltip place="top" />
        </div>
        <div id="tool"  className="ml-5">
           <span data-tip="hello new tooltip2">Test Tooltip2</span>
           <ReactTooltip place="bottom" />
        </div>
        </div>
        <Spinner show={this.state.showSpinner}></Spinner>
        {this.getAlert()}        
        <ToolTip title="Export to CSV" width={40}>
          <i className="fal fa-2x fa-file-csv color-gray2 pointer mb-5" onClick={this.handleExportCsv}></i>
        </ToolTip>
        {this.getDataTable()}
        <table>
          <tbody>
            <tr>
              <td style={{textAlign:"right"}}><strong>Total number of rows:</strong></td>
              <td>{items.length}</td>
            </tr>
            <tr>
              <td  style={{textAlign:"right"}}><strong>Total Pre-Pay Support Users:</strong></td>
              <td>{totalPrePay}</td>
            </tr>
          </tbody>
        </table>
      </div>
    );
  }
}

export default ContractAndNumbersReportComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactContractAndNumbersReportComponent");
  if (domContainer)
    ReactDOM.render(React.createElement(ContractAndNumbersReportComponent), domContainer);
});
