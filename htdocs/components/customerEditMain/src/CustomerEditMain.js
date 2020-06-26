"use strict";
import React from 'react';
import Select from "./Select";
import EncryptedTextInput from "./EncryptedTextInput";
import Skeleton from "react-loading-skeleton";
import ReactDOM from 'react-dom';

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
        document.customerMain = this;
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
                console.log('customer data saved');
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
        return this.el('tr', {valign: "top", key: key + "_tr"},
            [
                this.el('td', {className: 'content', key: key + "_content", width}, label),
                this.el('td', {className: 'content', key: key + "_input"}, input)
            ]
        )
    }

    getCustomerTypeSelect() {
        return this.el(Select, {
            options: this.state.customerTypes,
            selectedOption: this.state.customer.customerTypeID,
            key: 'customerTypes',
            onChange: this.handleCustomerTypeUpdate
        })
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
        console.log(this.state.customer.specialAttentionFlag, this.state.customer.specialAttentionEndDate);
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

    getSLAResponseHoursInput() {
        return [
            "1",
            this.el(
                'input',
                {
                    type: 'text',
                    value: this.state.customer.slaP1,
                    onChange: this.handleSLAP1Update,
                    key: 'SLAP1',
                    size: 1,
                    maxLength: 3
                }
            ),
            " 2",
            this.el(
                'input',
                {
                    type: 'text',
                    value: this.state.customer.slaP2,
                    onChange: this.handleSLAP2Update,
                    key: 'SLAP2',
                    size: 1,
                    maxLength: 3
                }
            ),
            " 3",
            this.el(
                'input',
                {
                    type: 'text',
                    value: this.state.customer.slaP3,
                    onChange: this.handleSLAP3Update,
                    key: 'SLAP3',
                    size: 1,
                    maxLength: 3
                }
            ),
            " 4",
            this.el(
                'input',
                {
                    type: 'text',
                    value: this.state.customer.slaP4,
                    onChange: this.handleSLAP4Update,
                    key: 'SLAP4',
                    size: 1,
                    maxLength: 3
                }
            ),
            " 5",
            this.el(
                'input',
                {
                    type: 'text',
                    value: this.state.customer.slaP5,
                    onChange: this.handleSLAP5Update,
                    key: 'SLAP5',
                    size: 1,
                    maxLength: 3
                }
            ),
        ];
    }

    handleTechNotesUpdate(event) {
        this.updateCustomerField('techNotes', event.target.value);
    }

    getTechNotesInput() {
        return this.el(
            'input',
            {
                type: 'text',
                value: this.state.customer.techNotes,
                onChange: this.handleTechNotesUpdate
            }
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
        if (!this.state.loaded) {
            return this.el(
                Skeleton,
                null,
                this.el(
                    'table',
                    {className: 'content', border: 0, cellPadding: 2, cellSpacing: 1, width: '100%'},
                    this.el('tbody')
                )
            );
        }

        return this.el('table', {className: 'content', border: 0, cellPadding: 2, cellSpacing: 1, width: '100%'},
            this.el('tbody', null,
                [
                    this.getInputRow('Customer ' + this.props.customerID, this.getCustomerNameInput(), 'name', '13%'),
                    this.getInputRow('Primary Main Contact', this.getPrimaryMainContactSelect(), 'primaryMainContact'),
                    this.getInputRow('Mailshot', this.getMailshotInput(), 'mailshotFlag'),
                    this.getInputRow('Referred', this.getReferredInput(), 'referredFlag'),
                    this.getInputRow('Special Attention', this.getSpecialAttentionInput(), 'specialAttention'),
                    this.getInputRow('Last Review Meeting', this.getLastReviewMeetingInput(), 'lastReviewMeeting'),
                    this.getInputRow('Lead Status', this.getLeadStatusInput(), 'leadStatus'),
                    this.getInputRow('24 Hour Cover', this.get24HourCoverInput(), '24HourCover'),
                    this.getInputRow('Type', this.getCustomerTypeSelect(), 'customerType'),
                    this.getInputRow('Sector', this.getSectorSelect(), 'sector'),
                    this.getInputRow('PCs', this.getPCsInput(), 'pcs'),
                    this.getInputRow('Servers', this.getServersInput(), 'servers'),
                    this.getInputRow('Reg', this.getRegNoInput(), 'reg'),
                    this.getInputRow('Sites', this.getNoOfSitesInput(), 'sites'),
                    this.getInputRow('Pre-pay Top Up', this.getGscTopUpAmountInput(), 'gscTopUpAmount'),
                    this.getInputRow('Became Customer', this.getBecameCustomerDateInput(), 'becameCustomerDate'),
                    this.getInputRow('SLA Response Hours', this.getSLAResponseHoursInput(), 'SLA Response Hours'),
                    this.getInputRow('Last Modified', this.state.customer.modifyDate, 'Last Modified'),
                    this.getInputRow('Technical Notes', this.getTechNotesInput(), 'Technical Notes'),
                    this.getInputRow('Active Directory Name', this.getActiveDirectoryNameInput(), 'Active Directory Name'),
                    this.getInputRow('Account Manager', this.getAccountManagerInput(), 'Account Manager'),
                    this.getInputRow('Sort Code', this.getSortCodeInput(), 'Sort Code'),
                    this.getInputRow('Account Name', this.getAccountNameInput(), 'Account Name'),
                    this.getInputRow('Account Number', this.getAccountNumberInput(), 'Account Number'),
                ]
            )
        )
    }

    isProspect() {
        return !(this.state.customer.becameCustomerDate && !this.state.customer.droppedCustomerDate);
    }
}

export default CustomerEditMain;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerEditMain');
    ReactDOM.render(React.createElement(CustomerEditMain, {customerID: domContainer.dataset.customerId}), domContainer);
});

