import React from "react";
import {params} from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import Spinner from "../shared/Spinner/Spinner";
import Toggle from "../shared/Toggle";
import CNCCKEditor from "../shared/CNCCKEditor";

export default class CustomerCRMComponent extends MainComponent {
    api = new APICustomers();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            customerId: null,
            leadStatuses: [],
            reset: false,
            showModal: false,
            isNew: true,
            data: {...this.getInitData()},
            filter: {
                showInActive: false,
            },
            showSpinner: false,
        };
    }

    componentDidMount() {
        const customerId = params.get("customerID");
        this.getData();
        this.api.getLeadStatuses().then((res) => {
            this.setState({leadStatuses: res.data});
        });
        this.api.getCustomerData(this.props.customerId).then(data => {
            this.setState({data})
        }, error => {
            this.alert("Error in get customer data");
            console.log(error);
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
            mailshotFlag: "",
            dateMeetingConfirmed: "",
            meetingDateTime: "",
            inviteSent: "",
            reportProcessed: "",
            reportSent: "",
            rating: "",
            opportunityDeal: "",
        };
    }


    handleUpdateGenericField = ($event) => {
        this.setValue($event.target.name, $event.target.value);
    }

    handleSave = () => {
        const {data} = this.state;

        this.api.updateCRM(data).then(res => {
            if (res.status) {
                this.alert("Data saved successfully");
                this.getCustomerData();
            } else
                this.alert("Data not saved successfully");
        }, error => {
            console.log(error);
            this.alert("Data not saved successfully");
        })
    }

    getCustomerData = () => {
        this.api.getCustomerData(this.props.customerId).then(data => {
            this.setState({data})
        }, error => {
            this.alert("Error in get customer data");
            console.log(error);
        });
    }

    getLeadInformationCard = () => {
        const {data} = this.state;
        return <div className="card m-5">
            <div className="card-header">
                <h3>Lead Information</h3>
            </div>
            <div className="card-body">
                <table>
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
                        <td>
                            <div>Email Opt Out</div>
                        </td>
                        <td>
                            <Toggle
                                checked={data.mailshotFlag === 1}
                                onChange={() =>
                                    this.setValue(
                                        "mailshotFlag",
                                        data.mailshotFlag === 1 ? 0 : 1
                                    )
                                }
                            ></Toggle>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Meeting Confirmed
                        </td>
                        <td>
                            <input
                                type="date"
                                value={data.dateMeetingConfirmed || ""}
                                onChange={($event) =>
                                    this.handleUpdateGenericField($event)
                                }
                                size="10"
                                maxLength="10"
                                className="form-control input-sm"
                                name="dateMeetingConfirmed"
                                style={{maxWidth: 170}}
                            />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Meeting Date
                        </td>
                        <td>
                            <input
                                type="datetime-local"
                                value={data.meetingDateTime || ""}
                                onChange={($event) =>
                                    this.handleUpdateGenericField($event)
                                }
                                size="10"
                                maxLength="10"
                                className="form-control input-sm"
                                name="meetingDateTime"
                                style={{maxWidth: 170}}
                            />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Invite Sent</div>
                        </td>
                        <td>
                            <Toggle
                                checked={data.inviteSent === 1}
                                onChange={() =>
                                    this.setValue(
                                        "inviteSent",
                                        data.inviteSent === 1 ? 0 : 1
                                    )
                                }
                            ></Toggle>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Report Processed</div>
                        </td>
                        <td>
                            <Toggle
                                checked={data.reportProcessed === 1}
                                onChange={() =>
                                    this.setValue(
                                        "reportProcessed",
                                        data.reportProcessed === 1 ? 0 : 1
                                    )
                                }
                            ></Toggle>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Report Sent</div>
                        </td>
                        <td>
                            <Toggle
                                checked={data.reportSent === 1}
                                onChange={() =>
                                    this.setValue(
                                        "reportSent",
                                        data.reportSent === 1 ? 0 : 1
                                    )
                                }
                            ></Toggle>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>Rating</div>
                        </td>
                        <td>
                            <input
                                value={data.rating || ""}
                                onChange={(event) =>
                                    this.setValue("rating", event.target.value)
                                }
                                className="form-control"
                            />
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    }

    getOpportunityDealCard = () => {
        const {data} = this.state;
        return <div className="card m-5">
            <div className="card-header">
                <h3>Opportunity Deal</h3>
            </div>
            <div className="card-body">
                <CNCCKEditor
                    width={900}
                    height={180}
                    value={data.opportunityDeal}
                    onChange={(value) =>
                        this.setValue("opportunityDeal", value)
                    }
                >
                </CNCCKEditor>

            </div>
        </div>
    }

    getCards = () => {

        return <div className="row" style={{margin: 2}}>
            <div className="col-md-6">
                {this.getLeadInformationCard()}
            </div>
            <div className="col-md-6">
                {this.getOpportunityDealCard()}
            </div>
        </div>
    }

    render() {
        if (this.state.showSpinner)
            return <Spinner show={this.state.showSpinner}/>;

        return (
            <div>
                {this.getCards()}
                {this.getConfirm()}
                {this.getAlert()}
                <button onClick={this.handleSave} className="ml-5">Save</button>
            </div>
        );
    }
}
