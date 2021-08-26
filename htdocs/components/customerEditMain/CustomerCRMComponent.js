import React from "react";
import { params } from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import Spinner from "../shared/Spinner/Spinner";
import Toggle from "../shared/Toggle";
import CNCCKEditor from "../shared/CNCCKEditor";
import APIUser from "./../services/APIUser";
import ToolTip from "./../shared/ToolTip";
import CustomerContactsComponent from './CustomerContactsComponent';
import CustomerNotesComponent from './CustomerNotesComponent';

export default class CustomerCRMComponent extends MainComponent {
  api = new APICustomers();
  apiUsers = new APIUser();

  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      customerId: null,
      leadStatuses: [],
      reset: false,
      showModal: false,
      isNew: true,
      data: { ...this.getInitData() },
      filter: {
        showInActive: false,
      },
      showSpinner: false,
      enginners: [],
    };
  }

  componentDidMount() {
    const customerId = params.get("customerID");
    this.getData();
    this.api.getLeadStatuses().then((res) => {
      this.setState({ leadStatuses: res.data });
    });
    this.api.getCustomerData(this.props.customerId).then(
      (data) => {
        this.setState({ data });
      },
      (error) => {
        this.alert("Error in get customer data");
      }
    );
    this.apiUsers.getActiveUsers().then((enginners) => {
      this.setState({ enginners });
    });
  }

  getData = () => {
    //this.setState({ showSpinner: true });
    //const customerId = params.get("customerID");
  };

  getInitData() {
    return {
      id: "",
      customerID: params.get("customerID"),
      leadStatusId: "",
      meetingDateTime: "",            
      opportunityDeal: "",
    };
  }

  handleUpdateGenericField = ($event) => {
    this.setValue($event.target.name, $event.target.value);
  };

  handleSave = () => {
    const { data } = this.state;

    this.api.updateCRM(data).then(
      (res) => {
        if (res.status) {
          this.alert("Data saved successfully");
          this.getCustomerData();
        } else this.alert("Data not saved successfully");
      },
      (error) => {
        this.alert("Data not saved successfully");
      }
    );
  };

  getCustomerData = () => {
    this.api.getCustomerData(this.props.customerId).then(
      (data) => {
        this.setState({ data });
      },
      (error) => {
        this.alert("Error in get customer data");
      }
    );
  };

  getLeadInformationCard = () => {
    const { data } = this.state;
    return (
      <div className="card m-5">
        <div className="card-header">
          <h3>Lead Information</h3>
        </div>
        <div className="card-body">
          <table className="table">
            <tbody>
              <tr>
                <td>
                  <div>Lead Status</div>
                </td>
                <td>
                  <select
                    required
                    value={data.leadStatusId}
                    onChange={(event) =>
                      this.setValue("leadStatusId", event.target.value)
                    }
                    className="form-control"
                  >
                    {this.state.leadStatuses.map((leadStatus, index) => {
                      return (
                        <option key={leadStatus.id} value={leadStatus.id}>
                          {leadStatus.name}
                        </option>
                      );
                    })}
                  </select>
                </td>
              </tr>

              <tr>
                <td>Meeting Date</td>
                <td>
                  <input
                    type="date"
                    value={data.meetingDateTime || ""}
                    onChange={($event) => this.handleUpdateGenericField($event)}
                    size="10"
                    maxLength="10"
                    className="form-control"
                    name="meetingDateTime"
                  />
                </td>
              </tr>
              <tr>
                <td>Next CRM Review</td>
                <td>
                  <input
                    className="form-control"
                    type="date"
                    value={data.reviewDate || ""}
                    name="reviewDate"
                    onChange={($event) => this.handleUpdateGenericField($event)}
                  ></input>
                </td>
              </tr>
              <tr>
                <td>Review By</td>
                <td>
                  <select
                    className="form-control"
                    value={data.reviewUserID}
                    name="reviewUserID"
                    onChange={($event) => this.handleUpdateGenericField($event)}
                  >
                    {this.state.enginners.map((e) => (
                      <option key={e.id} value={e.id}>
                        {e.name}
                      </option>
                    ))}
                  </select>
                </td>
              </tr>
              <tr>
                <td>Website URL</td>
                <td>
                  <div className="flex-row" style={{ alignItems: "center" }}>
                    <input
                      className="form-control"
                      value={data.websiteURL || ""}
                      name="websiteURL"
                      onChange={($event) =>
                        this.handleUpdateGenericField($event)
                      }
                    ></input>
                    {data.websiteURL ? (
                      <ToolTip title="Open website">
                        <i
                          className="fal fa-external-link pointer"
                          onClick={() => window.open(data.websiteURL, "_blank")}
                        ></i>
                      </ToolTip>
                    ) : null}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    );
  };

  getOpportunityDealCard = () => {
    const { data } = this.state;
    return (
      <div className="card m-5">
        <div className="card-header">
          <h3>Opportunity Deal</h3>
        </div>
        <div className="card-body">
          <CNCCKEditor
            width={800}
            height={130}
            value={data.opportunityDeal}
            onChange={(value) => this.setValue("opportunityDeal", value)}
          ></CNCCKEditor>
        </div>
      </div>
    );
  };

  getCards = () => {
    return (
      <div className="row" style={{ margin: 2 }}>
        <div  style={{width:400}}>{this.getLeadInformationCard()}</div>
        <div  >{this.getOpportunityDealCard()}</div>
      </div>
    );
  };
  getContacts=()=>{
    const { data } = this.state;
    
    return <div className="ml-5">
      <h3>Contacts</h3>
      <CustomerContactsComponent customerId={data.customerID} custom={true}></CustomerContactsComponent>
      </div>
    
  }
  getNotes=()=>{
    const { data } = this.state; 
    return <div className="ml-5">
      <h3>Notes</h3>
      <CustomerNotesComponent customerId={data.customerID} custom={true}></CustomerNotesComponent>
      </div> 
  }
  render() {
    if (this.state.showSpinner)
      return <Spinner show={this.state.showSpinner} />;

    return (
      <div>
        {this.getCards()}
        {this.getConfirm()}
        {this.getAlert()}
        <button onClick={this.handleSave} className="ml-5">
          Save
        </button>        
        {this.getContacts()}
        {this.getNotes()}
      </div>
    );
  }
}
