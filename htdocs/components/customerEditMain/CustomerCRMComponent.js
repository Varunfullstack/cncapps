import React, {Fragment} from "react";
import {
    getCustomer,
    getCustomerNotes,
    getEditingNote,
    getEditingSite,
    getLeadStatuses,
    getNewNote,
    getNewNoteModalShow,
    getReviewEngineers
} from "./selectors";
import {
    addNewNote,
    deleteNote,
    goToFirstNote,
    goToLastNote,
    goToNextNote,
    goToPreviousNote,
    hideNewNoteModal,
    newNoteUpdate,
    showNewNoteModal,
    updateCustomerField,
    updateEditingNote,
    updateSiteField
} from "./actions";
import {connect} from "react-redux";
import CKEditor from "ckeditor4-react";
import Select from "./Select";
import AddCustomerNoteComponent from "./modals/AddCustomerNoteComponent";

class CustomerCRMComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    render() {
        const {
            site,
            customer,
            leadStatuses,
            reviewEngineers,
            customerNotes,
            newNote,
            newNoteModalShow,
            editingNote,
            onUpdateSiteField,
            onUpdateCustomerField,
            onGoToFirstNote,
            onGoToPreviousNote,
            onGoToNextNote,
            onGoToLastNote,
            onNewNoteUpdate,
            onNewNoteModalClose,
            onNewNoteAdd,
            onNewNoteModalShow,
            onEditingNoteUpdate,
            onDeleteEditingNote,
        } = this.props;

        CKEditor.editorUrl = '/ckeditor/ckeditor.js'

        const ckeditorConfig = {
            contentsCss: '/screen.css',
            toolbarStartupExpanded: false,
            disableNativeSpellChecker: false,
            toolbar: 'CNCToolbar',
            toolbar_CNCToolbar:
                [
                    ['Source', '-', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'TextColor', 'BGColor'],
                    ['NumberedList', 'BulletedList'],
                    ['Table'],
                    ['Format', 'Font', 'FontSize'],
                    ['Anchor', 'Link'],
                    ['Undo', 'Redo']
                ],
            extraPlugins: 'font,wordcount',
            fontSize_sizes: '8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt',
            wordcount: {
                showParagraphs: false,
                showCharCount: true,
            },
        };


        return (
            <Fragment>
                <AddCustomerNoteComponent note={newNote}
                                          show={newNoteModalShow}
                                          onNoteUpdate={onNewNoteUpdate}
                                          onClose={onNewNoteModalClose}
                                          onAdd={() => onNewNoteAdd(customer.customerID, newNote)}
                />
                <div className="mt-3">
                    <div className="row mb-3">
                        <div className="col-md-12">
                            <h2>Customer Relationship Management</h2>
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-md-3">
                            <div className="card">
                                <div className="card-body p-1">
                                    {
                                        !site ? '' : (
                                            <table className="table table-borderless">
                                                <tbody>
                                                <tr>
                                                    <td>Site Address</td>
                                                    <td>
                                                        <input type="text"
                                                               className="form-control input-sm"
                                                               value={site.address1}
                                                               onChange={$event => {
                                                                   onUpdateSiteField('address1', $event.target.value)
                                                               }}
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td/>
                                                    <td>
                                                        <input type="text"
                                                               className="form-control input-sm"
                                                               value={site.address2}
                                                               onChange={$event => {
                                                                   onUpdateSiteField('address2', $event.target.value)
                                                               }}
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td/>
                                                    <td>
                                                        <input type="text"
                                                               className="form-control input-sm"
                                                               value={site.address3}
                                                               onChange={$event => {
                                                                   onUpdateSiteField('address3', $event.target.value)
                                                               }}
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Town</td>
                                                    <td><input type="text"
                                                               className="form-control input-sm"
                                                               value={site.town}
                                                               onChange={$event => {
                                                                   onUpdateSiteField('town', $event.target.value)
                                                               }}
                                                    />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>County</td>
                                                    <td>
                                                        <input type="text"
                                                               className="form-control input-sm"
                                                               value={site.county}
                                                               onChange={$event => {
                                                                   onUpdateSiteField('county', $event.target.value)
                                                               }}
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Postcode</td>
                                                    <td>
                                                        <input type="text"
                                                               className="form-control input-sm"
                                                               value={site.postcode}
                                                               onChange={$event => {
                                                                   onUpdateSiteField('postcode', $event.target.value)
                                                               }}
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Phone</td>
                                                    <td>
                                                        <input type="text"
                                                               className="form-control input-sm"
                                                               value={site.phone}
                                                               onChange={$event => {
                                                                   onUpdateSiteField('phone', $event.target.value)
                                                               }}
                                                        />
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        )

                                    }

                                </div>
                                <div className="card-footer">
                                    <button className="btn btn-sm btn-new">Add Contact</button>
                                </div>
                            </div>

                        </div>
                        <div className="col-md-3">
                            <div className="card">
                                <div className="card-body p-1">
                                    {
                                        !customer ? '' :
                                            <table className="table table-borderless">
                                                <tbody>
                                                <tr>
                                                    <td>Lead Status</td>
                                                    <td>
                                                        <Select
                                                            name="leadStatus"
                                                            options={leadStatuses}
                                                            selectedOption={customer.leadStatusId}
                                                            onChange={$event => {
                                                                onUpdateCustomerField('leadStatusId', $event)
                                                            }}
                                                            className="form-control input-sm"
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Email Opt Out</td>
                                                    <td>
                                                        <div className="form-inline pt-1">
                                                            <label className="switch">
                                                                <input type="checkbox"
                                                                       checked={customer.mailshotFlag}
                                                                       onChange={$event => {
                                                                           onUpdateCustomerField('mailshotFlag', $event.target.checked)
                                                                       }}
                                                                       className="tick_field"
                                                                />

                                                                <span className="slider round"/>
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Meeting Confirmed</td>
                                                    <td>
                                                        <input type="date"
                                                               value={customer.dateMeetingConfirmed}
                                                               onChange={$event => {
                                                                   onUpdateCustomerField('dateMeetingConfirmed', $event.target.value)
                                                               }}
                                                               className="form-control input-sm"
                                                        />
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                    }

                                </div>
                            </div>

                        </div>
                        <div className="col-md-6">
                            <div className="card">
                                <div className="card-body p-1">
                                    <CKEditor data={customer.opportunityDeal}
                                              config={ckeditorConfig}
                                              onChange={$event => onUpdateCustomerField('opportunityDeal', $event.editor.getData())}
                                    />
                                </div>

                            </div>

                        </div>
                    </div>
                    <div className="row mt-5">
                        <div className="col-md-12">
                            <table className="table table-hover">
                                <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Position</th>
                                    <th>Phone</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Support Level</th>
                                    <th>Inv</th>
                                    <th>STM</th>
                                    <th>HR</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr data-toggle="collapse"
                                    data-target="#accordion{contact1234}"
                                    className="clickable"
                                >
                                    {/*<td>{title} {firstName} {lastName}</td>*/}
                                    {/*<td>{position}</td>*/}
                                    {/*<td>{phone}</td>*/}
                                    {/*<td>{mobilePhone}</td>*/}
                                    {/*<td>{email}</td>*/}
                                    {/*<td>{supportLevel}</td>*/}
                                    {/*<td>{inv}</td>*/}
                                    {/*<td>{stm}</td>*/}
                                    {/*<td>{hr}</td>*/}
                                </tr>
                                <tr>
                                    <td colSpan="10">
                                        {/*<div id="accordion{contact1234}"*/}
                                        {/*     className="collapse p-1"*/}
                                        {/*>*/}
                                        {/*    <div className="row">*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="site">Site <span>*</span></label>*/}
                                        {/*                /!*<select id="site"*!/*/}
                                        {/*                /!*        name="form[contact][{contactID}][siteNo]"*!/*/}
                                        {/*                /!*        data-validation="required"*!/*/}
                                        {/*                /!*        className="form-control input-sm"*!/*/}
                                        {/*                /!*>*!/*/}

                                        {/*                /!*    <option*!/*/}
                                        {/*                /!*        value="{selectSiteNo}"*!/*/}
                                        {/*                /!*    >*!/*/}
                                        {/*                /!*    </option>*!/*/}

                                        {/*                /!*</select>*!/*/}
                                        {/*            </div>*/}

                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">Title <span>*</span></label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][title]"*/}
                                        {/*                    size="2"*/}
                                        {/*                    maxLength="10"*/}
                                        {/*                    value="{title}"*/}
                                        {/*                    required*/}
                                        {/*                    data-validation="required"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">*/}
                                        {/*                    First Name <span>*</span>*/}
                                        {/*                </label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][firstName]"*/}
                                        {/*                    size="10"*/}
                                        {/*                    maxLength="50"*/}
                                        {/*                    value="{firstName}"*/}
                                        {/*                    required*/}
                                        {/*                    data-validation="required"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">*/}
                                        {/*                    Last Name*/}
                                        {/*                    <span>*</span>*/}
                                        {/*                </label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][lastName]"*/}
                                        {/*                    size="10"*/}
                                        {/*                    maxLength="50"*/}
                                        {/*                    value="{lastName}"*/}
                                        {/*                    required*/}
                                        {/*                    data-validation="required"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">Email <span>*</span></label>*/}
                                        {/*                <input*/}
                                        {/*                    type="email"*/}
                                        {/*                    name="form[contact][{contactID}][email]"*/}
                                        {/*                    value="{email}"*/}
                                        {/*                    size="25"*/}
                                        {/*                    maxLength="50"*/}
                                        {/*                    data-validation="emailOrEmpty server"*/}
                                        {/*                    data-validation-url="/validateUniqueEmail.php"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">Phone*/}
                                        {/*                    <span>*</span></label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][phone]"*/}
                                        {/*                    value="{phone}"*/}
                                        {/*                    size="10"*/}
                                        {/*                    maxLength="30"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">Mobile <span>*</span></label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][mobilePhone]"*/}
                                        {/*                    value="{mobilePhone}"*/}
                                        {/*                    size="10"*/}
                                        {/*                    maxLength="30"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">Position*/}
                                        {/*                    <span>*</span></label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][position]"*/}
                                        {/*                    id="form[contact][{contactID}][position]"*/}
                                        {/*                    value="{position}"*/}
                                        {/*                    size="10"*/}
                                        {/*                    maxLength="20"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">Support Level*/}
                                        {/*                    <span>*</span></label>*/}
                                        {/*                <select name="form[contact][{contactID}][supportLevel]"*/}
                                        {/*                        data-validation="atLeastOneMain"*/}
                                        {/*                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                >*/}

                                        {/*                    <option*/}
                                        {/*                        data-test="{supportLevelSelected}"*/}
                                        {/*                        value="{supportLevelValue}"*/}
                                        {/*                    >*/}

                                        {/*                    </option>*/}

                                        {/*                </select>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-4">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">Notes<span/></label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][notes]"*/}
                                        {/*                    size="5"*/}
                                        {/*                    maxLength="200"*/}
                                        {/*                    value="{notes}"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}

                                        {/*            <label htmlFor="password">Password</label>*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <button id="password"*/}
                                        {/*                        type="button"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                        onClick="editEncrypted('sortCode',this)"*/}
                                        {/*                >*/}
                                        {/*                    <i className="fal fa-lock {sortCodePencilColor}">*/}
                                        {/*                    </i>*/}
                                        {/*                </button>*/}
                                        {/*                <input type="hidden"*/}
                                        {/*                       data-contact-id="{contactID}"*/}
                                        {/*                       name="form[customer][{customerID}][sortCode]"*/}
                                        {/*                       className="encrypted input-sm form-control {portalPasswordButtonClass} btn btn-outline-secondary btn-block fullwidth"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}

                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <div className="form-group">*/}
                                        {/*                <label htmlFor="">*/}
                                        {/*                    Failed Login<span/>*/}
                                        {/*                </label>*/}
                                        {/*                <input*/}
                                        {/*                    name="form[contact][{contactID}][failedLoginCount]"*/}
                                        {/*                    value="{failedLoginCount}"*/}
                                        {/*                    size="2"*/}
                                        {/*                    maxLength="5"*/}
                                        {/*                    className="form-control input-sm"*/}
                                        {/*                />*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}

                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="initialLogging">Initial Logging</label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input id="initialLogging"*/}
                                        {/*                           type="checkbox"*/}
                                        {/*                           name="form[contact][{contactID}][initialLoggingEmailFlag]"*/}
                                        {/*                           value="Y"*/}
                                        {/*                           className="tick_field"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}

                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="fixed">Fixed</label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        className="tick_field"*/}
                                        {/*                        id="fixed"*/}
                                        {/*                        type="checkbox"*/}

                                        {/*                        name="form[site][{customerID}{siteNo}][nonUKFlag]"*/}
                                        {/*                        title="Check to show this site is overseas and not in the UK"*/}
                                        {/*                        value="Y"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="otherInitial">Other Initial</label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        className="tick_field"*/}
                                        {/*                        id="otherInitial"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][{contactID}][accountsFlag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        data-validation="atLeastOne"*/}
                                        {/*                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="accounts">Accounts</label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="accounts"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][accountsFlag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        data-validation="atLeastOne"*/}
                                        {/*                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                                        {/*                        className="tick_field"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="inv">Inv</label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="inv"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][mailshot2Flag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        data-validation="atLeastOne"*/}
                                        {/*                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                                        {/*                        className="tick_field"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="news">News</label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="news"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][mailshot3Flag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="stm">Stm<span/></label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="stm"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][mailshot4Flag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        className="stmCheckBox form-control"*/}
                                        {/*                        data-validation="atLeastOneAtMostOne"*/}
                                        {/*                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="hr">HR<span/></label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="hr"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][hrUser]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="review">Review<span/></label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="review"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][reviewUser]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        data-validation="atLeastOne"*/}
                                        {/*                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="top">Top<span/></label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="top"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][mailshot8Flag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="sr-rep">SR Rep<span/></label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="sr-rep"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][mailshot11Flag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="mailshot">Mailshot<span/></label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="mailshot"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][sendMailshotFlag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="pendingLeaver">Pending Leaver</label>*/}
                                        {/*            <div className="form-group form-inline">*/}
                                        {/*                <label className="switch">*/}
                                        {/*                    <input*/}
                                        {/*                        id="pendingLeaver"*/}
                                        {/*                        type="checkbox"*/}
                                        {/*                        name="form[contact][0][pendingLeaverFlag]"*/}
                                        {/*                        value="Y"*/}
                                        {/*                        className="form-control input-sm"*/}
                                        {/*                    />*/}
                                        {/*                    <span className="slider round"/>*/}
                                        {/*                </label>*/}
                                        {/*            </div>*/}
                                        {/*        </div>*/}

                                        {/*        <div className="col-lg-2">*/}
                                        {/*            <label htmlFor="pendingLeaverDate">Pending Leaver*/}
                                        {/*                Date</label>*/}
                                        {/*            <label className="tick_container d-block">*/}
                                        {/*                <input*/}
                                        {/*                    id="pendingLeaverDate"*/}
                                        {/*                    type="text"*/}
                                        {/*                    name="form[contact][0][pendingLeaverDate]"*/}
                                        {/*                    value="{pendingLeaverDate}"*/}
                                        {/*                    className="jQueryCalendar form-control"*/}
                                        {/*                    size="15"*/}
                                        {/*                    maxLength="5"*/}
                                        {/*                    autoComplete="off"*/}
                                        {/*                />*/}
                                        {/*            </label>*/}
                                        {/*        </div>*/}


                                        {/*    </div>*/}
                                        {/*    <div className="row">*/}
                                        {/*        <div className="col-lg-6">*/}
                                        {/*            <button className="btn btn-new btn-sm">*/}
                                        {/*                Save Details*/}
                                        {/*            </button>*/}
                                        {/*            <button className="btn btn-outline-primary btn-sm">*/}
                                        {/*                Process as Leaver*/}
                                        {/*            </button>*/}
                                        {/*        </div>*/}
                                        {/*        <div className="col-lg-6">*/}
                                        {/*            <button className="btn btn-outline-secondary float-right btn-sm">*/}
                                        {/*                Cancel*/}
                                        {/*            </button>*/}
                                        {/*            <button className="btn btn-danger float-right btn-sm">*/}
                                        {/*                Delete*/}
                                        {/*            </button>*/}
                                        {/*        </div>*/}
                                        {/*    </div>*/}
                                        {/*</div>*/}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-6">
                            <h4>Notes</h4>
                            <div className="row">
                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label htmlFor="reviewDate">To be reviewed on:</label>
                                        Date:
                                        <input
                                            type="date"
                                            name="reviewDate"
                                            value={customer.reviewDate}
                                            onChange={$event => {
                                                onUpdateCustomerField('reviewDate', $event.target.value)
                                            }}
                                            className="form-control input-sm"
                                        />
                                        Time:
                                        <input
                                            type="time"
                                            name="reviewTime"
                                            value={customer.reviewTime}
                                            onChange={$event => {
                                                onUpdateCustomerField('reviewTime', $event.target.value)
                                            }}
                                            className="form-control input-sm"
                                        />
                                    </div>
                                </div>
                                <div className="col-md-4">
                                    <div className="form-group">

                                        <label>By:</label>
                                        <Select
                                            name="reviewUserID"
                                            onChange={$event => {
                                                onUpdateCustomerField('reviewUserID', $event);
                                            }}
                                            className="form-control input-sm"
                                            options={reviewEngineers}
                                            selectedOption={customer.reviewUserID}
                                        />
                                    </div>

                                </div>
                            </div>


                            <div className="form-group customerReviewAction">
                                        <textarea
                                            title="Action to be taken"
                                            cols="120"
                                            rows="3"
                                            name="form[customer][{customerID}][reviewAction]"
                                            className="form-control input-sm"
                                            value={customer.reviewAction}
                                            onChange={$event => {
                                                onUpdateCustomerField('reviewAction', $event.target.value)
                                            }}
                                        />
                            </div>
                            <div className="form-group customerNoteHistory">
                                <div style={{
                                    display: 'flex',
                                    flexDirection: 'column',
                                    height: "400px",
                                    overflowY: 'scroll'
                                }}
                                >
                                    {
                                        customerNotes.map(x => {
                                            return (
                                                <div key={`rowsHolder-${x.id}`}>
                                                    <div style={{backgroundColor: '#cccccc'}}>
                                                        <span>
                                                            {`${x.modifiedAt} - ${x.modifiedByName}`}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        {x.note}
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }
                                </div>

                                <div className="customerNoteNav mt-3 mb-3">
                                    <button
                                        type="button"
                                        name="First"
                                        aria-hidden="true"
                                        onClick={() => {
                                            onGoToFirstNote()
                                        }}
                                        className="btn secondary"
                                    >
                                        <i className="fal fa-step-backward fa-lg">
                                        </i>
                                    </button>

                                    <button
                                        type="button"
                                        name="Previous"
                                        onClick={() => {
                                            onGoToPreviousNote()
                                        }}
                                        className="btn secondary"
                                    >
                                        <i className="fal fa-backward fa-lg"
                                           aria-hidden="true"
                                        >
                                        </i>
                                    </button>
                                    <button
                                        type="button"
                                        name="Next"
                                        onClick={() => {
                                            onGoToNextNote()
                                        }}
                                        className="btn secondary"
                                    >
                                        <i className="fal fa-forward fa-lg"
                                           aria-hidden="true"
                                        >
                                        </i>
                                    </button>

                                    <button
                                        type="button"
                                        name="Last"
                                        onClick={() => {
                                            onGoToLastNote()
                                        }}
                                        className="btn secondary"
                                    >
                                        <i className="fal fa-step-forward fa-lg"
                                           aria-hidden="true"
                                        >
                                        </i>
                                    </button>
                                    <button
                                        type="button"
                                        name="Delete"
                                        onClick={() => {
                                            if (confirm('Are you sure you want to delete this note?')) {
                                                onDeleteEditingNote()
                                            }
                                        }}
                                        className="btn secondary"
                                    >
                                        <i className="fal fa-trash-alt fa-lg"
                                           aria-hidden="true"
                                        >
                                        </i>
                                    </button>
                                    <button
                                        type="button"
                                        name="New"
                                        onClick={() => onNewNoteModalShow()}
                                        className="btn secondary"
                                    >
                                        <i className="fal fa-plus fa-lg"
                                           aria-hidden="true"
                                        >
                                        </i>
                                    </button>
                                </div>
                            </div>

                        </div>
                        <div className="col-md-6">
                            <div className="form-group customerNoteDetails">
                                <textarea
                                    name="customerNoteDetails"
                                    cols="120"
                                    onChange={($event) => onEditingNoteUpdate($event.target.value)}
                                    rows="12"
                                    value={editingNote?.note || ''}
                                    className="form-control input-sm"
                                >
                                </textarea>
                            </div>
                            <div>
                                Last Contract Sent: {customer.lastContractSent}
                            </div>
                        </div>
                    </div>
                </div>

            </Fragment>
        );
    }
}


function mapStateToProps(state) {
    return {
        site: getEditingSite(state),
        customer: getCustomer(state),
        leadStatuses: getLeadStatuses(state),
        reviewEngineers: getReviewEngineers(state),
        customerNotes: getCustomerNotes(state),
        editingNote: getEditingNote(state),
        newNote: getNewNote(state),
        newNoteModalShow: getNewNoteModalShow(state)
    }
}


function mapDispatchToProps(dispatch) {
    return {
        onUpdateSiteField: (field, value) => {
            dispatch(updateSiteField(field, value))
        },
        onUpdateCustomerField: (field, value) => {
            dispatch(updateCustomerField(field, value))
        },
        onGoToFirstNote: () => {
            dispatch(goToFirstNote())
        },
        onGoToPreviousNote: () => {
            dispatch(goToPreviousNote())
        },
        onGoToNextNote: () => {
            dispatch(goToNextNote())
        },
        onGoToLastNote: () => {
            dispatch(goToLastNote())
        },
        onNewNoteUpdate: (value) => {
            dispatch(newNoteUpdate(value))
        },
        onNewNoteModalShow: () => {
            dispatch(showNewNoteModal())
        },
        onNewNoteModalClose: () => {
            dispatch(hideNewNoteModal())
        },
        onNewNoteAdd: (customerId, note) => {
            dispatch(addNewNote(customerId, note))
        },
        onEditingNoteUpdate: (note) => {
            dispatch(updateEditingNote(note));
        },
        onDeleteEditingNote: () => {
            dispatch(deleteNote())
        }
    }
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CustomerCRMComponent)
