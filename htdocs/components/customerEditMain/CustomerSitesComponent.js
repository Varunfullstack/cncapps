import React from "react";
import { params } from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import ToolTip from "../shared/ToolTip";
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle";

export default class CustomerSitesComponent extends MainComponent {
  api = new APICustomers();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      customerId: null,
      sites: [],
      reset: false,
      showModal: false,
      isNew: true,
      showSpinner: true,
      data: { ...this.getInitData() },
      filter:{
        showInActive:false
      }
    };
  }
  componentDidMount() {
    this.getData();
  }
  getData = () => {
    this.setState({ showSpinner: true });
    const customerId = params.get("customerID");    
    this.api.getCustomerSites(customerId,this.state.filter.showInActive).then((sites) => {
      this.setState({ sites, customerId, showSpinner: false });
    });
  };
  getTable = () => {
    const columns = [
      {
        path: "add1",
        label: "Site Address",
        hdToolTip: "Site Address",
        sortable: true,
        width: 200,
      },
      {
        path: "town",
        label: "Town",
        hdToolTip: "Town",
        sortable: true,
        width: 150,
      },
      {
        path: "county",
        label: "Country",
        hdToolTip: "Country",
        sortable: true,
        width: 150,
      },
      {
        path: "phone",
        label: "Phone",
        hdToolTip: "Phone",
        sortable: true,
        width: 150,
      },
      {
        path: "what3Words",
        label: "What3Words",
        hdToolTip: "What3Words",
        icon: "pointer",
        sortable: true,
        width: 150,
      },
      {
        path: "activeFlag",
        label: "Active",
        hdToolTip: "Active",
        icon: "pointer",
        sortable: true,
        width: 150,
        content:(site)=><Toggle checked={site.activeFlag == 'Y'} onChange={()=>null}></Toggle>
      },
      {
        path: "edit",
        label: "",
        hdToolTip: "Edit site",
        //icon: "fal fa-2x fa-signal color-gray2 pointer",
        sortable: false,
        content: (site) =>
          this.getEditElement(site, () => this.handleEdit(site)),
      },
      {
        path: "delete",
        label: "",
        hdToolTip: "Delete site",
        //icon: "fal fa-2x fa-signal color-gray2 pointer",
        sortable: false,
        content: (site) =>
          this.getDeleteElement(
            site,
            () => this.handleDelete(site),
            site.isDeletable
          ),
      },
    ];
    return (
      <Table
        key="sites"
        pk="id"
        style={{ maxWidth: 1300 }}
        columns={columns}
        data={this.state.sites || []}
        search={true}
      ></Table>
    );
  };

  getInitData() {
    return {
      id: "",
      customerID: params.get("customerID"),
      add1: "",
      add2: "",
      add3: "",
      town: "",
      county: "",
      postcode: "",
      invoiceContactID: "",
      deliverContactID: "",
      debtorCode: "",
      sageRef: "",
      phone: "",
      maxTravelHours: "",
      activeFlag: "",
      what3Words: "",
    };
  }

  handleEdit = (site) => {
    //console.log("Edit Site", site);
    this.setState({ data: site, showModal: true, isNew: false });
  };

  handleDelete = async (site) => {
    //console.log("Delete site", site);
    if (await this.confirm("Are you sure you want to delete this site?")) {
      this.APICustomers.deleteCustomerSite(site.id).then((res) => {
        //console.log(res);
        this.getData();
      });
    }
  };

  handleNewItem = () => {
    this.setState({
      showModal: true,
      isNew: true,
      data: { ...this.getInitData() },
    });
  };

  getCheckBox = (name, yesNo = true) => {
    const { data } = this.state;
    let trueValue = "Y";
    let falseValue = "N";
    if (!yesNo) {
      trueValue = 1;
      falseValue = 0;
    }
    return (
      <input
        checked={data[name] == trueValue}
        onChange={() =>
          this.setValue(name, data[name] == trueValue ? falseValue : trueValue)
        }
        type="checkbox"
      />
    );
  };

  handleClose = () => {
    this.setState({ showModal: false });
  };

  handleSave = () => {
    const { data, isNew } = this.state;
    if (!this.isFormValid("siteformdata")) {
      this.alert("Please enter required data");
      return;
    }
    if (!isNew) {
      this.api.updateCustomerSite(data).then((res) => {
        if (res.status == 200) {
          this.setState({ showModal: false, reset: true }, () =>
            this.getData()
          );
        }
      });
    } else {
      data.id = null;
      this.api.addCustomerSite(data).then((res) => {
        if (res.status == 200) {
          this.setState({ showModal: false, reset: true }, () =>
            this.getData()
          );
        }
      });
    }
  };

  getModal = () => {
    const { isNew, showModal } = this.state;
    if (!showModal) return null;
    return (
      <Modal
        width={500}
        title={isNew ? "Create Site" : "Update Site"}
        show={showModal}
        content={this.getModalContent()}
        footer={
          <div key="footer">
            <button onClick={this.handleClose} className="btn btn-secodary">
              Cancel
            </button>
            <button onClick={this.handleSave}>Save</button>
          </div>
        }
        onClose={this.handleClose}
      ></Modal>
    );
  };

  getModalContent = () => {
    const { data } = this.state;
    return (
      <div key="content" id="siteformdata">
        <table className="table">
          <tbody>
            <tr>
              <td className="text-right">Site Address</td>
              <td>
                <input
                  required
                  value={data.add1 || ""}
                  onChange={(event) =>
                    this.setValue("add1", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td></td>
              <td>
                <input
                  required
                  value={data.add2 || ""}
                  onChange={(event) =>
                    this.setValue("add2", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right"></td>
              <td>
                <input
                  value={data.add3 || ""}
                  onChange={(event) =>
                    this.setValue("add3", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Town</td>
              <td>
                <input
                  required
                  value={data.town || ""}
                  onChange={(event) =>
                    this.setValue("town", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Country</td>
              <td>
                <input
                  required
                  value={data.county || ""}
                  onChange={(event) =>
                    this.setValue("county", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Postcode</td>
              <td>
                <input
                  required
                  value={data.postcode || ""}
                  onChange={(event) =>
                    this.setValue("postcode", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">What3Words</td>
              <td>
                <input
                  required
                  value={data.what3Words || ""}
                  onChange={(event) =>
                    this.setValue("what3Words", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Phone</td>
              <td>
                <input
                  required
                  value={data.phone || ""}
                  onChange={(event) =>
                    this.setValue("phone", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Max Travel Hours</td>
              <td>
                <input
                  required
                  value={data.maxTravelHours || ""}
                  onChange={(event) =>
                    this.setValue("maxTravelHours", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Active?</td>
              <td>
                <Toggle
                  checked={data.activeFlag === "Y"}
                  onChange={() =>
                    this.setValue(
                      "activeFlag",
                      data.activeFlag === "Y" ? "N" : "Y"
                    )
                  }
                ></Toggle>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    );
  };

  getFilter=()=>{
    const {filter}=this.state;
    return <div className="flex-row flex-center" style={{marginTop:-20}}>
            <label className="mr-3">Show InActive Sites</label>
            <Toggle checked={filter.showInActive} onChange={()=>this.handleFilterChange("showInActive",!filter.showInActive)}></Toggle>
          </div>
  }
  handleFilterChange=(prop,value)=>{    
     this.setFilter(prop,value,()=>this.getData());
  }

  render() {
    const arr = [
      { id: 1, name: "test 1" },
      { id: 2, name: "test 2" },
    ];
    if (this.state.showSpinner)
      return <Spinner show={this.state.showSpinner} />;
    return (
      <div>
        <div className="m-5">
          <ToolTip title="New Site" width={30}>
            <i
              className="fal fa-2x fa-plus color-gray1 pointer"
              onClick={this.handleNewItem}
            />
          </ToolTip>
        </div>
        {this.getConfirm()}
        {this.getAlert()}
        {this.getFilter()}
        {this.getTable()}
        <div className="modal-style">{this.getModal()}</div>
      </div>
    );
  }
}
