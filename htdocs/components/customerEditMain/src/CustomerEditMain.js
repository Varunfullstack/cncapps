"use strict";
import React from 'react';
import Select from "./Select";
import EncryptedTextInput from "./EncryptedTextInput";
import CustomerNotesComponent from "./CustomerNotesComponent";

class CustomerEditMain extends React.Component {
    el = React.createElement;


    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customer: {
                accountManagerUserID: '',
                accountName: '',
                accountNumber: '',
                activeDirectoryName: '',
                becameCustomerDate: '',
                customerID: '',
                customerTypeID: '',
                droppedCustomerDate: '',
                gscTopUpAmount: '',
                lastReviewMeetingDate: '',
                leadStatusId: '',
                mailshotFlag: '',
                reviewDate: '',
                reviewTime: '',
                modifyDate: '',
                name: '',
                noOfPCs: '',
                noOfServers: '',
                noOfSites: '',
                primaryMainContactID: '',
                referredFlag: '',
                regNo: '',
                reviewMeetingBooked: '',
                reviewMeetingFrequencyMonths: '',
                sectorID: '',
                slaP1: '',
                slaP2: '',
                slaP3: '',
                slaP4: '',
                slaP5: '',
                sortCode: '',
                specialAttentionEndDate: '',
                specialAttentionFlag: '',
                support24HourFlag: '',
                techNotes: '',
                websiteURL: '',
                slaFixHoursP1: '',
                slaFixHoursP2: '',
                slaFixHoursP3: '',
                slaFixHoursP4: '',
                slaP1PenaltiesAgreed: '',
                slaP2PenaltiesAgreed: '',
                slaP3PenaltiesAgreed: '',
                reviewUserID: '',
                reviewAction: '',
                lastContractSent: '',
            }
        };
        this.handleCustomerTypeUpdate = this.handleCustomerTypeUpdate.bind(this);
        this.handlePrimaryMainContactUpdate = this.handlePrimaryMainContactUpdate.bind(this);
        this.handleMailshotFlagUpdate = this.handleMailshotFlagUpdate.bind(this);
        this.handleReferredFlagUpdate = this.handleReferredFlagUpdate.bind(this);
        this.handleSpecialAttentionFlagUpdate = this.handleSpecialAttentionFlagUpdate.bind(this);
        this.handleSpecialAttentionDateUpdate = this.handleSpecialAttentionDateUpdate.bind(this);
        this.handleLastReviewMeetingDateUpdate = this.handleLastReviewMeetingDateUpdate.bind(this);
        this.handleReviewMeetingBookedUpdate = this.handleReviewMeetingBookedUpdate.bind(this);
        this.handleReviewMeetingFrequencyMonthsUpdate = this.handleReviewMeetingFrequencyMonthsUpdate.bind(this);
        this.handleLeadStatusIdUpdate = this.handleLeadStatusIdUpdate.bind(this);
        this.handleSupport24HourFlagUpdate = this.handleSupport24HourFlagUpdate.bind(this);
        this.handleNameUpdate = this.handleNameUpdate.bind(this);
        this.handleSectorIDUpdate = this.handleSectorIDUpdate.bind(this);
        this.handleNoOfPCsUpdate = this.handleNoOfPCsUpdate.bind(this);
        this.handleNoOfServersUpdate = this.handleNoOfServersUpdate.bind(this);
        this.handleRegNoUpdate = this.handleRegNoUpdate.bind(this);
        this.handleNoOfSitesUpdate = this.handleNoOfSitesUpdate.bind(this);
        this.handleGscTopUpAmountUpdate = this.handleGscTopUpAmountUpdate.bind(this);
        this.handleBecameCustomerDateUpdate = this.handleBecameCustomerDateUpdate.bind(this);
        this.handleDroppedCustomerDateUpdate = this.handleDroppedCustomerDateUpdate.bind(this);
        this.handleSLAP1Update = this.handleSLAP1Update.bind(this);
        this.handleSLAP2Update = this.handleSLAP2Update.bind(this);
        this.handleSLAP3Update = this.handleSLAP3Update.bind(this);
        this.handleSLAP4Update = this.handleSLAP4Update.bind(this);
        this.handleSLAP5Update = this.handleSLAP5Update.bind(this);
        this.handleTechNotesUpdate = this.handleTechNotesUpdate.bind(this);
        this.handleActiveDirectoryNameUpdate = this.handleActiveDirectoryNameUpdate.bind(this);
        this.handleAccountManagerUserIDUpdate = this.handleAccountManagerUserIDUpdate.bind(this);
        this.handleSortCodeUpdate = this.handleSortCodeUpdate.bind(this);
        this.handleAccountNameUpdate = this.handleAccountNameUpdate.bind(this);
        this.handleAccountNumberUpdate = this.handleAccountNumberUpdate.bind(this);
        this.handleSlaFixHoursP1 = this.handleSlaFixHoursP1.bind(this);
        this.handleSlaFixHoursP2 = this.handleSlaFixHoursP2.bind(this);
        this.handleSlaFixHoursP3 = this.handleSlaFixHoursP3.bind(this);
        this.handleSlaFixHoursP4 = this.handleSlaFixHoursP4.bind(this);
        this.handleSlaP1PenaltiesAgreed = this.handleSlaP1PenaltiesAgreed.bind(this);
        this.handleSlaP2PenaltiesAgreed = this.handleSlaP2PenaltiesAgreed.bind(this);
        this.handleSlaP3PenaltiesAgreed = this.handleSlaP3PenaltiesAgreed.bind(this);
        this.handleReviewDateUpdate = this.handleReviewDateUpdate.bind(this);
        this.handleReviewTimeUpdate = this.handleReviewTimeUpdate.bind(this);
        this.handleReviewUserIDUpdate = this.handleReviewUserIDUpdate.bind(this);
        this.handleReviewActionUpdate = this.handleReviewActionUpdate.bind(this);
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
        return fetch('?action=updateCustomer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(this.state.customer)
        })
            .then(response => response.json())
            .then(response => {
            })
    }

    componentDidMount() {
        const {customerId} = this.props;
        Promise.all([
            fetch('?action=getCustomer&customerID=' + customerId)
                .then(response => response.json())
                .then(response => this.setState({customer: response.data})),
            fetch('?action=getCustomerTypes')
                .then(response => response.json())
                .then(response => this.setState({
                    customerTypes: response.data.map(x => ({
                        label: x.cty_desc,
                        value: x.cty_ctypeno
                    }))
                })),
            fetch('?action=getMainContacts&customerID=' + customerId)
                .then(response => response.json())
                .then(response => this.setState({
                    mainContacts: response.data.map(x => ({
                        label: x.con_first_name + " " + x.con_last_name,
                        value: x.con_contno
                    }))
                })),
            fetch('?action=getLeadStatuses')
                .then(response => response.json())
                .then(response => this.setState({
                    leadStatuses: response.data.map(x => ({
                        label: x.name,
                        value: x.id
                    }))
                })),
            fetch('?action=getSectors')
                .then(response => response.json())
                .then(response => this.setState({
                    sectors: response.data.map(x => ({
                        label: x.sec_desc,
                        value: x.sec_sectorno
                    }))
                })),
            fetch('?action=getAccountManagers')
                .then(response => response.json())
                .then(response => this.setState({
                    accountManagers: response.data.map(x => ({
                        label: x.cns_name,
                        value: x.cns_consno,
                    }))
                })),
            fetch('?action=getReviewEngineers')
                .then(response => response.json())
                .then(response => this.setState({
                    reviewEngineers: response.data.map(x => ({
                        label: x.cns_name,
                        value: x.cns_consno,
                    }))
                })),
        ])
            .then(allLoaded => {
                console.log(this.state.customer);
                this.setState({loaded: true});
            })

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
        const {customerId} = this.props;
        return (
            <div className="tab-pane fade show active"
                 id="nav-home"
                 role="tabpanel"
                 aria-labelledby="nav-home-tab"
            >
                <div className="container-fluid mt-3 mb-3">
                    <div className="row">
                        <div className="col-md-6 mb-3">
                            <h2>Customer - {this.state.customer.name}</h2>
                        </div>
                        <div className="col-md-6 mb-3">
                            <ul className="list-style-none float-right">
                                <li>
                                    <button type="button"
                                            className="btn btn-outline-success"
                                            onClick={() => this.save()}
                                    >Save
                                    </button>
                                    <button type="button"
                                            className="btn btn-outline-danger"
                                    >Cancel
                                    </button>
                                    <button type="button"
                                            className="btn btn-outline-secondary"
                                    >Set all
                                        users to no support
                                    </button>
                                    <button type="button"
                                            className="btn btn-outline-secondary"
                                    >
                                        <i className="fa fa-filter"/>
                                    </button>
                                    <button type="button"
                                            className="btn btn-outline-secondary"
                                    >
                                        <i className="fa fa-ellipsis-v"/>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-6">
                            <div className="row">
                                <div className="col-lg-6">
                                    <label>Customer {customerId}</label>
                                    <div className="form-group">
                                        <input type="text"
                                               onChange={this.handleNameUpdate}
                                               value={this.state.customer.name || ''}
                                               size="50"
                                               maxLength="50"
                                               className="form-control"
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
                                            className='form-control'
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-3">
                                    <label>Mailshot</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               checked={this.state.customer.mailshotFlag === 'Y'}
                                               onChange={this.handleMailshotFlagUpdate}
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-3">
                                    <label>Referred</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               className="form-control"
                                               checked={this.state.customer.referredFlag === 'Y'}
                                               onChange={this.handleReferredFlagUpdate}
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-6">
                                    <label htmlFor="">Special Attention</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               className="form-control"
                                               onChange={this.handleSpecialAttentionFlagUpdate}
                                               checked={this.state.customer.specialAttentionFlag === 'Y'}
                                        />
                                        <div className="col-sm-4">until</div>
                                        <input type="date"
                                               value={this.state.customer.specialAttentionEndDate || ''}
                                               size="10"
                                               maxLength="10"
                                               className="form-control"
                                               onChange={this.handleSpecialAttentionDateUpdate}
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-12">
                                    <label htmlFor="">Last Review Meeting</label>
                                    <div className="form-group flex form-inline align-items-center">
                                        <input type="date"
                                               onChange={this.handleLastReviewMeetingDateUpdate}
                                               value={this.state.customer.lastReviewMeetingDate || ''}
                                               size="10"
                                               maxLength="10"
                                               className="form-control col-sm-4"
                                        />
                                        <label>Booked</label>
                                        <input type="checkbox"
                                               onChange={this.handleReviewMeetingBookedUpdate}
                                               checked={this.state.customer.reviewMeetingBooked}
                                               className="form-control"
                                        />
                                        <div className="col-sm-4">Frequency</div>
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
                                            className="form-control col-sm-4"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-6">
                                    <label htmlFor="">Lead Status</label>
                                    <Select
                                        options={this.state.leadStatuses}
                                        selectedOption={this.state.customer.leadStatusId || ''}
                                        onChange={this.handleLeadStatusIdUpdate}
                                        className="form-control"
                                    />
                                </div>
                                <div className="col-lg-6">
                                    <label>24 Hour Cover</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               checked={this.state.customer.support24HourFlag === 'Y'}
                                               onChange={this.handleSupport24HourFlagUpdate}
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-6">
                                    <label htmlFor="">Type</label>
                                    <div className="form-group">
                                        <Select options={this.state.customerTypes}
                                                className="form-control"
                                                selectedOption={this.state.customer.customerTypeID || ''}
                                                onChange={this.handleCustomerTypeUpdate}
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-6">
                                    <label htmlFor="">Sector</label>
                                    <div className="form-group">
                                        <Select options={this.state.sectors}
                                                selectedOption={this.state.customer.sectorID || ''}
                                                onChange={this.handleSectorIDUpdate}
                                                className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">PCs</label>
                                    <div className="form-group">
                                        <input type="number"
                                               value={this.state.customer.noOfPCs || ''}
                                               onChange={this.handleNoOfPCsUpdate}
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Servers</label>
                                    <div className="form-group">
                                        <input type="number"
                                               value={this.state.customer.noOfServers || ''}
                                               onChange={this.handleNoOfServersUpdate}
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Reg</label>
                                    <div className="form-group">
                                        <input type="text"
                                               value={this.state.customer.regNo || ''}
                                               onChange={this.handleRegNoUpdate}
                                               size="10"
                                               maxLength="10"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Sites</label>
                                    <div className="form-group">
                                        <input type="number"
                                               value={this.state.customer.noOfSites || ''}
                                               onChange={this.handleNoOfSitesUpdate}
                                               size="2"
                                               maxLength="2"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Pre-pay Top Up</label>
                                    <div className="form-group">
                                        <input type="text"
                                               value={this.state.customer.gscTopUpAmount || ''}
                                               onChange={this.handleGscTopUpAmountUpdate}
                                               size="10"
                                               maxLength="10"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Became Customer</label>
                                    <div className="form-group">

                                        <input type="date"
                                               value={this.state.customer.becameCustomerDate || ''}
                                               onChange={this.handleBecameCustomerDateUpdate}
                                               size="10"
                                               maxLength="10"
                                               className="form-control"
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
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                            </div>
                            <hr/>
                            <div className="row">
                                <div className="col-lg-12">
                                    <label htmlFor="">SLA Response Hours</label>
                                    <div className="form-group form-inline">
                                        <label style={{margin: "0 .5rem"}}>1</label>
                                        <input type="number"
                                               value={this.state.customer.slaP1 || ''}
                                               onChange={this.handleSLAP1Update}
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                        <label style={{margin: "0 .5rem"}}>2</label>
                                        <input type="number"
                                               value={this.state.customer.slaP2 || ''}
                                               onChange={this.handleSLAP2Update}
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                    </div>
                                    <div className="form-group form-inline">

                                        <label style={{margin: "0 .5rem"}}>3</label>
                                        <input type="number"
                                               value={this.state.customer.slaP3 || ''}
                                               onChange={this.handleSLAP3Update}
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                        <label style={{margin: "0 .5rem"}}>4</label>
                                        <input type="number"
                                               value={this.state.customer.slaP4 || ''}
                                               onChange={this.handleSLAP4Update}
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />

                                    </div>

                                    <div className="form-group form-inline">
                                        <label style={{margin: "0 .5rem"}}>5</label>
                                        <input type="number"
                                               value={this.state.customer.slaP5 || ''}
                                               onChange={this.handleSLAP5Update}
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
                                               onChange={this.handleSlaFixHoursP1}
                                               className="form-control col-sm-4"
                                        />
                                        <label style={{margin: "0 .5rem"}}>2</label>
                                        <input value={this.state.customer.slaFixHoursP2 || ''}
                                               type="number"
                                               size="1"
                                               step="0.1"
                                               maxLength="4"
                                               max="999.9"
                                               onChange={this.handleSlaFixHoursP2}
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
                                               onChange={this.handleSlaFixHoursP3}
                                               className="form-control col-sm-4"
                                        />
                                        <label style={{margin: "0 .5rem"}}>4</label>
                                        <input value={this.state.customer.slaFixHoursP4 || ''}
                                               type="number"
                                               size="1"
                                               step="0.1"
                                               maxLength="4"
                                               max="999.9"
                                               onChange={this.handleSlaFixHoursP4}
                                               className="form-control col-sm-4"
                                        />

                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">SLA Penalties Agreed</label>
                                    <div className="form-group form-inline">
                                        <label style={{margin: "0 .5rem"}}>1</label>
                                        <input type="checkbox"
                                               checked={this.state.customer.slaP1PenaltiesAgreed || ''}
                                               onChange={this.handleSlaP1PenaltiesAgreed}
                                               className="form-control"
                                        />
                                        <label style={{margin: "0 .5rem"}}>2</label>
                                        <input type="checkbox"
                                               checked={this.state.customer.slaP2PenaltiesAgreed || ''}
                                               onChange={this.handleSlaP2PenaltiesAgreed}
                                               className="form-control"
                                        />
                                        <label style={{margin: "0 .5rem"}}>3</label>
                                        <input type="checkbox"
                                               checked={this.state.customer.slaP3PenaltiesAgreed || ''}
                                               onChange={this.handleSlaP3PenaltiesAgreed}
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Last Modified:</label>
                                    <div className="form-group">
                                        <h6>{this.state.customer.lastModified}</h6>
                                    </div>
                                </div>
                            </div>
                            <hr/>
                            <div className="row">
                                <div className="col-lg-6">
                                    <label>Technical Notes</label>
                                    <div className="form-group">
                                <textarea className="form-control"
                                          cols="30"
                                          rows="2"
                                          value={this.state.customer.techNotes || ''}
                                          onChange={this.handleTechNotesUpdate}
                                />
                                    </div>
                                </div>

                                <div className="col-lg-6">
                                    <label>Active Directory Name</label>
                                    <div className="form-group">
                                        <input type="text"
                                               value={this.state.customer.activeDirectoryName || ''}
                                               onChange={this.handleActiveDirectoryNameUpdate}
                                               size="54"
                                               maxLength="255"
                                               className="form-control"
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
                                                className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">Sort Code</label>
                                    <div className="form-group">
                                        <EncryptedTextInput
                                            encryptedValue={this.state.customer.sortCode}
                                            onChange={this.handleSortCodeUpdate}
                                            mask='99-99-99'
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">Account Name</label>
                                    <div className="form-group">
                                        <input className="form-control"
                                               type='text'
                                               value={this.state.customer.accountName || ''}
                                               onChange={this.handleAccountNameUpdate}
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">Account Number</label>
                                    <div className="form-group">
                                        <EncryptedTextInput
                                            encryptedValue={this.state.customer.accountNumber}
                                            onChange={this.handleAccountNumberUpdate}
                                            mask='99999999'
                                        />
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div className="col-md-6">
                            <h4>Notes</h4>
                            <div className="row">
                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label htmlFor="reviewDate">
                                            To be reviewed on:
                                        </label>
                                        <input type="date"
                                               value={this.state.customer.reviewDate || ''}
                                               className="form-control"
                                               onChange={this.handleReviewDateUpdate}
                                        />
                                    </div>
                                </div>
                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label htmlFor="">Time:</label>
                                        <input type="time"
                                               value={this.state.customer.reviewTime || ''}
                                               className="form-control"
                                               onChange={this.handleReviewTimeUpdate}
                                        />
                                    </div>
                                </div>
                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label>By:</label>
                                        <Select
                                            options={this.state.reviewEngineers}
                                            selectedOption={this.state.customer.reviewUserID || ''}
                                            onChange={this.handleReviewUserIDUpdate}
                                            key='reviewUserID'
                                            className="form-control"
                                        />
                                    </div>
                                </div>
                            </div>


                            <div className="form-group customerReviewAction">
                                        <textarea title="Action to be taken"
                                                  cols="120"
                                                  rows="3"
                                                  value={this.state.customer.reviewAction || ''}
                                                  className="form-control"
                                                  onChange={this.handleReviewActionUpdate}
                                        />
                            </div>
                            <CustomerNotesComponent customerId={customerId}/>
                            <div>
                                {this.state.customer.lastContractSent}
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
            </div>
        )
    }

    isProspect() {
        return !(this.state.customer.becameCustomerDate && !this.state.customer.droppedCustomerDate);
    }
}

export default CustomerEditMain;