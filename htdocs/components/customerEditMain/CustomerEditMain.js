"use strict";

import Select from "../utils/Select.js";

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
                comments: '',
                companyBackground: '',
                createDate: '',
                crmComments: '',
                customerID: '',
                customerTypeID: '',
                dateMeetingConfirmed: '',
                decisionMakerBackground: '',
                deliverSiteNo: '',
                droppedCustomerDate: '',
                gscTopUpAmount: '',
                inviteSent: '',
                invoiceSiteNo: '',
                lastContractSent: '',
                lastReviewMeetingDate: '',
                leadStatusId: '',
                licensedOffice365Users: '',
                mailshotFlag: '',
                meetingDateTime: '',
                modifyDate: '',
                modifyUserID: '',
                name: '',
                noOfPCs: '',
                noOfServers: '',
                noOfSites: '',
                opportunityDeal: '',
                pcxFlag: '',
                primaryMainContactID: '',
                rating: '',
                referredFlag: '',
                regNo: '',
                reportProcessed: '',
                reportSent: '',
                reviewAction: '',
                reviewDate: '',
                reviewMeetingBooked: '',
                reviewMeetingEmailSentFlag: '',
                reviewMeetingFrequencyMonths: '',
                reviewTime: '',
                reviewUserID: '',
                sectorID: '',
                sendContractEmail: '',
                sendTandcEmail: '',
                slaFixHoursP1: '',
                slaFixHoursP2: '',
                slaFixHoursP3: '',
                slaFixHoursP4: '',
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
                        label: x.cns_consno,
                        value: x.cns_name
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

    getSortCodeInput() {

    }

    getAccountNameInput() {

    }

    getAccountNumberInput() {

    }


    render() {
        if (!this.state.loaded) {
            return '';
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

        // return "<table class=\"content\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\" width=\"100%\">\n" +
        //     "                <tbody><tr valign=\"top\">\n" +
        //     "                    <td class=\"content\" width=\"13%\">Customer 1939\n" +
        //     "                    </td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input name=\"form[customer][1939][name]\" type=\"text\" value=\"Stephen Rimmer LLP\" size=\"50\" maxlength=\"50\">\n" +
        //     "                    </td>\n" +
        //     "\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Primary Main Contact</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <select id=\"primaryMainContactSelector\" name=\"form[customer][1939][primaryMainContactID]\" required=\"required\">\n" +
        //     "                            <option value=\"\">Select a contact to be the Primary Main</option>\n" +
        //     "\n" +
        //     "                            <option value=\"4571\">\n" +
        //     "                                Diane Ash\n" +
        //     "                            </option>\n" +
        //     "                            <option selected=\"\" value=\"12702\">\n" +
        //     "                                Mark Poulton\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"17689\">\n" +
        //     "                                Grant Sanders\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"18546\">\n" +
        //     "                                Rebecca Bryan\n" +
        //     "                            </option>\n" +
        //     "                        </select>\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Mailshot</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"checkbox\" name=\"form[customer][1939][mailshotFlag]\" value=\"Y\" checked=\"\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Referred</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"checkbox\" name=\"form[customer][1939][referredFlag]\" value=\"Y\" id=\"referred\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Special Attention</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"checkbox\" name=\"form[customer][1939][specialAttentionFlag]\" value=\"Y\">\n" +
        //     "                        until\n" +
        //     "                        <input type=\"date\" name=\"form[customer][1939][specialAttentionEndDate]\" id=\"specialAttentionEndDate\" value=\"\" size=\"10\" maxlength=\"10\" autocomplete=\"off\">\n" +
        //     "                        <span class=\"formErrorMessage\"></span>\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Last Review Meeting</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"date\" name=\"form[customer][1939][lastReviewMeetingDate]\" id=\"lastReviewMeetingDate\" value=\"2019-08-21\" size=\"10\" maxlength=\"10\" autocomplete=\"off\" onchange=\"clearReviewMeetingBooked()\">\n" +
        //     "                        Booked <input type=\"checkbox\" name=\"form[customer][1939][reviewMeetingBooked]\" id=\"reviewMeetingBooked\" checked=\"\" value=\"1\">\n" +
        //     "                        Frequency\n" +
        //     "                        <select name=\"form[customer][1939][reviewMeetingFrequencyMonths]\">\n" +
        //     "\n" +
        //     "                            <option value=\"1\">\n" +
        //     "                                Monthly\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"2\">\n" +
        //     "                                Two Monthly\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"3\">\n" +
        //     "                                Quarterly\n" +
        //     "                            </option>\n" +
        //     "                            <option selected=\"\" value=\"6\">\n" +
        //     "                                Six-monthly\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"12\">\n" +
        //     "                                Annually\n" +
        //     "                            </option>\n" +
        //     "                        </select>\n" +
        //     "                        <span class=\"formErrorMessage\"></span>\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Lead Status</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <select name=\"form[customer][1939][leadStatusId]\">\n" +
        //     "                            <option value=\"\">None</option>\n" +
        //     "\n" +
        //     "                            <option value=\"1\">Lead\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"2\">Meeting\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"3\">Audit\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"4\">Proposal\n" +
        //     "                            </option>\n" +
        //     "                            <option selected=\"\" value=\"5\">Customer\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"6\">Dead\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"7\">Misc\n" +
        //     "                            </option>\n" +
        //     "                        </select>\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">24 Hour Cover</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"checkbox\" name=\"form[customer][1939][support24HourFlag]\" value=\"Y\" checked=\"\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Type</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <select name=\"form[customer][1939][customerTypeID]\">\n" +
        //     "\n" +
        //     "                            <option value=\"53\">2nd Site\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"6\">3CX\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"8\">5 Rings Telecom\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"4\">Business Networking\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"9\">Cellular\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"54\">Customer Referral\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"14\">Global4\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"5\">KSD Referral\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"26\">Leasing Company\n" +
        //     "                            </option>\n" +
        //     "                            <option selected=\"\" value=\"25\">Legacy\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"11\">LinkedIn/Social Media\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"27\">Microsoft\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"51\">Newsletter\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"52\">One To One\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"2\">Overline\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"13\">PavWeb Referral\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"50\">QSSD\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"34\">SonicWall\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"48\">Staff Referral\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"1\">Starkey Associates\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"10\">Sussex Business Times\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"47\">Telemarketing\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"15\">VazonTech\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"7\">Website\n" +
        //     "                            </option>\n" +
        //     "                        </select>\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Sector</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <select name=\"form[customer][1939][sectorID]\">\n" +
        //     "                            <option value=\"\">Please select</option>\n" +
        //     "\n" +
        //     "                            <option value=\"16\">Accountants\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"1\">Agriculture/Mining\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"19\">Architects\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"22\">Business &amp; Management\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"14\">Charity\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"10\">Chartered Surveyors\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"9\">Construction/Development\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"27\">Education\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"18\">Engineering\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"2\">Financial/Banking\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"28\">Healthcare\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"21\">Insurance\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"17\">Leisure\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"3\">Manufacturing\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"11\">Marketing\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"25\">Media Sevices\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"20\">Printers\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"4\">Property Management\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"13\">Public Relations\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"26\">Publishers\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"12\">Recruitment\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"5\">Retailers/Distributors\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"7\">Service Business\n" +
        //     "                            </option>\n" +
        //     "                            <option selected=\"\" value=\"6\">Solicitors\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"15\">Telecoms\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"8\">Transportation\n" +
        //     "                            </option>\n" +
        //     "                        </select>\n" +
        //     "                        <span class=\"formErrorMessage\"></span>\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">PCs</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"number\" min=\"0\" step=\"1\" name=\"form[customer][1939][noOfPCs]\" value=\"83\" size=\"10\" maxlength=\"10\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Servers</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input name=\"form[customer][1939][noOfServers]\" type=\"number\" value=\"6\" size=\"10\" maxlength=\"10\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Reg</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input name=\"form[customer][1939][regNo]\" type=\"text\" value=\"OC340622\" size=\"10\" maxlength=\"10\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Sites</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input name=\"form[customer][1939][noOfSites]\" type=\"text\" value=\"1\" size=\"2\" maxlength=\"2\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Pre-pay Top Up</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input name=\"form[customer][1939][gscTopUpAmount]\" type=\"text\" value=\"500\" size=\"10\" maxlength=\"10\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Became Customer</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input name=\"form[customer][1939][becameCustomerDate]\" id=\"becameCustomerDate\" type=\"date\" value=\"2001-08-14\" size=\"10\" maxlength=\"10\" onchange=\"checkIsProspect()\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Dropped Date</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input name=\"form[customer][1939][droppedCustomerDate]\" id=\"droppedCustomerDate\" type=\"date\" value=\"\" size=\"10\" maxlength=\"10\" onchange=\"checkIsProspect()\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">SLA Response Hours</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        1<input name=\"form[customer][1939][slaP1]\" type=\"text\" value=\"5\" size=\"1\" maxlength=\"3\">\n" +
        //     "                        2<input name=\"form[customer][1939][slaP2]\" type=\"text\" value=\"10\" size=\"1\" maxlength=\"3\">\n" +
        //     "                        3<input name=\"form[customer][1939][slaP3]\" type=\"text\" value=\"15\" size=\"1\" maxlength=\"3\">\n" +
        //     "                        4<input name=\"form[customer][1939][slaP4]\" type=\"text\" value=\"24\" size=\"1\" maxlength=\"3\">\n" +
        //     "                        5<input name=\"form[customer][1939][slaP5]\" type=\"text\" value=\"8\" size=\"1\" maxlength=\"3\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Last Modified</td>\n" +
        //     "                    <td class=\"content\">2020-06-18 14:28:27</td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td colspan=\"2\" class=\"content\">\n" +
        //     "\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Technical Notes</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"text\" name=\"form[customer][1939][techNotes]\" id=\"techNotes\" value=\"All remote work on printer related issues is to be capped at 1 hour before agreeing a site visit. Pl\" size=\"54\" maxlength=\"100\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Active Directory Name</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"text\" name=\"form[customer][1939][activeDirectoryName]\" value=\"sr.msft\" size=\"54\" maxlength=\"255\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Account Manager</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <select type=\"text\" name=\"form[customer][1939][accountManagerUserID]\" onchange=\"setFormChanged();\">\n" +
        //     "\n" +
        //     "                            <option value=\"29\">\n" +
        //     "                                Adrian Cragg\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"141\">\n" +
        //     "                                Alex Lackenby\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"139\">\n" +
        //     "                                Amedeo Fazi\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"105\">\n" +
        //     "                                Andy Hicks\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"125\">\n" +
        //     "                                Anisa Jones\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"84\">\n" +
        //     "                                Ciaran Melia\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"126\">\n" +
        //     "                                Daniel Gilmour\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"83\">\n" +
        //     "                                David Brewer\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"114\">\n" +
        //     "                                David Rochester\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"63\">\n" +
        //     "                                Dawn Peers-Reed\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"144\">\n" +
        //     "                                Dayle Chubb\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"45\">\n" +
        //     "                                Etienne Fournier\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"145\">\n" +
        //     "                                Finlay Jupp\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"2\">\n" +
        //     "                                Gary Jowett\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"85\">\n" +
        //     "                                Gavin Dodd\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"47\">\n" +
        //     "                                Graham Dyer\n" +
        //     "                            </option>\n" +
        //     "                            <option selected=\"\" value=\"3\">\n" +
        //     "                                Graham Lind\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"143\">\n" +
        //     "                                Ian Chown\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"97\">\n" +
        //     "                                Internal Sales\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"98\">\n" +
        //     "                                James Butler\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"132\">\n" +
        //     "                                Jamil Plested\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"140\">\n" +
        //     "                                Jay Luis\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"60\">\n" +
        //     "                                Jonathan Utter\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"123\">\n" +
        //     "                                Julia Parker\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"137\">\n" +
        //     "                                Karl Anderson\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"106\">\n" +
        //     "                                Leo Pullen\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"50\">\n" +
        //     "                                Matt Anderson\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"124\">\n" +
        //     "                                Matt Batchelor\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"134\">\n" +
        //     "                                Matt Nutt\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"90\">\n" +
        //     "                                Matty Harbron\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"57\">\n" +
        //     "                                Michael Wainwright\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"147\">\n" +
        //     "                                Neil Wilson\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"99\">\n" +
        //     "                                Nic Lambert\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"65\">\n" +
        //     "                                Paul Stephenson-Stonley\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"110\">\n" +
        //     "                                Pavwebdev\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"128\">\n" +
        //     "                                Ray Harwood\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"108\">\n" +
        //     "                                Rhys Woods\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"92\">\n" +
        //     "                                Richard Smith\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"138\">\n" +
        //     "                                Rob Harwood\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"136\">\n" +
        //     "                                Roger Fernandes\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"87\">\n" +
        //     "                                Sam Ure\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"131\">\n" +
        //     "                                Sid Gurung\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"67\">\n" +
        //     "                                qSystem\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"146\">\n" +
        //     "                                Tom Bean\n" +
        //     "                            </option>\n" +
        //     "                            <option value=\"133\">\n" +
        //     "                                Web Solutions\n" +
        //     "                            </option>\n" +
        //     "                        </select>\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <script>\n" +
        //     "\n" +
        //     "\n" +
        //     "                    let storedPassPhrase = null;\n" +
        //     "\n" +
        //     "                    const masks = {\n" +
        //     "                        sortCode: '00-00-00',\n" +
        //     "                        accountNumber: '00000000'\n" +
        //     "                    };\n" +
        //     "\n" +
        //     "                    function storePassPhrase(passPhrase) {\n" +
        //     "                        storedPassPhrase = passPhrase;\n" +
        //     "                        setTimeout(() => {\n" +
        //     "                            storedPassPhrase = null;\n" +
        //     "                        }, 30000);\n" +
        //     "                    }\n" +
        //     "\n" +
        //     "\n" +
        //     "                    function editEncrypted(id, element) {\n" +
        //     "\n" +
        //     "                        const el = $(element);\n" +
        //     "                        const parent = el.closest('td');\n" +
        //     "\n" +
        //     "                        let encryptedValue = parent.find('.encrypted').val();\n" +
        //     "                        let promise;\n" +
        //     "\n" +
        //     "                        if (encryptedValue) {\n" +
        //     "                            let passPhrase = storedPassPhrase;\n" +
        //     "                            if (!passPhrase) {\n" +
        //     "                                passPhrase = prompt('Please provide secure passphrase');\n" +
        //     "                                storePassPhrase(passPhrase);\n" +
        //     "                            }\n" +
        //     "                            if (!passPhrase) {\n" +
        //     "                                return;\n" +
        //     "                            }\n" +
        //     "\n" +
        //     "                            const formData = new FormData();\n" +
        //     "\n" +
        //     "                            formData.append('passphrase', passPhrase);\n" +
        //     "                            formData.append('encryptedData', encryptedValue);\n" +
        //     "                            promise = fetch('?action=decrypt', {\n" +
        //     "                                method: 'POST',\n" +
        //     "                                body: formData\n" +
        //     "                            }).then(response => {\n" +
        //     "                                if (response.ok) {\n" +
        //     "                                    return response.json()\n" +
        //     "                                }\n" +
        //     "                                return null;\n" +
        //     "                            }).then(json => {\n" +
        //     "                                if (json) {\n" +
        //     "                                    return json.decryptedData;\n" +
        //     "                                } else {\n" +
        //     "                                    return null;\n" +
        //     "                                }\n" +
        //     "                            })\n" +
        //     "                        } else {\n" +
        //     "                            promise = new Promise(resolve => {\n" +
        //     "                                resolve()\n" +
        //     "                            });\n" +
        //     "                        }\n" +
        //     "\n" +
        //     "                        promise.then(data => {\n" +
        //     "                            parent.append('<input id=\"' + id + '\" name=\"form[customer][1939][new' + capitalize(id) + ']\" value=\"' + data + '\">');\n" +
        //     "                            $('#' + id).mask(masks[id]);\n" +
        //     "                            el.hide();\n" +
        //     "                        })\n" +
        //     "\n" +
        //     "                    }\n" +
        //     "\n" +
        //     "                    function capitalize(string) {\n" +
        //     "                        return string.charAt(0).toUpperCase() + string.slice(1);\n" +
        //     "                    }\n" +
        //     "                </script>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Sort Code</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <button type=\"button\" onclick=\"editEncrypted('sortCode',this)\">\n" +
        //     "                            <i class=\"fa fa-pencil-alt redPencil\" aria-hidden=\"true\">\n" +
        //     "                            </i>\n" +
        //     "                        </button>\n" +
        //     "                        <input type=\"hidden\" name=\"form[customer][1939][sortCode]\" value=\"\" class=\"encrypted\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Account Name</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <input type=\"text\" name=\"form[customer][1939][accountName]\" id=\"accountName\" value=\"\" size=\"18\" maxlength=\"18\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "                <tr>\n" +
        //     "                    <td class=\"content\">Account Number</td>\n" +
        //     "                    <td class=\"content\">\n" +
        //     "                        <button type=\"button\" onclick=\"editEncrypted('accountNumber',this)\">\n" +
        //     "                            <i class=\"fa fa-pencil-alt redPencil\" aria-hidden=\"true\">\n" +
        //     "                            </i>\n" +
        //     "                        </button>\n" +
        //     "                        <input type=\"hidden\" name=\"form[customer][1939][accountNumber]\" value=\"\" class=\"encrypted\">\n" +
        //     "                    </td>\n" +
        //     "                </tr>\n" +
        //     "\n" +
        //     "            </tbody></table>"
        // return this.el(
        //     "div",
        //     {className:'my-account'},
        //     [
        //         this.el('dl',{className:'row',key:'about_me'},[
        //             this.getElement('name','Name',this.state.name),
        //
        //             this.getElement('jobTitle','Job Title',this.state.jobTitle),
        //
        //             this.getElement('startDate','Start Date',this.state.startDate),
        //
        //             this.getElement('lengthOfServices','Length Of Service',this.state.lengthOfServices+" years"),
        //
        //             this.getElement('manager','Manager',this.state.manager),
        //
        //             this.getElement('team','Team',this.state.team) ,
        //             this.el('dt',{key:'userLog',className:'col-3'},'Last login times'),
        //             this.getUserLog(),
        //         ]),
        //         this.el('h1',{key:'section_title_2'},'My Settings'),
        //         // this.el(CheckBox,
        //         //     { key:'sendMeEmail',
        //         //         name:'sendMeEmail',
        //         //         label:"Send me an email when I'm assigned a Service Request.",
        //         //         checked:this.state.sendEmailAssignedService,
        //         //         onChange:this.handleOnChange
        //         //     },null) ,
        //         this.el('button',{key:'btnSave',style:{width:50},onClick:this.handleOnClick},'Save')
        //     ]
        // );
    }
}

export default CustomerEditMain;

const domContainer = document.querySelector('#reactCustomerEditMain');
ReactDOM.render(React.createElement(CustomerEditMain, {customerID: domContainer.dataset.customerId}), domContainer);
