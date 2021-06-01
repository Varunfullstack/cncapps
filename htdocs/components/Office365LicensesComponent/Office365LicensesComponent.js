import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIOffice365Licenses from "./services/APIOffice365Licenses.js";
import "../style.css";
import "./Office365LicensesComponent.css";
import CustomerSearch from "../shared/CustomerSearch.js";

class Office365LicensesComponent extends MainComponent {
  api = new APIOffice365Licenses();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      licenses: [],
      mode: "new",
      data: { ...this.getInitData() },
    };
    
  }
  getInitData = () => {
    return {
      id: "",
      replacement: "",
      license:"",
      mailboxLimit: "",
      reportOnSpareLicenses: false,
      includesDefender: false,
      includesOffice: false,
    };
  };
  componentDidMount() {
    this.getData();
  }

  getData = () => {
    this.api.getAllLicenses().then(
      (res) => {         
        this.setState({ licenses: res.data });
      },
      (error) => this.alert("Error in loading data")
    );
  };

  getDataTable = () => {
    const columns = [
      {
        path: "license",
        label: "License",
        //hdToolTip: "Domain",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-at color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "mailboxLimit",
        label: "Mailbox Limit(MB)",
        //hdToolTip: "Customer",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "replacement",
        label: "Friendly Name	",
        //hdToolTip: "Domain",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-at color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "reportOnSpareLicenses",
        label: "Report on Spare Licenses	",
        //hdToolTip: "Customer",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(license)=><Toggle checked={license.reportOnSpareLicenses} disabled={true}></Toggle>
      },
      {
        path: "includesDefender",
        label: "Includes Defender	",
        //hdToolTip: "Customer",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(license)=><Toggle checked={license.includesDefender} disabled={true}></Toggle>

      },
      {
        path: "includesOffice",
        label: "Includes Office	",
        //hdToolTip: "Customer",
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-building color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(license)=><Toggle checked={license.includesOffice} disabled={true}></Toggle>

      },
      {
        path: "edit",
        label: "",
        hdToolTip: "Edit",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-edit color-gray2 pointer",
        sortable: false,
        className: "text-center",
        content: (domain) =>
          this.getEditElement(domain, () => this.showEditModal(domain)),
      },
      {
        path: "trash",
        label: "",
        hdToolTip: "Delete",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
        sortable: false,
        className: "text-center",
        content: (domain) =>
          this.getDeleteElement(domain, () => this.handleDelete(domain)),
      },
    ];

    return (
      <Table
        style={{   marginTop: 20 }}
        key="licensesTable"
        pk="id"
        columns={columns}
        data={this.state.licenses || []}
        search={true}
      ></Table>
    );
  };
  showEditModal = (data) => {
    if (data.customerID == 0) data.global = true;
    else data.global = false;
    this.setState({ showModal: true, data: { ...data }, mode: "edit" });
  };
  handleDelete = async (type) => {
    const conf = await this.confirm("Are you sure to delete this License?");
    if (conf)
      this.api.deleteLicense(type.id).then((res) => {
        if (res.state) this.getData();
        else this.alert(res.error);
      });
  };

  handleNewLicense = () => {
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
    const { mode, data } = this.state;    
    if (!this.state.showModal) return null;
    return (
      <Modal
        width={400}
        show={this.state.showModal}
        title={mode == "new" ? "Add New Licenses" : "Edit Licenses"}
        onClose={this.hideModal}
        content={
          <div key="content" id="formData">
             
            <div className="form-group">
              <label>License</label>
              <input
                value={data.license}
                type="text"
                name=""
                id=""
                className="form-control required"
                required
                onChange={(event) =>
                  this.setValue("license", event.target.value.replace(" ", ""))
                }
              />
            </div>
            <div className="form-group">
              <label>Mailbox Limit (MB) </label>
              <input
                value={data.mailboxLimit}
                type="number"
                name=""
                id=""
                className="form-control"                 
                onChange={(event) =>
                  this.setValue("mailboxLimit", event.target.value)
                }
              />
            </div>
            <div className="form-group">
              <label>Friendly Name	</label>
              <input
                value={data.replacement}
                type="text"
                name=""
                id=""
                className="form-control required"
                required
                onChange={(event) =>
                  this.setValue("replacement", event.target.value)
                }
              />
            </div>
            <div className="form-group">
              <label>Report on Spare Licenses</label>
              <Toggle checked={data.reportOnSpareLicenses} onChange={()=>this.setValue("reportOnSpareLicenses",!data.reportOnSpareLicenses)}></Toggle>               
            </div>
            <div className="form-group">
              <label>Includes Defender</label>
              <Toggle checked={data.includesDefender} onChange={()=>this.setValue("includesDefender",!data.includesDefender)}></Toggle>               
            </div>
            <div className="form-group">
              <label>Includes Office</label>
              <Toggle checked={data.includesOffice} onChange={()=>this.setValue("includesOffice",!data.includesOffice)}></Toggle>               
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
    const { data, mode } = this.state;
    delete data.global;
    delete data.customerString;
    if (!this.isFormValid("formData")) {
      this.alert("Please add all required data");
      return;
    }
    if (mode == "new") {
      delete data.id;
      this.api.addLicense(data).then(
        (result) => {
          this.setState({ showModal: false });
          this.getData();
        },
        (error) => {
          this.alert("Error in save data");
        }
      );
    } else if (mode == "edit") {
      this.api.updateLicense(data).then(
        (result) => {
          if (result.state) {
            this.setState({ showModal: false });
            this.getData();
          } else {
            this.alert(result.error);
          }
        },
        (error) => {
          this.alert("Error in save data");
        }
      );
    }
  };
  render() {
     return (
      <div>
        <Spinner show={this.state.showSpinner}></Spinner>
        <ToolTip title="New Domain" width={30}>
          <i
            className="fal fa-2x fa-plus color-gray1 pointer"
            onClick={this.handleNewLicense}
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

export default Office365LicensesComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector(
    "#reactOffice365LicensesComponent"
  );
  if (domContainer)
    ReactDOM.render(
      React.createElement(Office365LicensesComponent),
      domContainer
    );
});
