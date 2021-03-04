import MainComponent from "../../shared/MainComponent";
import React from "react";
import APICustomerInfo from "../services/APICustomerInfo";
import Spinner from "../../shared/Spinner/Spinner";
import "../../shared/table/table.css";
import { exportCSV, sort } from "../../utils/utils";
import CustomerSearch from "../../shared/CustomerSearch";
import Table from "../../shared/table/table";
import ToolTip from "../../shared/ToolTip";
class ContactAuditComponent extends MainComponent {
  api = new APICustomerInfo();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      filter: {
        customerID: "",
        firstName: "",
        lastName: "",
        from: moment().subtract(1, "month").format("YYYY-MM-DD"),
        to: moment().format("YYYY-MM-DD"),
        customerName:""
      },
    };
  }

  componentWillUnmount() {}
  componentDidMount() {}
  setFilter = (prop, value) => {
    const { filter } = this.state;
    filter[prop] = value;
    this.setState({ filter });
  };
  getData = async () => {
    this.setState({ showSpinner: true });
    const data = await this.api.getSpecialAttention();
    data.contacts.map((c) => {
      c.name = c.customerName + c.contactName;
      return c;
    });
    this.setState({
      showSpinner: false,
    });
  };
  handleCustomerSelect = (customer) => {
    const {filter}=this.state;
    filter.customerID=customer.id;
    filter.customerName=customer.name;
    this.setState({filter});
  };
  getSearchElement = () => {
    const { filter } = this.state;
    return (
      <table style={{ width: 500 }}>
        <tbody>
          <tr>
            <td>Customer</td>
            <td colSpan={3}>
              {" "}
              <CustomerSearch                
                customerName={filter.customerName}
                onChange={this.handleCustomerSelect}
                width={360}
              ></CustomerSearch>
            </td>
          </tr>
          <tr>
            <td>Contact First Name</td>
            <td>
              <input
                type="text"
                style={{ width: 132 }}
                value={filter.firstName}
                onChange={(event) =>
                  this.setFilter("firstName", event.target.value)
                }
              ></input>
            </td>
            <td>Last Name</td>
            <td>
              <input
                type="text"
                style={{ width: 132 }}
                value={filter.lastName}
                onChange={(event) =>
                  this.setFilter("lastName", event.target.value)
                }
              ></input>
            </td>
          </tr>
          <tr>
            <td>Created From</td>
            <td>
              <input
                type="date"
                value={filter.from}
                onChange={(event) => this.setFilter("from", event.target.value)}
              ></input>
            </td>
            <td>To</td>
            <td>
              <input
                type="date"
                value={filter.to}
                onChange={(event) => this.setFilter("to", event.target.value)}
              ></input>
            </td>
          </tr>
          <tr>
            <td colSpan={3}  >
              <div style={{display:"flex",justifyContent:"center"}}>
              <ToolTip title="Search">
                <i
                  className="fal fa-search fa-2x icon m-5 pointer"
                  onClick={this.handleSearch}
                ></i>
              </ToolTip>

              <ToolTip title="Export Results to CSV file">
                <i
                  className="fal fa-file-csv fa-2x icon m-5 pointer"
                  onClick={this.handleExportExcel}
                ></i>
              </ToolTip>
              <ToolTip title="Clear Inputs">
                <i
                  className="fal fa-sync fa-2x icon m-5 pointer"
                  onClick={this.handleClear}
                ></i>
              </ToolTip>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    );
  };
  handleSearch = () => {
    const { filter } = this.state;
    if (filter.from == "" && filter.to == "") {
      this.alert("Please select the interval");
      return;
    }    
    this.setState({ showSpinner: true });
    this.api.searchContactAudit(filter).then((data) => {
      this.setState({ data: sort(data, "createdByUserName"),showSpinner: false });
    });
  };
  handleClear=()=>{
    const { filter } = this.state;
    filter.firstName='';
    filter.lastName='';
    filter.from='';
    filter.to='';
    filter.customerID='';
    filter.customerName='';
    this.setState({filter})
  }
  getDataElement = () => {
    const { data } = this.state;
    const columns = [
      {
        path: "createdByUserName",
        label: "CNC Modifer",
        hdToolTip: "CNC Modifer",
        hdClassName: "text-center",
        sortable: true,
        className: "text-center",
      },
      {
        path: "createdByContactName",
        label: "Customer Modifier",
        sortable: true,
        className: "text-center",
      },
      {
        path: "action",
        label: "Action",
        sortable: true,
        className: "text-center",
      },
      {
        path: "createdAt",
        label: "Change Data",
        sortable: true,
        className: "text-center",
      },
      {
        path: "customerName",
        label: "Customer",
        sortable: true,
        className: "text-center",
      },
      {
        path: "title",
        label: "Title",
        sortable: true,
        className: "text-center",
      },
      {
        path: "firstName",
        label: "First Name",
        sortable: true,
        className: "text-center",
      },
      {
        path: "lastName",
        label: "Last Name",
        sortable: true,
        className: "text-center",
      },
      {
        path: "email",
        label: "Email",
        sortable: true,
        className: "text-center",
      },
      {
        path: "contactID",
        label: "Contact ID",
        sortable: true,
        className: "text-center",
      },
      {
        path: "siteNo",
        label: "Site No",
        sortable: true,
        className: "text-center",
      },
      {
        path: "supportLevel",
        label: "Support Level",
        sortable: true,
        className: "text-center",
      },
      {
        path: "position",
        label: "Position",
        sortable: true,
        className: "text-center",
      },
      {
        path: "phone",
        label: "Phone",
        sortable: true,
        className: "text-center",
      },
      {
        path: "mobilePhone",
        label: "Mobile",
        sortable: true,
        className: "text-center",
      },
      {
        path: "sendMailshotFlag",
        label: "Mailshot",
        sortable: true,
        className: "text-center",
      },
      {
        path: "mailshot2Flag",
        label: "Invoice",
        sortable: true,
        className: "text-center",
      },
      {
        path: "mailshot3Flag",
        label: "Newsletter",
        sortable: true,
        className: "text-center",
      },
      {
        path: "mailshot4Flag",
        label: "Statement",
        sortable: true,
        className: "text-center",
      },
      {
        path: "mailshot8Flag",
        label: "Top Up",
        sortable: true,
        className: "text-center",
      },
      {
        path: "mailshot9Flag",
        label: "Reports",
        sortable: true,
        className: "text-center",
      },
      {
        path: "mailshot11Flag",
        label: "SR Reports",
        sortable: true,
        className: "text-center",
      },
      {
        path: "notes",
        label: "Notes",
        sortable: true,
        className: "text-center",
      },
      {
        path: "workStartedEmailFlag",
        label: "Work Started",
        sortable: true,
        className: "text-center",
      },
      {
        path: "initialLoggingEmailFlag",
        label: "initialLoggingEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "workUpdatesEmailFlag",
        label: "workUpdatesEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "fixedEmailFlag",
        label: "fixedEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "pendingClosureEmailFlag",
        label: "pendingClosureEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "closureEmailFlag",
        label: "closureEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "othersInitialLoggingEmailFlag",
        label: "othersInitialLoggingEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "othersWorkStartedEmailFlag",
        label: "othersWorkStartedEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "othersWorkUpdatesEmailFlag",
        label: "othersWorkUpdatesEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "othersFixedEmailFlag",
        label: "othersFixedEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "othersPendingClosureEmailFlag",
        label: "othersPendingClosureEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "othersClosureEmailFlag",
        label: "othersClosureEmailFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "pendingLeaverFlag",
        label: "pendingLeaverFlag",
        sortable: true,
        className: "text-center",
      },
      {
        path: "pendingLeaverDate",
        label: "pendingLeaverDate",
        sortable: true,
        className: "text-center",
      },
      {
        path: "pendingFurloughAction",
        label: "pendingFurloughAction",
        sortable: true,
        className: "text-center",
      },
      {
        path: "pendingFurloughActionDate",
        label: "pendingFurloughActionDate",
        sortable: true,
        className: "text-center",
      },
      {
        path: "pendingFurloughActionLevel",
        label: "pendingFurloughActionLevel",
        sortable: true,
        className: "text-center",
      },
    ];
    return (
      <Table
        id="audit"
        data={data || []}
        columns={columns}
        pk="contactID"
        search={true}
      ></Table>
    );
  };
  handleExportExcel=()=>{
    const { data } = this.state;
    exportCSV(data,'ContictAudit.csv')
  }
  render() {
    return (
      <div>
        {this.getAlert()}
        <Spinner key="spinner" show={this.state.showSpinner}></Spinner>
        <div
          style={{
            display: "flex",
            flexDirection: "column",
            justifyContent: "space-between",
            maxWidth: 1300,
          }}
        >
          {this.getSearchElement()}
          {this.getDataElement()}
        </div>
      </div>
    );
  }
}

export default ContactAuditComponent;
