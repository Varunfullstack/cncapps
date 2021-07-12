import React from "react";
import { params } from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import ToolTip from "../shared/ToolTip";
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle";
import { Fragment } from "react";

export default class CustomerContactsComponent extends MainComponent {
  api = new APICustomers();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      customerId: null,
      contacts: [],
      contactsFiltered: [],
      sites: [],
      letters:[],
      reset: false,
      showModal: false,
      isNew: true,
      data: { ...this.getInitData() },
      filter: {
        showInActive: false,
      },
      showSpinner: true,
      supportLevelOptions: [
        "main",
        "supervisor",
        "support",
        "delegate",
        "furlough",
      ],
    };
  }
  componentDidMount() {
    const customerId = params.get("customerID");
    this.getData();
    this.api.getCustomerSites(customerId, "Y").then((sites) => {
        this.setState({ sites, customerId });
      });
    this.api.getLetters().then(res=>{
        console.log("letters",res);
        this.setState({letters:res.data});
    })
  }
  getData = () => {
    this.setState({ showSpinner: true });
    const customerId = params.get("customerID");
    this.api.getCustomerContacts(customerId).then((contacts) => {
      this.setState({ contacts, customerId, showSpinner: false }, () =>
        this.applyFilter()
      );
    });
    
  };

  applyFilter = () => {
    const { filter, contacts } = this.state;
    const contactsFiltered = filter.showInActive
      ? contacts
      : contacts.filter((c) => c.active == 1);
    this.setState({ contactsFiltered });
  };

  getSiteTitle = (siteNo) => {
    const { sites } = this.state;
    const site = sites.find((s) => s.id == siteNo);
    if (site) return site.add1;
    else return "";
  };
  getTable = () => {
    const columns = [
      {
        path: "title",
        label: "Title",
        hdToolTip: "Title",
        sortable: true,
        width: 50,
      },
      {
        path: "firstName",
        label: "Name",
        hdToolTip: "Name",
        sortable: true,
        content: (contact) => contact.firstName + " " + contact.lastName,
      },

      {
        path: "siteNo",
        label: "Site",
        hdToolTip: "Site",
        sortable: true,
        content: (contact) => this.getSiteTitle(contact.siteNo),
      },
      {
        path: "position",
        label: "Position",
        hdToolTip: "Position",
        sortable: true,
      },
      {
        path: "email",
        label: "Email",
        hdToolTip: "Email",
        sortable: true,
      },
      {
        path: "phone",
        label: "Phone",
        hdToolTip: "Phone",
        icon: "pointer",
        sortable: true,
      },
      {
        path: "mobilePhone",
        label: "Mobile",
        hdToolTip: "Mobile",
        icon: "pointer",
        sortable: true,
      },
      {
        path: "supportLevel",
        label: "Support Level",
        hdToolTip: "Support Level",
        icon: "pointer",
        sortable: true,

        content: (contact) => this.capitalizeFirstLetter(contact.supportLevel),
      },
      {
        path: "active",
        label: "Active",
        hdToolTip: "",
        icon: "pointer",
        sortable: true,

        content: (contact) => (
          <Toggle checked={contact.active} onChange={() => null}></Toggle>
        ),
      },

      {
        path: "edit",
        label: "",
        hdToolTip: "Edit contact",
        //icon: "fal fa-2x fa-signal color-gray2 pointer",
        sortable: false,
        content: (contact) =>
          this.getEditElement(contact, () => this.handleEdit(contact)),
      },
      {
        path: "delete",
        label: "",
        hdToolTip: "Delete contact",
        //icon: "fal fa-2x fa-signal color-gray2 pointer",
        sortable: false,
        content: (contact) =>
          this.getDeleteElement(
            contact,
            () => this.handleDelete(contact),
            contact.isDeletable
          ),
      },
    ];
    return (
      <Table
        key="contacts"
        pk="id"
        columns={columns}
        data={this.state.contactsFiltered || []}
        search={true}
      ></Table>
    );
  };

  capitalizeFirstLetter(string) {
    if (string != null) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    }
    return "";
  }

  getInitData() {
    return {
      id: "",
      customerID: params.get("customerID"),
      title: "",
      position: "",
      firstName: "",
      lastName: "",
      email: "",
      phone: "",
      mobilePhone: "",
      fax: "",
      portalPassword: "",
      mailshot: "",
      mailshot2Flag: "",
      mailshot3Flag: "",
      mailshot8Flag: "",
      mailshot9Flag: "",
      mailshot11Flag: "",
      notes: "",
      failedLoginCount: "",
      reviewUser: "",
      hrUser: "",
      supportLevel: "",
      initialLoggingEmail: "",
      othersInitialLoggingEmailFlag: "",
      othersWorkUpdatesEmailFlag: "",
      othersFixedEmailFlag: "",
      pendingLeaverFlag: "",
      pendingLeaverDate: "",
      specialAttentionContactFlag: "",
      linkedInURL: "",
      pendingFurloughAction: "",
      pendingFurloughActionDate: "",
      pendingFurloughActionLevel: "",
      siteNo: "",
      active: "",
    };
  }

  handleEdit = (contact) => {
    //contact['customerID'] = params.get("customerID");
    console.log("Edit Contact", contact);
    this.setState({ data: contact, showModal: true, isNew: false });
  };

  handleDelete = async (contact) => {
    console.log("Delete contact", contact);
    if (await this.confirm("Are you sure you want to delete this contact?")) {
      this.api.deleteCustomerContact(contact.id).then((res) => {
        console.log(res);
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
    console.log(data);
    if (!this.isFormValid("contactformdata")) {
      this.alert("Please enter required data");
      return;
    }
    if (!isNew) {
      this.api.updateCustomerContact(data).then((res) => {
        if (res.status == 200) {
          this.setState({ showModal: false, reset: true }, () =>
            this.getData()
          );
        }
      });
    } else {
      data.id = null;
      this.api.addCustomerContact(data).then((res) => {
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
        width={800}
        title={isNew ? "Create Contact" : "Update Contact"}
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
      <div key="content" id="contactformdata">
        <table className="table">
          <tbody>
            <tr>
              <td className="text-right">Site</td>
              <td>
                <select
                  required
                  value={data.siteNo}
                  onChange={(event) =>
                    this.setValue("siteNo", event.target.value)
                  }
                  className="form-control"
                >
                  {this.state.sites.map((site, index) => {
                    return (
                      <option key={site.id} value={site.id}>
                        {site.add1}
                      </option>
                    );
                  })}
                </select>
              </td>
              <td className="text-right">Title</td>
              <td>
                <input
                  required
                  value={data.title || ""}
                  onChange={(event) =>
                    this.setValue("title", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">First</td>
              <td>
                <input
                  required
                  value={data.firstName || ""}
                  onChange={(event) =>
                    this.setValue("firstName", event.target.value)
                  }
                  className="form-control"
                />
              </td>
              <td className="text-right">Last</td>
              <td>
                <input
                  required
                  value={data.lastName || ""}
                  onChange={(event) =>
                    this.setValue("lastName", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Position</td>
              <td>
                <input
                  value={data.position || ""}
                  onChange={(event) =>
                    this.setValue("position", event.target.value)
                  }
                  className="form-control"
                />
              </td>
              <td className="text-right">Email</td>
              <td>
                <input
                  required
                  value={data.email || ""}
                  onChange={(event) =>
                    this.setValue("email", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Phone</td>
              <td>
                <input
                  value={data.phone || ""}
                  onChange={(event) =>
                    this.setValue("phone", event.target.value)
                  }
                  className="form-control"
                />
              </td>
              <td className="text-right">Mobile</td>
              <td>
                <input
                  value={data.mobilePhone || ""}
                  onChange={(event) =>
                    this.setValue("mobilePhone", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Linkedin</td>
              <td>
                <input
                  value={data.linkedInURL || ""}
                  onChange={(event) =>
                    this.setValue("linkedInURL", event.target.value)
                  }
                  className="form-control"
                />
              </td>
              <td className="text-right">Notes</td>
              <td>
                <input
                  value={data.notes || ""}
                  onChange={(event) =>
                    this.setValue("notes", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right"> Pending Leaver Date</td>
              <td>
                <input
                  type="date"
                  value={data.pendingLeaverDate || ""}
                  onChange={(event) =>
                    this.setValue("pendingLeaverDate", event.target.value)
                  }
                  className="form-control"
                />
              </td>
              <td className="text-right">Support Level</td>
              <td>
                <select
                  required
                  value={data.supportLevel}
                  onChange={(event) =>
                    this.setValue("supportLevel", event.target.value)
                  }
                  className="form-control"
                >
                  {this.state.supportLevelOptions.map((level) => {
                    return (
                      <option key={level} value={level}>
                        {level.replace(/^(.)|\s(.)/g, (x) => x.toUpperCase())}
                      </option>
                    );
                  })}
                </select>
              </td>
            </tr>
            <tr>
              {this.getYNFlag(
                "Special Attention",
                "specialAttentionContactFlag"
              )}
              {this.getYNFlag("Company Information Reports", "mailshot9Flag")}
            </tr>
            <tr>
              {this.getYNFlag("Daily SR Reports", "mailshot11Flag")}
              {this.getYNFlag("Newsletter", "mailshot3Flag")}
            </tr>
            <tr>
              {this.getYNFlag(
                "Send Others Initial Logging Email?",
                "othersInitialLoggingEmailFlag"
              )}
              {this.getYNFlag("Mailshot", "sendMailshotFlag")}
            </tr>
            <tr>
              {this.getYNFlag(
                "Send Others Fixed Email?",
                "othersFixedEmailFlag"
              )}
              {this.getYNFlag("Accounts", "accountsFlag")}
            </tr>
            <tr>
              {this.getYNFlag("Pending Leaver", "pendingLeaverFlag")}
              {this.getYNFlag("PrePay TopUp Notifications", "mailshot8Flag")}
            </tr>
            <tr>
              {this.getYNFlag("Active", "activeFlag")}
              {this.getYNFlag("Receive Invoices", "mailshot2Flag")}
            </tr>
            <tr>
              {this.getYNFlag("Attends Review Meeting", "reviewUser")}
              {this.getYNFlag("Receive Statements", "mailshot4Flag")}
            </tr>
            <tr>{this.getYNFlag("HR User to edit contacts", "hrUser")}</tr>
          </tbody>
        </table>
      </div>
    );
  };
  getYNFlag = (label, prop) => {
    const { data } = this.state;
    return (
      <Fragment>
        <td  className="text-right">{label}</td>
        <td>
          <Toggle
            checked={data[prop] === "Y"}
            onChange={() => this.setValue(prop, data[prop] === "Y" ? "N" : "Y")}
          ></Toggle>
        </td>
      </Fragment>
    );
  };
  getFilter = () => {
    const { filter } = this.state;
    return (
      <div className="flex-row flex-center" style={{ marginTop: -30 }}>
        <label className="mr-3">Show Inactive</label>
        <Toggle
          checked={filter.showInActive}
          onChange={() =>
            this.handleFilterChange("showInActive", !filter.showInActive)
          }
        ></Toggle>
      </div>
    );
  };
  handleFilterChange = (prop, value) => {
    this.setFilter(prop, value, () => this.applyFilter());
  };
  calcSummary=(level)=>{
    const {contacts}=this.state;
    return contacts.filter(c=>c.supportLevel==level).length;
  }
  getSummaryElement=()=>{

      return <table className="table" style={{maxWidth:500}}>
          <thead>
              <tr>
                  <th>Main</th>
                  <th>Supervisor</th>
                  <th>Support</th>
                  <th>Delegate</th>
                  <th>Furlough</th>
                  <th>No Level</th>
                  <th>Total</th>
              </tr>
          </thead>
          <tbody>
              <tr>
                  <td className="text-center">{this.calcSummary('main')}</td>
                  <td className="text-center">{this.calcSummary('supervisor')}</td>
                  <td className="text-center">{this.calcSummary('support')}</td>
                  <td className="text-center">{this.calcSummary('delegate')}</td>
                  <td className="text-center">{this.calcSummary('furlough')}</td>
                  <td className="text-center">{this.calcSummary(null)}</td>
                  <td className="text-center">{this.state.contacts.length}</td>
              </tr>
          </tbody>
      </table>
  }
  handleClearSupportLevel=async ()=>{
    if(await this.confirm("This is will change all contacts to the support level of None & refer the customer. Please confirm that you want to continue."))
    {
        // clear support level
        this.api.removeSupportAndRefer(this.state.customerId).then(res=>{
            this.getData();
        })
    }
  }
  render() {
    if (this.state.showSpinner)
      return <Spinner show={this.state.showSpinner} />;
    return (
      <div>
        <div className="flex-row m-5">
            <button onClick={this.handleClearSupportLevel}>Clear Support Level</button>
          <ToolTip title="New Item" width={30}>
            <i
              className="fal fa-2x fa-plus color-gray1 pointer"
              onClick={this.handleNewItem}
            />
          </ToolTip>
        </div>
        {this.getConfirm()}
        {this.getAlert()}
        {this.getFilter()}
        {this.getSummaryElement()}
        {this.getTable()}
        <div className="modal-style">{this.getModal()}</div>
      </div>
    );
  }
}
