"use strict";
import React from 'react';
import Select from "./Select";
import EncryptedTextInput from "./EncryptedTextInput";
import {connect} from "react-redux";

class CustomerEditMain extends React.Component {
    el = React.createElement;


    constructor(props) {
        super(props);
        this.state = {
            loaded: false,

        };
    }

    handleReviewActionUpdate($event) {
        this.updateCustomerField('reviewAction', $event.target.value);
    }

    handleReviewUserIDUpdate($event) {
        this.updateCustomerField('reviewUserID', $event.target.value);
    }

    handleSlaFixHoursP1($event) {
        this.updateCustomerField('slaFixHoursP1', $event.target.value);
    }

    handleSlaFixHoursP2($event) {
        this.updateCustomerField('slaFixHoursP2', $event.target.value);
    }

    handleSlaFixHoursP3($event) {
        this.updateCustomerField('slaFixHoursP3', $event.target.value);
    }

    handleSlaFixHoursP4($event) {
        this.updateCustomerField('slaFixHoursP4', $event.target.value);
    }

    handleSlaP1PenaltiesAgreed($event) {
        this.updateCustomerField('slaP1PenaltiesAgreed', $event.target.value);
    }

    handleSlaP2PenaltiesAgreed($event) {
        this.updateCustomerField('slaP2PenaltiesAgreed', $event.target.value);
    }

    handleSlaP3PenaltiesAgreed($event) {
        this.updateCustomerField('slaP3PenaltiesAgreed', $event.target.value);
    }

    save() {
        const {customer} = this.props;
        return fetch('?action=updateCustomer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(customer)
        })
            .then(response => response.json())
            .then(response => {
            })
    }

    componentDidMount() {
    }

    handleCustomerTypeUpdate(value) {
        this.updateCustomerField('customerTypeID', value);
    }

    updateCustomerField(field, value) {
        this.setState(prevState => {
            const customer = {...prevState.customer};
            customer[field] = value;
            return {customer};
        })
    }

    handlePrimaryMainContactUpdate(value) {
        this.updateCustomerField("primaryMainContactID", value);
    }

    handleMailshotFlagUpdate(event) {
        this.updateCustomerField('mailshotFlag', event.target.checked ? "Y" : "N");
    }

    handleReferredFlagUpdate(event) {
        this.updateCustomerField('referredFlag', event.target.checked ? "Y" : "N");
    }

    handleSpecialAttentionFlagUpdate(event) {
        this.updateCustomerField('specialAttentionFlag', event.target.checked ? "Y" : "N");
    }

    handleSpecialAttentionDateUpdate(event) {
        this.updateCustomerField('specialAttentionEndDate', event.target.value);
    }

    handleLastReviewMeetingDateUpdate(event) {
        this.updateCustomerField('lastReviewMeetingDate', event.target.value);
    }

    handleReviewMeetingBookedUpdate(event) {
        this.updateCustomerField('reviewMeetingBooked', event.target.checked);
    }

    handleReviewMeetingFrequencyMonthsUpdate(event) {
        this.updateCustomerField('reviewMeetingFrequencyMonths', event.target.value);
    }

    handleLeadStatusIdUpdate(value) {
        this.updateCustomerField('leadStatusId', value);
    }

    handleSupport24HourFlagUpdate(event) {
        this.updateCustomerField('support24HourFlag', event.target.checked);
    }

    handleNameUpdate(event) {
        this.updateCustomerField('name', event.target.value);
    }

    handleSectorIDUpdate(event) {
        this.updateCustomerField('sectorID', event.target.value);
    }

    handleNoOfPCsUpdate(event) {
        this.updateCustomerField('noOfPCs', event.target.value);
    }


    handleNoOfServersUpdate(event) {
        this.updateCustomerField('noOfServers', event.target.value);
    }


    handleRegNoUpdate(event) {
        this.updateCustomerField('regNo', event.target.value);
    }

    handleNoOfSitesUpdate(event) {
        this.updateCustomerField('noOfSites', event.target.value);
    }

    handleGscTopUpAmountUpdate(event) {
        this.updateCustomerField('gscTopUpAmount', event.target.value);
    }

    handleBecameCustomerDateUpdate(event) {
        this.updateCustomerField('becameCustomerDate', event.target.value);
    }

    handleDroppedCustomerDateUpdate(event) {
        this.updateCustomerField('droppedCustomerDate', event.target.value);
    }


    handleSLAP1Update(event) {
        this.updateCustomerField('slaP1', event.target.value);
    }

    handleSLAP2Update(event) {
        this.updateCustomerField('slaP2', event.target.value);
    }

    handleSLAP3Update(event) {
        this.updateCustomerField('slaP3', event.target.value);
    }

    handleSLAP4Update(event) {
        this.updateCustomerField('slaP4', event.target.value);
    }

    handleSLAP5Update(event) {
        this.updateCustomerField('slaP5', event.target.value);
    }

    handleTechNotesUpdate(event) {
        this.updateCustomerField('techNotes', event.target.value);
    }


    handleActiveDirectoryNameUpdate(event) {
        this.updateCustomerField('activeDirectoryName', event.target.value);
    }

    handleAccountManagerUserIDUpdate(event) {
        this.updateCustomerField('accountManagerUserID', event.target.value);
    }

    handleSortCodeUpdate(value) {
        this.updateCustomerField('sortCode', value);
    }

    handleAccountNameUpdate(event) {
        this.updateCustomerField('accountName', event.target.value);
    }

    handleAccountNumberUpdate(value) {
        this.updateCustomerField('accountNumber', value);
    }

    handleReviewDateUpdate(value) {
        this.updateCustomerField('reviewDate', value);
    }

    handleReviewTimeUpdate(value) {
        this.updateCustomerField('reviewTime', value);
    }


    render() {
        const {customer} = this.props;

        if (!customer) {
            return null;
        }

        const {customerId} = customer;
        return (
            <div className="tab-pane fade show active"
                 id="nav-home"
                 role="tabpanel"
                 aria-labelledby="nav-home-tab"
            >
                <div className="mt-3">
                    <div className="row">
                        <div className="col-md-6 mb-3">
                            <h2>Customer - {customer.name}
                                <a href="#">
                                    <i className="fal fa-globe"/>
                                </a>
                            </h2>
                        </div>
                        <div className="col-md-6 mb-3">
                            <ul className="list-style-none float-right">
                                <li>
                                    <button type="button"
                                            className="btn btn-sm btn-new"
                                            onClick={() => this.save()}
                                    >Save
                                    </button>
                                    <button type="button"
                                            className="btn btn-sm btn-outline-secondary"
                                    >Set all
                                        users to no support
                                    </button>
                                    <button type="button"
                                            className="btn btn-sm btn-outline-secondary"
                                    >
                                        <i className="fal fa-filter"/>
                                    </button>
                                    <button type="button"
                                            className="btn btn-sm btn-outline-secondary"
                                    >
                                        <i className="fal fa-ellipsis-v"/>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-6">
                            <div className="card mb-3">
                                <div className="card-body">
                                    <div className="row">
                                        <div className="col-md-12">
                                            <h5>Key Details</h5>
                                        </div>
                                        <div className="col-lg-6">
                                            <label>Customer {customerId}</label>
                                            <div className="form-group">
                                                <input type="text"
                                                       onChange={this.handleNameUpdate}
                                                       value={this.state.customer.name || ''}
                                                       size="50"
                                                       maxLength="50"
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-6">
                                            <label htmlFor="">Primary Main Contact</label>
                                            <div className="form-group">
                                                <Select
                                                    options={this.state.mainContacts}
                                                    selectedOption={this.state.customer.primaryMainContactID || ''}
                                                    onChange={this.handlePrimaryMainContactUpdate}
                                                    className='form-control input-sm'
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-3">
                                            <label>Referred</label>
                                            <div className="form-group form-inline pt-1">
                                                <label className="switch">
                                                    <input type="checkbox"
                                                           checked={this.state.customer.referredFlag === 'Y'}
                                                           onChange={this.handleReferredFlagUpdate}
                                                    />
                                                    <span className="slider round"/>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-3">
                                            <label>24 Hour Cover</label>
                                            <div className="form-group form-inline pt-1">
                                                <label className="switch"
                                                >
                                                    <input type="checkbox"
                                                           checked={this.state.customer.support24HourFlag === 'Y'}
                                                           onChange={this.handleSupport24HourFlagUpdate}
                                                    />
                                                    <span className="slider round"/>
                                                </label>

                                            </div>
                                        </div>
                                        <div className="col-lg-6">
                                            <label htmlFor="">Special Attention</label>
                                            <div className="form-group form-inline">
                                                <label htmlFor=""
                                                       className="switch mr-3"
                                                >
                                                    <input type="checkbox"
                                                           onChange={this.handleSpecialAttentionFlagUpdate}
                                                           checked={this.state.customer.specialAttentionFlag === 'Y'}
                                                    />
                                                    <span className="slider round"/>
                                                </label>
                                                <div className="form-group mr-3">
                                                    <label className="pr-3"
                                                    >
                                                        Until
                                                    </label>
                                                    <input type="date"
                                                           value={this.state.customer.specialAttentionEndDate || ''}
                                                           size="10"
                                                           maxLength="10"
                                                           className="form-control input-sm"
                                                           onChange={this.handleSpecialAttentionDateUpdate}
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="card mb-3">
                                <div className="card-body">
                                    <div className="row">
                                        <div className="col-md-12"><h5>Review Meetings</h5></div>
                                        <div className="col-lg-12">
                                            <div className="form-inline">
                                                <div className="form-group mr-3">
                                                    <label htmlFor="ex3"
                                                           className="col-form-label pr-3"
                                                    >Last Review Meeting
                                                    </label>
                                                    <input type="date"
                                                           onChange={this.handleLastReviewMeetingDateUpdate}
                                                           value={this.state.customer.lastReviewMeetingDate || ''}
                                                           size="10"
                                                           maxLength="10"
                                                           className="form-control input-sm"
                                                    />
                                                </div>
                                                <div className="checkbox mr-3 d-flex p-2 justify-content-between align-items-center">
                                                    <label className="pr-3">Booked</label>
                                                    <label className="switch inline"
                                                    >
                                                        <input type="checkbox"
                                                               onChange={this.handleReviewMeetingBookedUpdate}
                                                               checked={this.state.customer.reviewMeetingBooked}
                                                        />
                                                        <span className="slider round"/>
                                                    </label>

                                                </div>
                                                <div className="form-group">
                                                    <label htmlFor="ex4"
                                                           className="col-form-label pr-3"
                                                    >Frequency</label>
                                                    <Select
                                                        options={
                                                            [
                                                                {label: 'Monthly', value: 1},
                                                                {label: "Two Monthly", value: 2},
                                                                {label: 'Quarterly', value: 3},
                                                                {label: "Six-Monthly", value: 6},
                                                                {label: 'Annually', value: 12}
                                                            ]
                                                        }
                                                        selectedOption={this.state.customer.reviewMeetingFrequencyMonths || ''}
                                                        onChange={this.handleReviewMeetingFrequencyMonthsUpdate}
                                                        className="form-control input-sm"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="card mb-3">
                                <div className="card-body">
                                    <div className="row">
                                        <div className="col-md-12"><h5>Accounts</h5></div>
                                        <div className="col-lg-4">
                                            <label>Became Customer</label>
                                            <div className="form-group">

                                                <input type="date"
                                                       value={this.state.customer.becameCustomerDate || ''}
                                                       onChange={this.handleBecameCustomerDateUpdate}
                                                       size="10"
                                                       maxLength="10"
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label>Dropped Date</label>
                                            <div className="form-group">
                                                <input type="date"
                                                       value={this.state.customer.droppedCustomerDate || ''}
                                                       onChange={this.handleDroppedCustomerDateUpdate}
                                                       size="10"
                                                       maxLength="10"
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label>Account Manager</label>
                                            <div className="form-group">
                                                <Select options={this.state.accountManagers}
                                                        selectedOption={this.state.customer.accountManagerUserID || ''}
                                                        onChange={this.handleAccountManagerUserIDUpdate}
                                                        key={'accountManager'}
                                                        className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="card">
                                <div className="card-body">
                                    <div className="row">
                                        <div className="col-md-12"><h5>Sector and Size</h5></div>
                                        <div className="col-lg-6">
                                            <label htmlFor="">Type</label>
                                            <div className="form-group">
                                                <Select options={this.state.customerTypes}
                                                        className="form-control input-sm"
                                                        selectedOption={this.state.customer.customerTypeID || ''}
                                                        onChange={(value) => this.handleCustomerTypeUpdate(value)}
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-6">
                                            <label htmlFor="">Sector</label>
                                            <div className="form-group">
                                                <Select options={this.state.sectors}
                                                        selectedOption={this.state.customer.sectorID || ''}
                                                        onChange={(value) => this.handleSectorIDUpdate(value)}
                                                        className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label htmlFor="">PCs</label>
                                            <div className="form-group">
                                                <input type="number"
                                                       value={this.state.customer.noOfPCs || ''}
                                                       onChange={($event) => this.handleNoOfPCsUpdate($event)}
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label>Servers</label>
                                            <div className="form-group">
                                                <input type="number"
                                                       value={this.state.customer.noOfServers || ''}
                                                       onChange={($event) => this.handleNoOfServersUpdate($event)}
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label>Sites</label>
                                            <div className="form-group">
                                                <input type="number"
                                                       value={this.state.customer.noOfSites || ''}
                                                       onChange={($event) => this.handleNoOfSitesUpdate($event)}
                                                       size="2"
                                                       maxLength="2"
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label htmlFor="">Sort Code</label>
                                            <div className="form-group">
                                                <EncryptedTextInput encryptedValue={this.state.customer.sortCode}
                                                                    onChange={(value) => this.handleSortCodeUpdate(value)}
                                                                    mask='99-99-99'
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label htmlFor="">Account Name</label>
                                            <div className="form-group">
                                                <EncryptedTextInput className="form-control input-sm"
                                                                    encryptedValue={this.state.customer.accountName || ''}
                                                                    onChange={(value) => this.handleAccountNameUpdate(value)}
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label htmlFor="">Account Number</label>
                                            <div className="form-group">
                                                <EncryptedTextInput
                                                    encryptedValue={this.state.customer.accountNumber}
                                                    onChange={(value) => this.handleAccountNumberUpdate(value)}
                                                    mask='99999999'
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label>Reg</label>
                                            <div className="form-group">
                                                <input type="text"
                                                       value={this.state.customer.regNo || ''}
                                                       onChange={($event) => this.handleRegNoUpdate($event)}
                                                       size="10"
                                                       maxLength="10"
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label>Pre-pay Top Up</label>
                                            <div className="form-group">
                                                <input type="text"
                                                       value={this.state.customer.gscTopUpAmount || ''}
                                                       onChange={($event) => this.handleGscTopUpAmountUpdate($event)}
                                                       size="10"
                                                       maxLength="10"
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="col-md-6">
                            <div className="card mb-3">
                                <div className="card-body">
                                    <div className="row">
                                        <div className="col-md-12"><h5>Service Level Agreements</h5></div>

                                        <div className="col-lg-12">
                                            <label htmlFor="">SLA Response Hours</label>
                                            <div className="form-group form-inline">
                                                <label style={{margin: "0 .5rem"}}>1</label>
                                                <input type="number"
                                                       value={this.state.customer.slaP1 || ''}
                                                       onChange={($event) => this.handleSLAP1Update($event)}
                                                       size="1"
                                                       maxLength="3"
                                                       className="form-control col-sm-4"
                                                />
                                                <label style={{margin: "0 .5rem"}}>2</label>
                                                <input type="number"
                                                       value={this.state.customer.slaP2 || ''}
                                                       onChange={($event) => this.handleSLAP2Update($event)}
                                                       size="1"
                                                       maxLength="3"
                                                       className="form-control col-sm-4"
                                                />
                                            </div>
                                            <div className="form-group form-inline">

                                                <label style={{margin: "0 .5rem"}}>3</label>
                                                <input type="number"
                                                       value={this.state.customer.slaP3 || ''}
                                                       onChange={($event) => this.handleSLAP3Update($event)}
                                                       size="1"
                                                       maxLength="3"
                                                       className="form-control col-sm-4"
                                                />
                                                <label style={{margin: "0 .5rem"}}>4</label>
                                                <input type="number"
                                                       value={this.state.customer.slaP4 || ''}
                                                       onChange={($event) => this.handleSLAP4Update($event)}
                                                       size="1"
                                                       maxLength="3"
                                                       className="form-control col-sm-4"
                                                />

                                            </div>

                                            <div className="form-group form-inline">
                                                <label style={{margin: "0 .5rem"}}>5</label>
                                                <input type="number"
                                                       value={this.state.customer.slaP5 || ''}
                                                       onChange={($event) => this.handleSLAP5Update($event)}
                                                       size="1"
                                                       maxLength="3"
                                                       className="form-control col-sm-4"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-12">
                                            <label htmlFor="">SLA Response Fix Hours</label>
                                            <div className="form-group form-inline">
                                                <label style={{margin: "0 .5rem"}}>1</label>
                                                <input value={this.state.customer.slaFixHoursP1 || ''}
                                                       type="number"
                                                       size="1"
                                                       step="0.1"
                                                       maxLength="4"
                                                       max="999.9"
                                                       onChange={($event) => this.handleSlaFixHoursP1($event)}
                                                       className="form-control col-sm-4"
                                                />
                                                <label style={{margin: "0 .5rem"}}>2</label>
                                                <input value={this.state.customer.slaFixHoursP2 || ''}
                                                       type="number"
                                                       size="1"
                                                       step="0.1"
                                                       maxLength="4"
                                                       max="999.9"
                                                       onChange={($event) => this.handleSlaFixHoursP2($event)}
                                                       className="form-control col-sm-4"
                                                />
                                            </div>
                                            <div className="form-group form-inline">
                                                <label style={{margin: "0 .5rem"}}>3</label>
                                                <input value={this.state.customer.slaFixHoursP3 || ''}
                                                       type="number"
                                                       size="1"
                                                       step="0.1"
                                                       maxLength="4"
                                                       max="999.9"
                                                       onChange={($event) => this.handleSlaFixHoursP3($event)}
                                                       className="form-control col-sm-4"
                                                />
                                                <label style={{margin: "0 .5rem"}}>4</label>
                                                <input value={this.state.customer.slaFixHoursP4 || ''}
                                                       type="number"
                                                       size="1"
                                                       step="0.1"
                                                       maxLength="4"
                                                       max="999.9"
                                                       onChange={($event) => this.handleSlaFixHoursP4($event)}
                                                       className="form-control col-sm-4"
                                                />

                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label htmlFor="">SLA Penalties Agreed</label>
                                            <div className="form-group form-inline pt-1 d-flex">
                                                <div className="toggle-inline">
                                                    <label>1</label>
                                                    <label className="switch"
                                                    >
                                                        <input type="checkbox"
                                                               checked={this.state.customer.slaP1PenaltiesAgreed || ''}
                                                               onChange={($event) => this.handleSlaP1PenaltiesAgreed($event)}
                                                        />
                                                        <span className="slider round"/>
                                                    </label>
                                                </div>

                                                <div className="toggle-inline">
                                                    <label>2</label>
                                                    <label className="switch"
                                                    >
                                                        <input type="checkbox"
                                                               checked={this.state.customer.slaP2PenaltiesAgreed || ''}
                                                               onChange={($event) => this.handleSlaP2PenaltiesAgreed($event)}
                                                        />
                                                        <span className="slider round"/>
                                                    </label>
                                                </div>
                                                <div className="toggle-inline">
                                                    <label>3</label>
                                                    <label className="switch"
                                                    >
                                                        <input type="checkbox"
                                                               checked={this.state.customer.slaP3PenaltiesAgreed || ''}
                                                               onChange={($event) => this.handleSlaP3PenaltiesAgreed($event)}
                                                        />
                                                        <span className="slider round"/>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <label>Last Modified:</label>
                                            <div className="form-group">
                                                <h6>{this.state.customer.lastModified}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="card">
                                <div className="card-body">
                                    <div className="row">
                                        <div className="col-md-12"><h5>Technical Notes</h5></div>
                                        <div className="col-lg-6">
                                            <label>Technical Notes</label>
                                            <div className="form-group">
                                                <textarea className="form-control input-sm"
                                                          cols="30"
                                                          rows="2"
                                                          value={this.state.customer.techNotes || ''}
                                                          onChange={($event) => this.handleTechNotesUpdate($event)}
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-6">
                                            <label>Active Directory Name</label>
                                            <div className="form-group">
                                                <input type="text"
                                                       value={this.state.customer.activeDirectoryName || ''}
                                                       onChange={($event) => this.handleActiveDirectoryNameUpdate($event)}
                                                       size="54"
                                                       maxLength="255"
                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }

    isProspect() {
        return !(this.props.customer.becameCustomerDate && !this.props.customer.droppedCustomerDate);
    }
}

function mapStateToProps(state) {
    const {customerEdit} = state;
    debugger;
    return {
        customer: customerEdit.customer,
        customerTypes: customerEdit.customerTypes,
        leadStatuses: customerEdit.leadStatuses,
        sectors: customerEdit.sectors,
        accountManagers: customerEdit.accountManagers,
        reviewEngineers: customerEdit.reviewEngineers,
    }
}

function mapDispatchToProps(dispatch) {
    return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(CustomerEditMain)