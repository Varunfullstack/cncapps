"use strict";
import React from 'react';
import EncryptedTextInput from "./EncryptedTextInput";
import APICustomers from '../services/APICustomers';
import Toggle from '../shared/Toggle';
import MainComponent from '../shared/MainComponent';
import APIUser from '../services/APIUser';
import APILeadStatusTypes from '../LeadStatusTypes/services/APILeadStatusTypes';
import {Pages} from '../utils/utils';
import moment from 'moment';
import ToolTip from '../shared/ToolTip';

export default class CustomerEditMain extends MainComponent {

    api = new APICustomers();
    apiUsers = new APIUser()
    apiLeadStatusTypes = new APILeadStatusTypes();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            loaded: false,
            data: {
                accountManagerUserID: null,
                accountName: "",
                accountNumber: "",
                activeDirectoryName: "",
                becameCustomerDate: "",
                customerID: null,
                customerTypeID: "",
                deliverSiteNo: null,
                droppedCustomerDate: null,
                eligiblePatchManagement: null,
                excludeFromWebrootChecks: 0,
                gscTopUpAmount: null,
                inclusiveOOHCallOuts: 0,

                invoiceSiteNo: "",
                lastContractSent: null,
                lastReviewMeetingDate: "",
                lastUpdatedDateTime: "",
                leadStatusId: null,
                meetingDateTime: null,
                modifyDate: moment().format("YYYY-MM-DD"),
                name: "",
                noOfPCs: null,
                noOfServers: null,
                opportunityDeal: null,
                primaryMainContactID: null,
                referredFlag: 0,
                regNo: "",
                reviewAction: "",
                reviewDate: "",
                reviewMeetingBooked: 0,
                reviewMeetingFrequencyMonths: null,

                sectorID: null,
                slaFixHoursP1: null,
                slaFixHoursP2: null,
                slaFixHoursP3: null,
                slaFixHoursP4: null,
                slaP1: null,
                slaP1PenaltiesAgreed: 0,
                slaP2: null,
                slaP2PenaltiesAgreed: 0,
                slaP3: null,
                slaP3PenaltiesAgreed: 0,
                slaP4: null,
                slaP5: null,
                sortCode: "",
                specialAttentionEndDate: "",
                specialAttentionFlag: "N",
                statementContactId: null,
                support24HourFlag: "N",
                techNotes: "",
                websiteURL: ""
            },
            originData: null,
            contacts: [],
            users: [],
            customerTypes: [],
            sectors: [],
            leadStatus: [],
            sites: [],
        };
    }

    componentDidMount() {
        if (this.props.customerId) {
            this.getCustomerData();
            this.api.getCustomerContacts(this.props.customerId).then(contacts => {
                this.setState({contacts});
                if (contacts.length == 0) {
                    this.alert("Please add contacts");
                    setTimeout(() => {
                        window.location = `Customer.php?action=dispEdit&customerID=${this.props.customerId}&activeTab=contacts`;
                    }, 1000)
                }
            });

        }
        this.apiUsers.getActiveUsers().then(users => {
            this.setState({users})
        });
        this.api.getCustomerTypes().then(customerTypes => {
            this.setState({customerTypes})
        })
        this.api.getCustomerSectors().then(sectors => {
            this.setState({sectors})
        })
        this.apiLeadStatusTypes.getAllTypes().then(leadStatus => {
            this.setState({leadStatus})
        })

    }

    getCustomerData = () => {
        Promise.all([
                this.api.getCustomerSites(this.props.customerId, false),
                this.api.getCustomerData(this.props.customerId)
            ]
        ).then(([sites, data]) => {
            this.setState({data, originData: {...data}, sites})
        }, error => {
            this.alert("Error in get customer data");
        });
    }
    updateCustomerField = (field, value) => {
        this.setValue(field, value);
    }

    handleFlagUpdate($event) {
        this.setValue($event.target.name, $event.target.checked ? "Y" : "N");
    }

    handleUpdateGenericField = ($event) => {
        this.setValue($event.target.name, $event.target.value);
    }

    getKeyDetailsCard = () => {
        const {data, sites} = this.state;
        return (
            <div className="card m-5">
                <div className="card-header">
                    <h3>Key Details</h3>
                </div>
                <div className="card-body">
                    <div>
                        <table>
                            <tbody>
                            <tr>
                                <td className="text-align-right">
                                    Customer {data.customerID}
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        onChange={($event) =>
                                            this.handleUpdateGenericField($event)
                                        }
                                        value={data.name || ""}
                                        size="50"
                                        maxLength="50"
                                        name="name"
                                        className="form-control "
                                        required
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td className="text-align-right">Primary Main Contact</td>
                                <td>
                                    <select
                                        name="primaryMainContactID"
                                        className="form-control "
                                        value={data.primaryMainContactID || ""}
                                        onChange={($value) =>
                                            this.setValue(
                                                "primaryMainContactID",
                                                $value.target.value
                                            )
                                        }
                                    >
                                        {this.state.contacts
                                            .filter((contact) => contact.supportLevel == "main")
                                            .map((contact) => (
                                                <option key={contact.id} value={contact.id}>
                                                    {contact.firstName + " " + contact.lastName}
                                                </option>
                                            ))}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td className="text-align-right">Statement Contact</td>
                                <td>
                                    <select
                                        name="statementContactId"
                                        className="form-control "
                                        value={data.statementContactId | ""}
                                        onChange={($value) =>
                                            this.setValue(
                                                "statementContactId",
                                                $value.target.value
                                            )
                                        }
                                    >
                                        {this.state.contacts.filter(c => c.active == 1).map((contact) => (
                                            <option key={contact.id} value={contact.id}>
                                                {contact.firstName + " " + contact.lastName}
                                            </option>
                                        ))}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td className="text-align-right">Default Invoice Site</td>
                                <td>
                                    <div className="flex-row pointer" style={{alignItems: "center"}}>
                                        <select value={data.invoiceSiteNo?.toString() || ""}
                                                name="invoiceSiteNo"
                                                className="form-control "
                                                onChange={event => this.handleUpdateGenericField(event)}>
                                            <option value="" key="emptyOption">-- Select Site --</option>
                                            {
                                                sites.map(site => <option key={site.id}
                                                                          value={site.id}>{site.title}</option>)
                                            }
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td className="text-align-right">Default Delivery Site</td>
                                <td>
                                    <div className="flex-row pointer" style={{alignItems: "center"}}>
                                        <select value={data.deliverSiteNo?.toString() || ""}
                                                className="form-control "
                                                onChange={event => this.handleUpdateGenericField(event)}
                                                name="deliverSiteNo">
                                            <option value="" key="emptyOption">-- Select Site --</option>
                                            {
                                                sites.map(site => <option key={site.id}
                                                                          value={site.id}>{site.title}</option>)
                                            }
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td className="text-align-right">Website</td>
                                <td>
                                    <div className="flex-row pointer" style={{alignItems: "center"}}>
                                        <input className="form-control" value={data.websiteURL}
                                               onChange={($value) =>
                                                   this.setValue(
                                                       "websiteURL",
                                                       $value.target.value
                                                   )
                                               }
                                        ></input>
                                        {data.websiteURL ?
                                            <ToolTip title="Open website">
                                                <i className="fal fa-external-link pointer"
                                                   onClick={() => window.open(data.websiteURL, "_blank")}></i>
                                            </ToolTip> : null}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td className="text-align-right">Referred</td>
                                <td>
                                    <div
                                        className="flex-row "
                                        style={{justifyContent: "space-between", width: 150}}
                                    >
                                        <Toggle
                                            checked={data.referredFlag}
                                            onChange={() =>
                                                this.setValue(
                                                    "referredFlag",
                                                    (!data.referredFlag * 1)
                                                )
                                            }
                                        ></Toggle>
                                        <div className="flex-row flex-center ">
                                            <span className="mr-2"> 24 Hour Cover </span>
                                            <Toggle
                                                checked={data.support24HourFlag === "Y"}
                                                onChange={() =>
                                                    this.setValue(
                                                        "support24HourFlag",
                                                        data.support24HourFlag === "Y" ? "N" : "Y"
                                                    )
                                                }
                                            ></Toggle>
                                        </div>

                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td className="text-align-right">Special Attention</td>
                                <td>
                                    <div className="flex-row  " style={{alignItems: "center"}}>

                                        <Toggle
                                            checked={data.specialAttentionFlag === "Y"}
                                            onChange={() =>
                                                this.setValue(
                                                    "specialAttentionFlag",
                                                    data.specialAttentionFlag === "Y" ? "N" : "Y"
                                                )
                                            }
                                        ></Toggle>

                                        <span className="mr-2 ml-5"> Until </span>
                                        <input
                                            type="date"
                                            value={data.specialAttentionEndDate || ""}
                                            size="10"
                                            maxLength="10"
                                            className="form-control "
                                            onChange={($event) =>
                                                this.handleUpdateGenericField($event)
                                            }
                                            name="specialAttentionEndDate"
                                            style={{maxWidth: 170}}
                                        />
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        );
    }
    getReviewMeetingCard = () => {
        const {data} = this.state;
        return (
            <div className="card m-5">
                <div className="card-header">
                    <h3>Review Meetings</h3>
                </div>
                <div className="card-body">
                    <table>
                        <tbody>
                        <tr>
                            <td align="right">Last Review Meeting</td>
                            <td>

                                <input
                                    type="date"
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    value={data.lastReviewMeetingDate || ""}
                                    size="10"
                                    maxLength="10"
                                    className="form-control "
                                    name="lastReviewMeetingDate"
                                />
                            </td>

                            <td align="right">Frequency</td>
                            <td>
                                <select
                                    className="form-control "
                                    name="reviewMeetingFrequencyMonths"
                                    value={data.reviewMeetingFrequencyMonths || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                >
                                    <option value="1">Monthly</option>
                                    <option value="2">Two Monthly</option>
                                    <option value="3">Quarterly</option>
                                    <option value="6">Six-Monthly</option>
                                    <option value="12">Annually</option>
                                </select>
                            </td>
                            <td align="right">Booked</td>
                            <td>
                                <Toggle
                                    checked={data.reviewMeetingBooked}
                                    onChange={() =>
                                        this.setValue(
                                            "reviewMeetingBooked",
                                            !data.reviewMeetingBooked
                                        )
                                    }
                                ></Toggle>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        );
    }
    getAccountsCard = () => {
        const {data, users, leadStatus} = this.state;
        return (
            <div className="card m-5">
                <div className="card-header">
                    <h3>Accounts</h3>
                </div>
                <div className="card-body">
                    <table>
                        <tbody>
                        <tr>
                            <td align="right">Account Manager</td>
                            <td>
                                <select
                                    className="form-control "
                                    name="accountManagerUserID"
                                    value={data.accountManagerUserID || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                >
                                    {users.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name}
                                        </option>
                                    ))}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Became Customer</td>
                            <td>
                                <input
                                    type="date"
                                    value={data.becameCustomerDate || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    size="10"
                                    maxLength="10"
                                    className="form-control "
                                    name="becameCustomerDate"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Dropped Date</td>
                            <td>
                                <input
                                    type="date"
                                    value={data.droppedCustomerDate || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    size="10"
                                    maxLength="10"
                                    className="form-control "
                                    name="droppedCustomerDate"
                                />
                            </td>
                        </tr>

                        <tr>
                            <td align="right">Lead Status</td>
                            <td>
                                <select required className="form-control " value={data.leadStatusId || ""}
                                        onChange={($event) => this.setValue("leadStatusId", $event.target.value)}>
                                    <option value="">None</option>
                                    {
                                        leadStatus.map(status => <option key={status.id}
                                                                         value={status.id}>{status.name}</option>)
                                    }

                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        );
    }
    getSectorSizeCard = () => {

        const {data, customerTypes, sectors} = this.state;
        return (
            <div className="card m-5">
                <div className="card-header">
                    <h3>Sector and Size</h3>
                </div>
                <div className="card-body">
                    <table>
                        <tbody>
                        <tr>
                            <td align="right">Type</td>
                            <td>
                                <select
                                    required
                                    className="form-control "
                                    value={data.customerTypeID || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    name="customerTypeID"
                                >
                                    <option></option>
                                    {customerTypes.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.name}
                                        </option>
                                    ))}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Sector</td>
                            <td>
                                <select
                                    required
                                    className="form-control "
                                    value={data.sectorID || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    name="sectorID"
                                >
                                    <option></option>
                                    {sectors.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.name}
                                        </option>
                                    ))}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">PCs</td>
                            <td>
                                <input
                                    type="number"
                                    value={data.noOfPCs || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    className="form-control "
                                    name="noOfPCs"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Servers</td>
                            <td>

                                <input
                                    type="number"
                                    value={data.noOfServers || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    className="form-control "
                                    name="noOfServers"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Sort Code</td>
                            <td>
                                <EncryptedTextInput
                                    encryptedValue={data.sortCode || ""}
                                    onChange={(encryptedValue) => this.updateCustomerField('sortCode', encryptedValue)}
                                    replaceFunction={(value) => {
                                        return value.replace(/[^0-9]+/g, "");
                                    }}
                                    mask="99-99-99"
                                    name="sortCode"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Account Name</td>
                            <td>
                                <input
                                    type="text"
                                    value={data.accountName || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    size="18"
                                    maxLength="18"
                                    className="form-control "
                                    name="accountName"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Account Number</td>
                            <td>
                                <EncryptedTextInput
                                    encryptedValue={data.accountNumber || ""}
                                    onChange={(encryptedValue) => this.updateCustomerField('accountNumber', encryptedValue)}
                                    replaceFunction={(value) => {
                                        return value.replace(/[^0-9]+/g, "");
                                    }}
                                    mask="99999999"
                                    name="accountNumber"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Reg</td>
                            <td>
                                <input
                                    type="text"
                                    value={data.regNo || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    size="10"
                                    maxLength="10"
                                    className="form-control "
                                    name="regNo"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Pre-pay Top Up</td>
                            <td>
                                <input
                                    type="text"
                                    value={data.gscTopUpAmount || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    size="10"
                                    maxLength="10"
                                    className="form-control "
                                    name="gscTopUpAmount"
                                />
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Inclusive OOH Call Outs</td>
                            <td>
                                <input
                                    type="number"
                                    value={data.inclusiveOOHCallOuts || ""}
                                    onChange={($event) =>
                                        this.handleUpdateGenericField($event)
                                    }
                                    size="10"
                                    maxLength="10"
                                    className="form-control "
                                    name="inclusiveOOHCallOuts"
                                />

                            </td>
                            <td><span style={{whiteSpace: "nowrap"}}>Per Month</span>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Patch Management Eligible</td>
                            <td>
                                {data.eligiblePatchManagement || ""}
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Exclude from Webroot Checks</td>
                            <td>
                                <Toggle checked={data.excludeFromWebrootChecks}
                                        onChange={() => this.setValue("excludeFromWebrootChecks", (!data.excludeFromWebrootChecks * 1))}></Toggle>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        );
    }
    getSLAItem = (title, field) => {
        const {data} = this.state;
        return (
            <div className="flex-row flex-center">
                <span style={{width: 15, textAlign: "right"}}>{title}</span>
                <input style={{width: 50}} className="form-control" value={data[field] || ''}
                       onChange={($event) => this.setValue(field, $event.target.value)}></input>
            </div>
        );
    }

    getSLAItemToggle = (title, field) => {
        const {data} = this.state;
        return (
            <div className="flex-row flex-center">
                <span style={{width: 15, textAlign: "right"}}>{title}</span>
                <div style={{width: 50, marginLeft: 3}}>
                    <Toggle checked={data[field]} onChange={() => this.setValue(field, !data[field])}></Toggle>
                </div>
            </div>
        );

    }

    getServiceLevelAgreementsCard = () => {
        const {data} = this.state;
        return (
            <div className="card m-5">
                <div className="card-header">
                    <h3>Service Level Agreements</h3>
                </div>
                <div className="card-body">
                    <table>
                        <tbody>
                        <tr>
                            <td align="right">SLA Response Hours</td>
                            <td>
                                <div className="flex-row">
                                    {this.getSLAItem(1, "slaP1")}
                                    {this.getSLAItem(2, "slaP2")}
                                    {this.getSLAItem(3, "slaP3")}
                                    {this.getSLAItem(4, "slaP4")}
                                    {this.getSLAItem(5, "slaP5")}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">SLA Response Fix Hours</td>
                            <td>
                                <div className="flex-row">
                                    {this.getSLAItem(1, "slaFixHoursP1")}
                                    {this.getSLAItem(2, "slaFixHoursP2")}
                                    {this.getSLAItem(3, "slaFixHoursP3")}
                                    {this.getSLAItem(4, "slaFixHoursP4")}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">SLA Penalties Agreed</td>
                            <td>
                                <div className="flex-row">
                                    {this.getSLAItemToggle(1, "slaP1PenaltiesAgreed")}
                                    {this.getSLAItemToggle(2, "slaP2PenaltiesAgreed")}
                                    {this.getSLAItemToggle(3, "slaP3PenaltiesAgreed")}
                                </div>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        );
    }
    getTechnicalNotesCard = () => {
        const {data} = this.state;

        return <div className="card m-5">
            <div className="card-header">
                <h3>Technical Notes</h3>
            </div>
            <div className="card-body">
                <table className="table">
                    <tbody>
                    <tr>
                        <td align="right">Active Directory Name</td>
                        <td>
                            <input type="text"
                                   value={data.activeDirectoryName || ''}
                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                   size="54"
                                   maxLength="255"
                                   className="form-control "
                                   name="activeDirectoryName"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Technical Notes</td>
                        <td>  <textarea className="form-control "

                                        rows="6"
                                        value={data.techNotes || ''}
                                        onChange={($event) => this.handleUpdateGenericField($event)}
                                        name="techNotes"
                        /></td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>
    }
    getCards = () => {

        return <div className="row" style={{margin: 2}} id="mainForm">
            <div className="col-md-6">
                {this.getKeyDetailsCard()}
                {this.getSectorSizeCard()}
            </div>
            <div className="col-md-6">
                {this.getReviewMeetingCard()}
                {this.getAccountsCard()}
                {this.getServiceLevelAgreementsCard()}
                {this.getTechnicalNotesCard()}
            </div>
        </div>
    }
    handleSave = () => {
        const {data, originData} = this.state;
        if (!this.isFormValid("mainForm")) {
            this.alert("Please enter required inputs");
            return;
        }
        this.api.updateCustomer(data).then(res => {
            if (!res.state) {
                this.alert("Data not saved successfully: " + res.error);
                return
            }
            this.logData(data, originData, data.customerID, null, Pages.Customer);
            if (this.props.customerId) {
                this.alert("Data saved successfully");
                this.getCustomerData();
            } else {
                this.alert("Data saved successfully, Please add sites and contacts");
                setTimeout(() => {
                    window.location = `Customer.php?action=dispEdit&customerID=${res.data.customerID}&activeTab=sites`;
                }, 1000)
            }
        }, error => {
            this.alert("Data not saved successfully: " + error.error);
        })
    }


    isProspect() {
        return !(this.props.data.becameCustomerDate && !this.props.data.droppedCustomerDate);
    }

    render() {
        return (
            <div>
                {this.getAlert()}
                {this.getCards()}
                <button onClick={this.handleSave} className="ml-5">Save</button>
            </div>
        );
    }

}
