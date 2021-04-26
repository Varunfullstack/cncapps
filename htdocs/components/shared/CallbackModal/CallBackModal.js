import CurrentActivityService from "../../CurrentActivityReportComponent/services/CurrentActivityService";
import React from "react";
import Modal from "../Modal/modal";
import APIStandardText from "../../services/APIStandardText";
import MainComponent from "../MainComponent";
import APICustomers from "../../services/APICustomers";
import * as PropTypes from "prop-types";
import moment from "moment";
import './CallBackModal.css';

class CallBackModal extends MainComponent {
    apiCurrentActivityService = new CurrentActivityService();
    apiTemplate = new APIStandardText();
    apiCustomer = new APICustomers();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            templateOptions: [],
            data: {
                description: "",
                time: moment().add(2, 'hours').format("HH:mm"),
                date: moment().format("YYYY-MM-DD"),
                contactID: this.props.contactID,
                customerID: this.props.customerID,
                problemID: this.props.problemID,
                contactName: this.props.contactName,
                notifyTeamLead: false
            },
            contacts: []
        }
    }

    componentDidMount() {
        this.apiCustomer.getCustomerContacts(this.props.customerID).then(contacts => {
            this.setState({contacts});
        })
    }

    handleClose = () => {
        if (this.props.onClose)
            this.props.onClose();
    };
    getContent = () => {
        const {data, contacts} = this.state;
        return (
            <div>

                <div className="form-group">
                    <label>Call back date / time</label>
                    <div className="flex-row">
                        <input
                            type="date"
                            className="modal-input"
                            style={{width: 120}}
                            value={data.date}
                            onChange={(event) => this.setValue("date", event.target.value)}
                        />

                        <input
                            type="time"
                            className="modal-input"
                            style={{width: 70}}
                            value={data.time}
                            onChange={(event) => this.setValue("time", event.target.value)}
                        />
                    </div>
                </div>

                <div className="form-group">
                    <label>Contact</label>
                    <select value={data.contactID}
                            onChange={(event) => this.handleContactChange(event.target.value)}
                    >
                        <option>
                        </option>
                        {contacts.map(c => <option key={c.id}
                                                   value={c.id}
                        >{c.firstName + ' ' + c.lastName}</option>)}
                    </select>
                </div>
                <div className="flex-row">
                    <input type="checkbox"
                           onChange={(event) =>
                               this.setValue("notifyTeamLead", !this.state.data.notifyTeamLead)
                           }
                    />
                    <label>This is high profile, notify Team Lead as well (reason must be supplied)</label>
                </div>
                <div className="form-group">
                    <label>Reason for the call back (this will be visible on the portal)</label>
                    <textarea
                        className="modal-input"
                        style={{}}
                        value={data.description}
                        onChange={(event) =>
                            this.setValue("description", event.target.value)
                        }
                    />
                </div>
            </div>
        );
    }
    handleContactChange = (contactID) => {
        const {contacts, data} = this.state;
        const contact = contacts.find(c => c.id == contactID);
        data.contactID = contactID;
        data.contactName = contact.firstName + ' ' + contact.lastName;
        this.setState({data});
    }
    handleSave = () => {
        const {data} = this.state;
        if (moment(data.date + " " + data.time) < moment()) {
            this.alert("Data and time must be in future.");
            return;
        }
        if (data.notifyTeamLead && data.description == '') {
            this.alert("Please provide the reason.");
            return;
        }
        this.apiCurrentActivityService.addCallback(data).then(result => {
            if (result.status)
                this.handleClose();
        });

    }

    render() {
        if (!this.props.show) return null;
        return (
            <div className="callbackmodal">
                {this.getAlert()}
                <Modal
                    width={600}
                    show={this.props.show}
                    title="Record Customer Call Back"
                    content={<div key="content">
                        {this.getContent()}
                    </div>}
                    footer={<div key="footer">
                        <button onClick={this.handleSave}>Save</button>
                        <button onClick={() => this.handleClose()}>Cancel</button>
                    </div>}
                    onClose={() => this.handleClose()}
                />
            </div>
        );
    }
}

CallBackModal.propTypes = {
    show: PropTypes.bool,
    onClose: PropTypes.func,
    contactID: PropTypes.number,
    customerID: PropTypes.number,
    problemID: PropTypes.number,
    contactName: PropTypes.string
}
export default CallBackModal;
