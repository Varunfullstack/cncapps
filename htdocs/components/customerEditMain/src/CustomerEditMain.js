"use strict";
import React from 'react';
import Select from "./Select";
import EncryptedTextInput from "./EncryptedTextInput";

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

        Promise.all([
            fetch('?action=getCustomer&customerID=' + this.props.customerID)
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
            fetch('?action=getMainContacts&customerID=' + this.props.customerID)
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
        ])
            .then(allLoaded => {
                this.setState({loaded: true});
            })

    }

    handleCustomerTypeUpdate(value) {
        this.updateCustomerField('customerTypeID', value);
    }

    getInputRow(label, input, key, width = null) {
        return (
            <tr valign="top"
                key={key + "_tr"}
            >

                <td className='content'
                    key={key + "_content"}
                    width={width}
                >
                    {label}
                </td>
                <td className='content'
                    key={key + "_input"}
                    width={width}
                >
                    {input}
                </td>
            </tr>
        )
    }

    getCustomerTypeSelect() {

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

    getPrimaryMainContactSelect() {
        return this.el(Select, {
            options: this.state.mainContacts,
            selectedOption: this.state.customer.primaryMainContactID,
            key: 'primaryMainContacts',
            onChange: this.handlePrimaryMainContactUpdate
        })
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

    getMailshotInput() {
        return this.el('input',
            {
                type: 'checkbox',
                name: 'mailshotFlag',
                checked: this.state.customer.mailshotFlag === 'Y',
                onChange: this.handleMailshotFlagUpdate,
                key: 'mailshotFlag'
            }
        )
    }

    getSpecialAttentionInput() {
        return [
            this.el('input', {
                type: 'checkbox',
                name: "specialAttentionFlag",
                onChange: this.handleSpecialAttentionFlagUpdate,
                key: 'specialAttentionFlag',
                checked: this.state.customer.specialAttentionFlag === 'Y'
            }),
            'until',
            this.el('input', {
                type: 'date',
                size: 10,
                maxLength: 10,
                value: this.state.customer.specialAttentionEndDate || '',
                onChange: this.handleSpecialAttentionDateUpdate,
                key: 'specialAttentionDate'
            })
        ];
    }

    getReferredInput() {
        return this.el(
            'input',
            {
                type: 'checkbox',
                name: 'referredFlag',
                checked: this.state.customer.referredFlag === 'Y',
                onChange: this.handleReferredFlagUpdate
            }
        )
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

    getLastReviewMeetingInput() {
        return [
            this.el('input', {
                type: 'date',
                onChange: this.handleLastReviewMeetingDateUpdate,
                value: this.state.customer.lastReviewMeetingDate || '',
                key: 'lastReviewMeetingDate'
            }),
            " Booked",
            this.el('input',
                {
                    type: "checkbox",
                    onChange: this.handleReviewMeetingBookedUpdate,
                    checked: this.state.customer.reviewMeetingBooked,
                    key: 'reviewMeetingBooked'
                }
            ),
            " Frequency",
            this.el(Select, {
                key: 'reviewMeetingFrequencyMonths',
                options: [
                    {label: 'Monthly', value: 1},
                    {label: "Two Monthly", value: 2},
                    {label: 'Quarterly', value: 3},
                    {label: "Six-Monthly", value: 6},
                    {label: 'Annually', value: 12}
                ],
                selectedOption: this.state.customer.reviewMeetingFrequencyMonths,
                onChange: this.handleReviewMeetingFrequencyMonthsUpdate
            })
        ]
    }


    handleLeadStatusIdUpdate(value) {
        this.updateCustomerField('leadStatusId', value);
    }

    getLeadStatusInput() {
        return this.el(Select, {
            key: 'leadStatuses',
            options: this.state.leadStatuses,
            selectedOption: this.state.customer.leadStatusId,
            onChange: this.handleLeadStatusIdUpdate
        })
    }

    handleSupport24HourFlagUpdate(event) {
        this.updateCustomerField('support24HourFlag', event.target.checked);
    }

    get24HourCoverInput() {
        return this.el('input', {
            key: '24HourCover',
            type: 'checkbox',
            checked: this.state.customer.support24HourFlag === 'Y',
            onChange: this.handleSupport24HourFlagUpdate
        })
    }

    handleNameUpdate(event) {
        this.updateCustomerField('name', event.target.value);
    }

    getCustomerNameInput() {
        return this.el(
            'input',
            {
                key: 'nameInput',
                type: 'text',
                value: this.state.customer.name,
                onChange: this.handleNameUpdate,
            }
        )
    }

    handleSectorIDUpdate(event) {
        this.updateCustomerField('sectorID', event.target.value);
    }

    getSectorSelect() {
        return this.el(
            Select,
            {
                options: this.state.sectors,
                selectedOption: this.state.customer.sectorID,
                onChange: this.handleSectorIDUpdate,
                key: 'sectorSelect'
            }
        )
    }

    handleNoOfPCsUpdate(event) {
        this.updateCustomerField('noOfPCs', event.target.value);
    }

    getPCsInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.noOfPCs,
                onChange: this.handleNoOfPCsUpdate
            }
        )
    }

    handleNoOfServersUpdate(event) {
        this.updateCustomerField('noOfServers', event.target.value);
    }

    getServersInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.noOfServers,
                onChange: this.handleNoOfServersUpdate
            }
        )
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

    getRegNoInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.regNo,
                onChange: this.handleRegNoUpdate
            }
        )
    }

    getNoOfSitesInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.noOfSites,
                onChange: this.handleNoOfSitesUpdate
            }
        )
    }

    getGscTopUpAmountInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.gscTopUpAmount,
                onChange: this.handleGscTopUpAmountUpdate
            }
        )
    }

    getBecameCustomerDateInput() {
        return this.el(
            'input',
            {
                type: 'date',
                value: this.state.customer.becameCustomerDate,
                onChange: this.handleBecameCustomerDateUpdate
            }
        )
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

    getSLAFixHoursInputs() {
        return (
            <React.Fragment>
                1
                <input name="slaFixHoursP1"
                       value={this.state.customer.slaFixHoursP1}
                       type="number"
                       size="1"
                       step="0.1"
                       maxLength="4"
                       max="999.9"
                       style={{width: "50px"}}
                       onChange={this.handleSlaFixHoursP1}
                />
                2
                <input name="slaFixHoursP2"
                       value={this.state.customer.slaFixHoursP2}
                       type="number"
                       size="1"
                       step="0.1"
                       maxLength="4"
                       max="999.9"
                       style={{width: "50px"}}
                       onChange={this.handleSlaFixHoursP2}
                />
                3
                <input name="slaFixHoursP3"
                       value={this.state.customer.slaFixHoursP3}
                       type="number"
                       size="1"
                       step="0.1"
                       maxLength="4"
                       max="999.9"
                       style={{width: "50px"}}
                       onChange={this.handleSlaFixHoursP3}
                />
                4
                <input name="slaFixHoursP4"
                       value={this.state.customer.slaFixHoursP4}
                       type="number"
                       size="1"
                       step="0.1"
                       maxLength="4"
                       max="999.9"
                       style={{width: "50px"}}
                       onChange={this.handleSlaFixHoursP4}
                />
            </React.Fragment>
        )
    }

    getSLAPenaltiesAgreedInputs() {
        return (
            <React.Fragment>
                1
                <input type="checkbox"
                       name="slaP1PenaltiesAgreed"
                       checked={this.state.customer.slaP1PenaltiesAgreed}
                       onChange={this.handleSlaP1PenaltiesAgreed}
                       value="1"
                />
                2
                <input type="checkbox"
                       name="slaP2PenaltiesAgreed"
                       checked={this.state.customer.slaP2PenaltiesAgreed}
                       onChange={this.handleSlaP2PenaltiesAgreed}
                       value="1"
                />
                3
                <input type="checkbox"
                       name="slaP3PenaltiesAgreed"
                       checked={this.state.customer.slaP3PenaltiesAgreed}
                       onChange={this.handleSlaP3PenaltiesAgreed}
                       value="1"
                />
            </React.Fragment>
        )
    }


    getSLAResponseHoursInput() {
        return (
            <React.Fragment>
                1
                <input type="text"
                       value={this.state.customer.slaP1}
                       onChange={this.handleSLAP1Update}
                       key="SLAP1"
                       size="1"
                       maxLength="3"
                />
                2
                <input type="text"
                       value={this.state.customer.slaP2}
                       onChange={this.handleSLAP2Update}
                       key="SLAP2"
                       size="1"
                       maxLength="3"
                />
                3
                <input type=" text"
                       value={this.state.customer.slaP3}
                       onChange={this.handleSLAP3Update}
                       key="SLAP3"
                       size="1"
                       maxLength="3"

                />
                4
                <input type="text"
                       value={this.state.customer.slaP4}
                       onChange={this.handleSLAP4Update}
                       key="SLAP4"
                       size="1"
                       maxLength="3"
                />
                5
                <input type="text"
                       value={this.state.customer.slaP5}
                       onChange={this.handleSLAP5Update}
                       key="SLAP5"
                       size="1"
                       maxLength="3"
                />
            </React.Fragment>
        );
    }

    handleTechNotesUpdate(event) {
        this.updateCustomerField('techNotes', event.target.value);
    }

    getTechNotesInput() {
        return (
            <input
                type="text"
                value={this.state.customer.techNotes}
                onChange={this.handleTechNotesUpdate}
            />
        )
    }

    handleActiveDirectoryNameUpdate(event) {
        this.updateCustomerField('activeDirectoryName', event.target.value);
    }

    getActiveDirectoryNameInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.activeDirectoryName,
                onChange: this.handleActiveDirectoryNameUpdate
            }
        )
    }

    handleAccountManagerUserIDUpdate(event) {
        this.updateCustomerField('accountManagerUserID', event.target.value);
    }

    getAccountManagerInput() {
        return this.el(
            Select,
            {
                options: this.state.accountManagers,
                selectedOption: this.state.customer.accountManagerUserID,
                onChange: this.handleAccountManagerUserIDUpdate,
                key: 'accountManager'
            }
        )
    }

    handleSortCodeUpdate(value) {
        this.updateCustomerField('sortCode', value);
    }

    getSortCodeInput() {
        return this.el(
            EncryptedTextInput,
            {
                encryptedValue: this.state.customer.sortCode,
                onChange: this.handleSortCodeUpdate,
                mask: '99-99-99'
            }
        )
    }

    handleAccountNameUpdate(event) {
        this.updateCustomerField('accountName', event.target.value);
    }

    getAccountNameInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.accountName,
                onChange: this.handleAccountNameUpdate
            }
        )

    }

    handleAccountNumberUpdate(value) {
        this.updateCustomerField('accountNumber', value);
    }

    getAccountNumberInput() {
        return this.el(
            EncryptedTextInput,
            {
                encryptedValue: this.state.customer.accountNumber,
                onChange: this.handleAccountNumberUpdate,
                mask: '99999999'
            }
        )
    }

    render() {
        const {customerId} = this.props.customerId;
        return (
            <div className="tab-pane fade show active"
                 id="nav-home"
                 role="tabpanel"
                 aria-labelledby="nav-home-tab"
            >
                <div className="container-fluid mt-3 mb-3">
                    <div className="row">
                        <div className="col-md-6 mb-3">
                            <h2>`Customer-SussexIndependentFinanceAdvisersLtd.`</h2>
                        </div>
                        <div className="col-md-6 mb-3">
                            <ul className="list-style-none float-right">
                                <li>
                                    <button className="btn btn-outline-success">Save</button>
                                    <button className="btn btn-outline-danger">Cancel</button>
                                    <button className="btn btn-outline-secondary">Set all
                                        users to no support
                                    </button>
                                    <button className="btn btn-outline-secondary">
                                        <i className="fa fa-filter"/>
                                    </button>
                                    <button className="btn btn-outline-secondary">
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
                                        <input
                                            name="form[customer][{customerID}][name]"
                                            type="text"
                                            value="{customerName}"
                                            size="50"
                                            maxLength="50"
                                            className="form-control"
                                        />
                                    </div>
                                </div>

                                <div className="col-lg-6">
                                    <label htmlFor="">Primary Main Contact</label>
                                    <div className="form-group">
                                        <select id="primaryMainContactSelector"
                                                name="form[customer][{customerID}][primaryMainContactID]"
                                                className="form-control"
                                        >
                                            <option value="">
                                                Select a contact to be the Primary Main
                                            </option>

                                            <option value="{primaryMainContactValue}"
                                            >
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div className="col-lg-3">
                                    <label>Mailshot</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               name="form[customer][{customerID}][mailshotFlag]"
                                               value="Y"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-3">
                                    <label>Referred</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               name="form[customer][{customerID}][referredFlag]"
                                               value="Y"
                                               id="referred"
                                               className="form-control"
                                        />
                                    </div>
                                </div>

                                <div className="col-lg-6">
                                    <label htmlFor="">Special Attention</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               name="form[customer][{customerID}][specialAttentionFlag]"
                                               value="Y"
                                               className="form-control"
                                        />
                                        <div className="col-sm-4">until</div>
                                        <input type="text"
                                               name="form[customer][{customerID}][specialAttentionEndDate]"
                                               id="specialAttentionEndDate"
                                               value="{specialAttentionEndDate}"
                                               size="10"
                                               maxLength="10"
                                               autoComplete="off"
                                               className="jQueryCalendar form-control"
                                        />
                                    </div>
                                </div>

                                <div className="col-lg-12">
                                    <label htmlFor="">Last Review Meeting</label>
                                    <div className="form-group flex form-inline align-items-center">
                                        <input
                                            type="text"
                                            name="form[customer][{customerID}][lastReviewMeetingDate]"
                                            id="lastReviewMeetingDate"
                                            value="{lastReviewMeetingDate}"
                                            size="10"
                                            maxLength="10"
                                            autoComplete="off"
                                            className="jQueryCalendar form-control col-sm-4"
                                        />

                                        <div className="col-sm-4">Frequency</div>
                                        <select
                                            name="form[customer][{customerID}][reviewMeetingFrequencyMonths]"
                                            className="form-control col-sm-4"
                                        >

                                            <option
                                                value="{reviewMeetingFrequencyMonths}"
                                            >

                                            </option>

                                        </select>
                                        <span className="formErrorMessage"/>
                                    </div>
                                </div>

                                <div className="col-lg-6">
                                    <label htmlFor="">Lead Status</label>
                                    <select
                                        name=""
                                        className="form-control"
                                    >
                                        <option
                                            value="{customer}"
                                        >

                                        </option>
                                    </select>
                                </div>
                                <div className="col-lg-6">
                                    <label>24 Hour Cover</label>
                                    <div className="form-group form-inline">
                                        <input type="checkbox"
                                               name="form[customer][{customerID}][support24HourFlag]"
                                               value="Y"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-6">
                                    <label htmlFor="">Type</label>
                                    <div className="form-group">
                                        <Select options={this.state.customerTypes}
                                                className="form-control"
                                                selectedOption={this.state.customer.customerTypeID}
                                                onChange={this.handleCustomerTypeUpdate}
                                                key="customerTypes"
                                        />
                                    </div>
                                </div>

                                {/*<div className="col-lg-6">*/}
                                {/*    <label htmlFor="">Sector</label>*/}
                                {/*    <div className="form-group">*/}
                                {/*        <select name="form[customer][{customerID}][sectorID]"*/}
                                {/*                className="form-control"*/}
                                {/*        >*/}
                                {/*            <option value="">Please select</option>*/}

                                {/*            <option value="{sectorID}"*/}
                                {/*            >{sectorDescription}*/}
                                {/*            </option>*/}
                                {/*        </select>*/}
                                {/*        <span className="formErrorMessage">{SectorMessage}</span>*/}
                                {/*    </div>*/}
                                {/*</div>*/}

                                {/*<div className="col-lg-4">*/}
                                {/*    <label htmlFor="">PCs</label>*/}
                                {/*    <div className="form-group">*/}
                                {/*        <select name="form[customer][{customerID}][noOfPCs]"*/}
                                {/*                className="form-control"*/}
                                {/*        >*/}
                                {/*            <option value="{noOfPCsValue}"*/}
                                {/*            >{noOfPCsValue}*/}
                                {/*            </option>*/}
                                {/*        </select>*/}
                                {/*    </div>*/}
                                {/*</div>*/}

                                {/*<div className="col-lg-4">*/}
                                {/*    <label>Servers</label>*/}
                                {/*    <div className="form-group">*/}
                                {/*        <input name="form[customer][{customerID}][noOfServers]"*/}
                                {/*               type="text"*/}
                                {/*               value="{noOfServers}"*/}
                                {/*               size="10"*/}
                                {/*               maxLength="10"*/}
                                {/*               className="form-control"*/}
                                {/*        />*/}
                                {/*    </div>*/}
                                {/*</div>*/}

                                {/*<div className="col-lg-4">*/}
                                {/*    <label>Reg</label>*/}
                                {/*    <div className="form-group">*/}
                                {/*        <input name="form[customer][{customerID}][regNo]"*/}
                                {/*               type="text"*/}
                                {/*               value="{regNo}"*/}
                                {/*               size="10"*/}
                                {/*               maxLength="10"*/}
                                {/*               className="form-control"*/}
                                {/*        />*/}
                                {/*    </div>*/}
                                {/*</div>*/}
                                <div className="col-lg-4">
                                    <label>Sites</label>
                                    <div className="form-group">
                                        <input name="form[customer][{customerID}][noOfSites]"
                                               type="text"
                                               value="{noOfSites}"
                                               size="2"
                                               maxLength="2"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>`Pre - pay Top Up`</label>
                                    <div className="form-group">
                                        <input name="form[customer][{customerID}][gscTopUpAmount]"
                                               type="text"
                                               value="{gscTopUpAmount}"
                                               size="10"
                                               maxLength="10"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Became Customer</label>
                                    <div className="form-group">
                                        <input name="form[customer][{customerID}][becameCustomerDate]"
                                               type="text"
                                               value="{becameCustomerDate}"
                                               size="10"
                                               maxLength="10"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Dropped Date</label>
                                    <div className="form-group">
                                        <input name="form[customer][{customerID}][droppedCustomerDate]"
                                               type="text"
                                               value="{droppedCustomerDate}"
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
                                        <label style="margin: 0 .5rem">1</label>
                                        <input name="form[customer][{customerID}][slaP1]"
                                               type="text"
                                               value="{slaP1}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                        <label style="margin: 0 .5rem">2</label>
                                        <input name="form[customer][{customerID}][slaP2]"
                                               type="text"
                                               value="{slaP2}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                    </div>
                                    <div className="form-group form-inline">

                                        <label style="margin: 0 .5rem">3</label>
                                        <input name="form[customer][{customerID}][slaP3]"
                                               type="text"
                                               value="{slaP3}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                        <label style="margin: 0 .5rem">4</label>
                                        <input name="form[customer][{customerID}][slaP4]"
                                               type="text"
                                               value="{slaP4}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"

                                        />

                                    </div>

                                    <div className="form-group form-inline">
                                        <label style="margin: 0 .5rem">5</label>
                                        <input name="form[customer][{customerID}][slaP5]"
                                               type="text"
                                               value="{slaP5}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                    </div>
                                </div>

                                <div className="col-lg-12">
                                    <label htmlFor="">SLA Response Fix Hours</label>
                                    <div className="form-group form-inline">
                                        <label style="margin: 0 .5rem">1</label>
                                        <input name="form[customer][{customerID}][slaP1]"
                                               type="text"
                                               value="{slaP1}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                        <label style="margin: 0 .5rem">2</label>
                                        <input name="form[customer][{customerID}][slaP2]"
                                               type="text"
                                               value="{slaP2}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                    </div>
                                    <div className="form-group form-inline">
                                        <label style="margin: 0 .5rem">3</label>
                                        <input name="form[customer][{customerID}][slaP3]"
                                               type="text"
                                               value="{slaP3}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />
                                        <label style="margin: 0 .5rem">4</label>
                                        <input name="form[customer][{customerID}][slaP4]"
                                               type="text"
                                               value="{slaP4}"
                                               size="1"
                                               maxLength="3"
                                               className="form-control col-sm-4"
                                        />

                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">SLA Penalties Agreed</label>
                                    <div className="form-group form-inline">
                                        <label style="margin: 0 .5rem">1</label>
                                        <input type="checkbox"
                                               id="slaP1"
                                               className="form-control"
                                        />
                                        <label style="margin: 0 .5rem">2</label>
                                        <input type="checkbox"
                                               id="slaP2"
                                               className="form-control"
                                        />
                                        <label style="margin: 0 .5rem">3</label>
                                        <input type="checkbox"
                                               id="slaP3"
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
                                                  name="form[customer][{customerID}][techNotes]"
                                                  id="techNotes"
                                        >{this.state.customer.techNotes}</textarea>
                                    </div>
                                </div>

                                <div className="col-lg-6">
                                    <label>Active Directory Name</label>
                                    <div className="form-group">
                                        <input type="text"
                                               name="form[customer][{customerID}][activeDirectoryName]"
                                               value="{activeDirectoryName}"
                                               size="54"
                                               maxLength="255"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label>Account Manager</label>
                                    <div className="form-group">
                                        <
                                        <select name="form[customer][{customerID}][accountManagerUserID]"
                                                onChange="setFormChanged();"
                                                className="form-control"
                                        >
                                            <option value="{accountManagerUserID}"
                                            >
                                                {accountManagerUserName}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">Sort Code</label>
                                    <div className="form-group">
                                        <button type="button"
                                                className="form-control"
                                                onClick="editEncrypted('sortCode',this)"
                                        >
                                            <i className="fa fa-pencil-alt {sortCodePencilColor}">
                                            </i>
                                        </button>
                                        <input type="hidden"
                                               name="form[customer][{customerID}][sortCode]"
                                               value="{sortCode}"
                                               className="encrypted form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">Account Name</label>
                                    <div className="form-group">
                                        <input type="text"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-lg-4">
                                    <label htmlFor="">Account Number</label>
                                    <div className="form-group">
                                        <button type="button"
                                                onClick="editEncrypted('accountNumber',this)"
                                                className="form-control"
                                        >
                                            <i className="fa fa-pencil-alt {accountNumberPencilColor}">
                                            </i>
                                        </button>
                                        <input type="hidden"
                                               name="form[customer][{customerID}][accountNumber]"
                                               value="{accountNumber}"
                                               className="encrypted form-control"
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
                                        <label htmlFor="reviewDate">To be received
                                            on:</label>
                                        <input type="text"
                                               name="form[customer][{customerID}][reviewDate]"
                                               id="reviewDate"
                                               value="{reviewDate}"
                                               maxLength="10"
                                               autoComplete="off"
                                               className="jQueryCalendar form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label htmlFor="">Time:</label>
                                        <input name="form[customer][{customerID}][reviewTime]"
                                               value="{reviewTime}"
                                               size="5"
                                               maxLength="5"
                                               className="form-control"
                                        />
                                    </div>
                                </div>
                                <div className="col-md-4">
                                    <div className="form-group">

                                        <label>By:</label>
                                        <select name="form[customer][{customerID}][reviewUserID]"
                                                onChange="setFormChanged();"
                                                className="form-control"
                                        >
                                            <option value="{reviewUserID}"
                                            >{reviewUserName}
                                            </option>
                                        </select>
                                        <span
                                            className="formErrorMessage formError"
                                        >{reviewTimeMessage}</span>
                                    </div>

                                </div>
                            </div>


                            <div className="form-group customerReviewAction">
                                        <textarea title="Action to be taken"
                                                  cols="120"
                                                  rows="3"
                                                  name="form[customer][{customerID}][reviewAction]"
                                                  className="form-control"
                                        >{reviewAction}</textarea>
                            </div>
                            <div className="form-group customerNoteHistory">
                                                            <textarea cols="30"
                                                                      rows="12"
                                                                      readOnly="readonly"
                                                                      id="customerNoteHistory"
                                                                      className="form-control"
                                                            > </textarea>
                                <div className="customerNoteNav mt-3 mb-3">
                                    <button type="button"
                                            name="First"
                                            aria-hidden="true"
                                            onClick="loadNote('first')"
                                            className="btn btn-outline-secondary"
                                    >
                                        <i className="fa fa-step-backward">
                                        </i> First
                                    </button>

                                    <button type="button"
                                            name="Previous"
                                            onClick="loadNote('previous')"
                                            className="btn btn-outline-secondary"
                                    >
                                        <i className="fa fa-backward"
                                           aria-hidden="true"
                                        >
                                        </i> Back
                                    </button>
                                    <button type="button"
                                            name="Next"
                                            onClick="loadNote('next')"
                                            className="btn btn-outline-secondary"
                                    >
                                        Next <i className="fa fa-forward"
                                                aria-hidden="true"
                                    >
                                    </i>
                                    </button>

                                    <button type="button"
                                            name="Last"
                                            onClick="loadNote('last')"
                                            className="btn btn-outline-secondary"
                                    >
                                        Last <i className="fa fa-step-forward"
                                                aria-hidden="true"
                                    >
                                    </i>
                                    </button>
                                    <button type="button"
                                            name="Delete"
                                            onClick="deleteNote()"
                                            className="btn btn-outline-danger"
                                    >
                                        <i className="fa fa-trash"
                                           aria-hidden="true"
                                        >
                                        </i>
                                        Delete
                                    </button>
                                    <button type="button"
                                            name="New"
                                            onClick="newNote()"
                                            className="btn btn-outline-secondary"
                                    >
                                        <i className="fa fa-plus-circle"
                                           aria-hidden="true"
                                        >
                                        </i>
                                        New
                                    </button>
                                    <button type="button"
                                            name="Save"
                                            onClick="saveNote()"
                                            className="btn btn-outline-secondary"
                                    >
                                        <i className="fa fa-floppy-o"
                                           aria-hidden="true"
                                        >
                                        </i>
                                        Save
                                    </button>

                                </div>
                                {customerNotePopupLink}
                            </div>
                            <div className="form-group customerNoteDetails">
                                                            <textarea name="customerNoteDetails"
                                                                      id="customerNoteDetails"
                                                                      cols="120"
                                                                      onChange="setCustomerNotesChanged()"
                                                                      rows="12"
                                                                      className="form-control"
                                                            >{customerNoteDetails}
                                                            </textarea>
                            </div>
                            <div>
                                {lastContractSent}
                            </div>
                        </div>

                    </div>
                    <hr/>
                </div>

            </div>
        );


        // if (!this.state.loaded) {
        //     return this.el(
        //         Skeleton,
        //         null,
        //         this.el(
        //             'table',
        //             {className: 'content', border: 0, cellPadding: 2, cellSpacing: 1, width: '100%'},
        //             this.el('tbody')
        //         )
        //     );
        // }

        // return this.el('table', {className: 'content', border: 0, cellPadding: 2, cellSpacing: 1, width: '100%'},
        //     this.el('tbody', null,
        //         [
        //             this.getInputRow('Customer ' + this.props.customerID, this.getCustomerNameInput(), 'name', '13%'),
        //             this.getInputRow('Primary Main Contact', this.getPrimaryMainContactSelect(), 'primaryMainContact'),
        //             this.getInputRow('Mailshot', this.getMailshotInput(), 'mailshotFlag'),
        //             this.getInputRow('Referred', this.getReferredInput(), 'referredFlag'),
        //             this.getInputRow('Special Attention', this.getSpecialAttentionInput(), 'specialAttention'),
        //             this.getInputRow('Last Review Meeting', this.getLastReviewMeetingInput(), 'lastReviewMeeting'),
        //             this.getInputRow('Lead Status', this.getLeadStatusInput(), 'leadStatus'),
        //             this.getInputRow('24 Hour Cover', this.get24HourCoverInput(), '24HourCover'),
        //             this.getInputRow('Type', this.getCustomerTypeSelect(), 'customerType'),
        //             this.getInputRow('Sector', this.getSectorSelect(), 'sector'),
        //             this.getInputRow('PCs', this.getPCsInput(), 'pcs'),
        //             this.getInputRow('Servers', this.getServersInput(), 'servers'),
        //             this.getInputRow('Reg', this.getRegNoInput(), 'reg'),
        //             this.getInputRow('Sites', this.getNoOfSitesInput(), 'sites'),
        //             this.getInputRow('Pre-pay Top Up', this.getGscTopUpAmountInput(), 'gscTopUpAmount'),
        //             this.getInputRow('Became Customer', this.getBecameCustomerDateInput(), 'becameCustomerDate'),
        //             this.getInputRow('SLA Response Hours', this.getSLAResponseHoursInput(), 'SLA Response Hours'),
        //             this.getInputRow('SLA Fix Hours', this.getSLAFixHoursInputs(), 'SLA Fix Hours'),
        //             this.getInputRow('Penalties Agreed', this.getSLAPenaltiesAgreedInputs(), 'Penalties Agreed'),
        //             this.getInputRow('Last Modified', this.state.customer.modifyDate, 'Last Modified'),
        //             this.getInputRow('Technical Notes', this.getTechNotesInput(), 'Technical Notes'),
        //             this.getInputRow('Active Directory Name', this.getActiveDirectoryNameInput(), 'Active Directory Name'),
        //             this.getInputRow('Account Manager', this.getAccountManagerInput(), 'Account Manager'),
        //             this.getInputRow('Sort Code', this.getSortCodeInput(), 'Sort Code'),
        //             this.getInputRow('Account Name', this.getAccountNameInput(), 'Account Name'),
        //             this.getInputRow('Account Number', this.getAccountNumberInput(), 'Account Number'),
        //         ]
        //     )
        // )
    }

    isProspect() {
        return !(this.state.customer.becameCustomerDate && !this.state.customer.droppedCustomerDate);
    }
}

export default CustomerEditMain;