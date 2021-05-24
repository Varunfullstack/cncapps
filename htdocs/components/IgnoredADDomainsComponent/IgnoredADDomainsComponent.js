import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIIgnoredADDomains from "./services/APIIgnoredADDomains.js";
import "../style.css";
import "./IgnoredADDomainsComponent.css";
import CustomerSearch from "../shared/CustomerSearch.js";

class IgnoredADDomainsComponent extends MainComponent {
  api = new APIIgnoredADDomains();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      types: [],
      mode: "new",
      data: { ...this.getInitData() },
    };
  }
  getInitData = () => {
    return {
      id: "",
      domain: "",
      customerID: 0,
      customerString: "",
      global: true,
    };
  };
  componentDidMount() {
    this.getData();
  }

  getData = () => {
    this.api.getAllDomains().then(
      (res) => {
        this.setState({ types: res.data });
      },
      (error) => this.alert("Error in loading data")
    );
  };

  getDataTable = () => {
    const columns = [
      {
        path: "domain",
        label: "",
        hdToolTip: "Domain",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-at color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "customerString",
        label: "",
        hdToolTip: "Customer",
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
        style={{ width: 900, marginTop: 20 }}
        key="rootCauseTable"
        pk="id"
        columns={columns}
        data={this.state.types || []}
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
    const conf = await this.confirm("Are you sure to delete this Domain?");
    if (conf)
      this.api.deleteDomain(type.id).then((res) => {
        if (res.state) this.getData();
        else this.alert(res.error);
      });
  };

  handleNewDomain = () => {
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
        title={mode == "new" ? "Add New Email" : "Edit Email"}
        onClose={this.hideModal}
        content={
          <div key="content" id="formData">
            <div className="flex-row flex-center">
              <div className="flex-row flex-center m-3">
                <label className="mr-1">Global </label>
                <Toggle
                  onChange={this.handleGlobalChange}
                  checked={data.global}
                ></Toggle>
              </div>
              <div className="flex-row flex-center m-3">
                <label className="mr-1">Customer Specific</label>
                <Toggle
                  onChange={this.handleGlobalChange}
                  checked={!data.global}
                ></Toggle>
              </div>
            </div>
            <div className="form-group">
              <label>Domain</label>
              <input
                value={data.domain}
                type="text"
                name=""
                id=""
                className="form-control required"
                required
                onChange={(event) =>
                  this.setValue("domain", event.target.value.replace(" ", ""))
                }
              />
            </div>
            <div
              className="form-group"
              style={{ display: data.global ? "none" : "", color: "black" }}
            >
              <label style={{ color: "white" }}>Customer</label>

              <CustomerSearch
                width={370}
                customerName={data.customerString}
                onChange={this.handleCustomerChange}
              ></CustomerSearch>
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
  handleCustomerChange = (customer) => {
    const { data } = this.state;
    data.customerID = customer.id;
    data.customerString = customer.name;
    this.setState({ data });
  };
  handleGlobalChange = () => {
    const { data } = this.state;
    data.global = !data.global;
    if (data.global) {
      data.customerID = null;
      data.customerString = "";
    }
    this.setState(data);
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
      this.api.addDomain(data).then(
        (result) => {
          this.setState({ showModal: false });
          this.getData();
        },
        (error) => {
          this.alert("Error in save data");
        }
      );
    } else if (mode == "edit") {
      this.api.updateDomain(data).then(
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
            onClick={this.handleNewDomain}
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

export default IgnoredADDomainsComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector(
    "#reactIgnoredADDomainsComponent"
  );
  if (domContainer)
    ReactDOM.render(
      React.createElement(IgnoredADDomainsComponent),
      domContainer
    );
});
