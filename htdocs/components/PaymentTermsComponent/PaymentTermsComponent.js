import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIPaymentTerms from "./services/APIPaymentTerms.js";
import "../style.css";
import "./PaymentTermsComponent.css";

class PaymentTermsComponent extends MainComponent {
  api = new APIPaymentTerms();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      mode: "new",
      data: { ...this.getInitData() },
      items: [],
    };
  }
  getInitData = () => {
    return {
      id: null,
      description: "",
      days: 0,
      generateInvoiceFlag: "N",
      automaticInvoiceFlag: "N",
    };
  };
  componentDidMount() {
    this.getData();
  }

  getData = () => {
    this.api.getAll().then((res) => {      
      this.setState({ items: res.data });
    },error=>{
      this.alert("Error in loading data")
    });
  };

  getDataTable = () => {
    const columns = [
      {
        path: "description",
        label: "Description",
         hdClassName: "text-center",
        //icon: "fal fa-2x fa-text color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "days",
        label: "Days",         
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
      },
      {
        path: "generateInvoiceFlag",
        label: "Raise an invoice",         
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(item)=><Toggle checked={item.generateInvoiceFlag=="Y"} disabled={true}></Toggle>
      },
      {
        path: "automaticInvoiceFlag",
        label: "Automatic invoice",     
        hdToolTip: "Automatic invoice when direct delivery complete",    
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(item)=><Toggle checked={item.automaticInvoiceFlag=="Y"} disabled={true}></Toggle>

      },
      {
        path: "edit",
        label: "",
        hdToolTip: "Edit",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-edit color-gray2 pointer",
        sortable: false,
        className: "text-center",
        content: (template) =>
          this.getEditElement(template, () => this.showEditModal(template)),
      },
      {
        path: "trash",
        label: "",
        hdToolTip: "Delete",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
        sortable: false,
        className: "text-center",
        content: (template) =>
          this.getDeleteElement(template, () => this.handleDelete(template)),
      },
    ];

    return (
      <Table
        style={{ width: 900, marginTop: 20 }}
        onOrderChange={this.handleOrderChange}
        allowRowOrder={true}
        key="items"
        pk="id"
        columns={columns}
        data={this.state.items || []}
        search={true}
      ></Table>
    );
  };
  showEditModal = (data) => {
    this.setState({ showModal: true, data:{...data}, mode: "edit" });
  };
  handleDelete = async (type) => {
    const conf = await this.confirm(
      "Are you sure to delete this payment term?"
    );
    if (conf)
      this.api.deleteItem(type.id).then((res) => {
        if (res.state) this.getData();
        else this.alert(res.error);
      });
  };
 
  handleNewTemplate = () => {
    this.setState({
      mode: "new",
      showModal: true,
      data: { ...this.getInitData() },
    });
  };
  hideModal = () => {
    this.setState({ showModal: false });
  };
  getModalElement = () => {
    const { mode, data, items } = this.state;
    return (
      <Modal
        width={500}
        show={this.state.showModal}
        title={mode == "new" ? "Add New Payment Term" : "Edit Payment Term"}
        onClose={this.hideModal}
        content={
          <div key="content" id="formData">
            <div className="form-group">
              <label>Description</label>
              <input
                required
                value={data.description}
                type="text"
                name=""
                id=""
                className="form-control"
                onChange={(event) =>
                  this.setValue("description", event.target.value)
                }
              />
            </div>
            <div className="form-group">
              <label>Days</label>
              <input                
                value={data.days}
                type="number"
                name=""
                id=""
                className="form-control"
                onChange={(event) =>
                  this.setValue("days", event.target.value)
                }
              />
            </div>
            <div className="form-group">
              <label>Raise an invoice</label>
              <Toggle checked={data.generateInvoiceFlag=='Y'} onChange={()=>this.setValue("generateInvoiceFlag", data.generateInvoiceFlag=="Y"?"N":"Y")}>

              </Toggle>              
            </div>
            <div className="form-group">
              <label>Automatic invoice when direct delivery complete	</label>
              <Toggle checked={data.automaticInvoiceFlag=='Y'} onChange={()=>this.setValue("automaticInvoiceFlag", data.automaticInvoiceFlag=="Y"?"N":"Y")}>
              </Toggle>               
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
  };
  handleSave = () => {
    const { data } = this.state;
    if (!this.isFormValid("formData")) {
      this.alert("Please enter all required inputs.");
      return;
    }
    let updateData = {
      description: data.description,
      days: data.days,
      id: data.id,
      generateInvoiceFlag: data.generateInvoiceFlag,
      automaticInvoiceFlag: data.automaticInvoiceFlag,
    };
    let callApi;
    if (data.id==null) callApi = this.api.add(updateData);
    else callApi = this.api.update(updateData);
    callApi
      .then((result) => {
        if (result.state) {
          this.setState({ showModal: false });
          this.getData();
        } else {
          this.alert(result.error);
        }
      })
      .catch((error) => {
        console.log(error);
        this.alert(error.error || "Error in save data.");
      });
  };
  render() {
    return (
      <div>
        <Spinner show={this.state.showSpinner}></Spinner>
        <ToolTip title="New Type" width={30}>
          <i
            className="fal fa-2x fa-plus color-gray1 pointer"
            onClick={this.handleNewTemplate}
          ></i>
        </ToolTip>
        {this.getConfirm()}
        {this.getAlert()}
        {this.getModalElement()}
        {this.getDataTable()}
      </div>
    );
  }
}

export default PaymentTermsComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactPaymentTermsComponent");
  if (domContainer)
    ReactDOM.render(React.createElement(PaymentTermsComponent), domContainer);
});
