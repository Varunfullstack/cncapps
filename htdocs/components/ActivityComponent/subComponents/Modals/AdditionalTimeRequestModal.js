import Modal from "../../../shared/Modal/modal";
import React from 'react';
import CNCCKEditor from "../../../shared/CNCCKEditor";
import * as PropTypes from "prop-types";
import APICustomers from "../../../services/APICustomers";

import './AdditionalTimeRequestModal.css';

class AdditionalTimeRequestModal extends React.Component {
    api = new APICustomers()
    static defaultProps = {
        show: false,
    }

    constructor(props) {
        super(props);
        this.state = this.initialState();
    }

    initialState() {
        return {
            reason: '',
            timeRequested: 0,
            contacts: [],
            selectedContactId: ''
        };
    }

    async componentDidMount() {
        const {serviceRequestData} = this.props;

        let contacts = await this.api.getCustomerContacts(serviceRequestData.customerId);
        const serviceRequestContactId = parseInt(serviceRequestData.contactID);
        const {filteredContacts, primaryContact, isSelectedContactMain} = contacts.reduce(
            (acc, c) => {
                if (c.id === serviceRequestContactId) {
                    acc.isSelectedContactMain = c.supportLevel === 'main';
                }

                if (c.supportLevel == 'main') {
                    acc.filteredContacts.push(c);
                    if (c.isPrimary) {
                        acc.primaryContact = c;
                    }
                }
                return acc;
            },
            {filteredContacts: [], primaryContact: null, isSelectedContactMain: false}
        )
        const selectedContactId = isSelectedContactMain ? serviceRequestContactId : primaryContact.id;

        this.setState({contacts: filteredContacts, selectedContactId})
    }

    handleTemplateValueChange = (reason) => {
        this.setState({reason});
    }
    handleTemplateOk = () => {
        if (this.props.onChange)
            this.props.onChange(this.state);
        this.setState(this.initialState());
    }

    onCancel() {
        if (this.props.onCancel) {
            this.props.onCancel();
        }
        this.setState(this.initialState());
    }

    handleTimeRequestedChange = ($event) => {
        this.setState({timeRequested: $event.target.value})
    }

    renderEditableField() {
        const {reason} = this.state;
        return (
            <div key="editorField"
                 className="modal_editor"
            >
                <div id="additionalTimeRequestModalTop"
                     key="topElement"
                />
                <CNCCKEditor key="AddInternalNote"
                             name="AddInternalNote"
                             value={reason}
                             onChange={this.handleTemplateValueChange}
                             className="CNCCKEditor"
                             type="inline"
                             height="500"
                             sharedSpaces={true}
                             top="additionalTimeRequestModalTop"
                             bottom="additionalTimeRequestModalBottom"
                />
                <div id="additionalTimeRequestModalBottom"
                     key="bottomElement"
                />
            </div>
        )
    }

    changeSelectedContact = ($event) => {
        this.setState({selectedContactId: $event.target.value});
    }

    getTemplateModal = () => {
        const {show} = this.props;
        const {reason, timeRequested, contacts, selectedContactId} = this.state;
        return (
            <Modal
                width="600px"
                onClose={() => this.onCancel()}
                title="Additional Time Request"
                show={show}
                className="standardTextModal"
                content={(
                    <React.Fragment key="internalModal">
                        <div key="contact picker"
                             className="contactPicker"
                        >
                            <label>
                                Send request to:
                            </label>
                            <select value={selectedContactId}
                                    onChange={this.changeSelectedContact}
                            >
                                {
                                    contacts.map(x => <option key={x.id}
                                                              value={x.id}
                                    >{`${x.firstName} ${x.lastName}`}</option>)
                                }
                            </select>
                        </div>
                        <div key="hoursContainer"
                             style={{marginBottom: "1rem"}}
                             className="hoursContainer"
                        >
                            <label key="someLabel">
                                Hours to quote for:
                            </label>
                            <select onChange={this.handleTimeRequestedChange}
                                    key="someSelect"
                            >
                                <option>
                                    -- Select an option --
                                </option>
                                <option value={1}
                                        key={1}
                                >1
                                </option>
                                <option value={2}
                                        key={2}
                                >2
                                </option>
                                <option value={3}
                                        key={3}
                                >3
                                </option>
                                <option value={4}
                                        key={4}
                                >4
                                </option>
                            </select>
                        </div>
                        <label>
                            Reason for additional charges (the customer will see this)
                        </label>
                        <div style={{height: 150}}
                             key="editableFieldContainer"
                        >
                            {this.renderEditableField()}
                        </div>
                    </React.Fragment>
                )}
                footer={
                    <div key="footer">
                        <button key="saveButton"
                                disabled={!reason || !timeRequested}
                                onClick={this.handleTemplateOk}
                        >
                            Save
                        </button>
                        <button key="cancelButton"
                                onClick={() => this.onCancel()}
                        >
                            Cancel
                        </button>
                    </div>
                }
            />
        )
    }

    render() {
        return this.getTemplateModal();
    }
}

AdditionalTimeRequestModal.propTypes = {
    show: PropTypes.bool,
    onChange: PropTypes.func,
    onCancel: PropTypes.func,
    serviceRequestData: PropTypes.object
}

export default AdditionalTimeRequestModal;