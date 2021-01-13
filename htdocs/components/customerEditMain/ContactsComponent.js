import {getAllContacts, getMappedContacts} from "./selectors";
import {updateCustomerField} from "./actions";
import {connect} from "react-redux";

import React from 'react';

class ContactsComponent extends React.PureComponent {

    renderContacts() {
        const {contacts} = this.props;
        return contacts.map(contact => (
            <div className="row"
                 key={contact.contactID}
            >
                <div className="col-md-12">
                    <table className="table table-hover">
                        <thead key="head">
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
                        <tbody key="body">
                        <tr data-toggle="collapse"
                            data-target="#accordion{contact1234}"
                            className="clickable"
                        >
                            <td>{contact.title} {contact.firstName} {contact.lastName}</td>
                            <td>{contact.position}</td>
                            <td>{contact.phone}</td>
                            <td>{contact.mobilePhone}</td>
                            <td>{contact.email}</td>
                            <td>{contact.supportLevel}</td>
                            <td>{contact.inv}</td>
                            <td>{contact.stm}</td>
                            <td>{contact.hr}</td>
                        </tr>
                        <tr>
                            <td colSpan="10">
                                <div id="accordion{contact1234}"
                                     className="collapse p-1"
                                >
                                    <div className="row">
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="site">Site <span>*</span></label>
                                                <select id="site"
                                                        name="form[contact][{contactID}][siteNo]"
                                                        data-validation="required"
                                                        data-type="site"
                                                        className="form-control input-sm"
                                                >

                                                    {/*<option {siteSelected}*/}
                                                    {/*        value="{selectSiteNo}"*/}
                                                    {/*>{selectSiteDesc}*/}
                                                    {/*</option>*/}

                                                </select>
                                            </div>

                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Title <span>*</span></label>
                                                <input
                                                    name="form[contact][{contactID}][title]"
                                                    size="2"
                                                    maxLength="10"
                                                    value="{title}"
                                                    required
                                                    data-validation="required"
                                                    data-type="title"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">First Name
                                                    <span>*</span></label>
                                                <input

                                                    name="form[contact][{contactID}][firstName]"
                                                    size="10"
                                                    maxLength="50"
                                                    value="{firstName}"
                                                    required
                                                    data-validation="required"
                                                    data-type="firstName"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Last Name
                                                    <span>*</span></label>
                                                <input

                                                    name="form[contact][{contactID}][lastName]"
                                                    size="10"
                                                    maxLength="50"
                                                    value="{lastName}"
                                                    required
                                                    data-validation="required"
                                                    data-type="lastName"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Email <span>*</span></label>
                                                <input

                                                    type="email"
                                                    name="form[contact][{contactID}][email]"
                                                    value="{email}"
                                                    size="25"
                                                    maxLength="50"
                                                    data-validation="emailOrEmpty server"
                                                    data-validation-url="/validateUniqueEmail.php"
                                                    data-type="email"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Phone
                                                    <span>*</span></label>
                                                <input

                                                    name="form[contact][{contactID}][phone]"
                                                    value="{phone}"
                                                    size="10"
                                                    maxLength="30"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Mobile <span>*</span></label>
                                                <input

                                                    name="form[contact][{contactID}][mobilePhone]"
                                                    value="{mobilePhone}"
                                                    size="10"
                                                    maxLength="30"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Position
                                                    <span>*</span></label>
                                                <input name="form[contact][{contactID}][position]"
                                                       id="form[contact][{contactID}][position]"
                                                       value="{position}"
                                                       size="10"
                                                       maxLength="20"


                                                       className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Support Level
                                                    <span>*</span></label>
                                                <select name="form[contact][{contactID}][supportLevel]"
                                                        data-validation="atLeastOneMain"
                                                        data-type="mainSelector"
                                                        data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                                        className="form-control input-sm"
                                                >
                                                    {/*<option {supportLevelSelected}*/}
                                                    {/*        data-test="{supportLevelSelected}"*/}
                                                    {/*        value="{supportLevelValue}"*/}
                                                    {/*>*/}
                                                    {/*    {supportLevelDescription}*/}
                                                    {/*</option>*/}
                                                </select>
                                            </div>
                                        </div>
                                        <div className="col-lg-4">
                                            <div className="form-group">
                                                <label htmlFor="">Notes<span></span></label>
                                                <input

                                                    name="form[contact][{contactID}][notes]"
                                                    size="5"
                                                    maxLength="200"
                                                    value="{notes}"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                        <div className="col-lg-2">

                                            <label htmlFor="password">Password</label>
                                            <div className="form-group">
                                                <button id="password"
                                                        type="button"
                                                        className="form-control input-sm"
                                                        onClick="editEncrypted('sortCode',this)"
                                                >
                                                    <i className="fal fa-lock {sortCodePencilColor}">
                                                    </i>
                                                </button>
                                                <input type="hidden"
                                                       data-contact-id="{contactID}"
                                                       name="form[customer][{customerID}][sortCode]"
                                                       className="encrypted input-sm form-control {portalPasswordButtonClass} btn btn-outline-secondary btn-block fullwidth"
                                                />
                                            </div>

                                        </div>
                                        <div className="col-lg-2">
                                            <div className="form-group">
                                                <label htmlFor="">Failed
                                                    Login<span></span></label>
                                                <input

                                                    name="form[contact][{contactID}][failedLoginCount]"
                                                    value="{failedLoginCount}"
                                                    size="2"
                                                    maxLength="5"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>

                                        <div className="col-lg-2">
                                            <label htmlFor="initialLogging">Initial Logging</label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="initialLogging"
                                                           type="checkbox"
                                                           name="form[contact][{contactID}][initialLoggingEmailFlag]"
                                                           value="Y"
                                                           className="tick_field"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div className="col-lg-2">
                                            <label htmlFor="fixed">Fixed</label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input className="tick_field"
                                                           id="fixed"
                                                           type="checkbox"
                                                           name="form[site][{customerID}{siteNo}][nonUKFlag]"
                                                           title="Check to show this site is overseas and not in the UK"
                                                           value="Y"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="otherInitial">Other Initial</label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input className="tick_field"
                                                           id="otherInitial"
                                                           type="checkbox"
                                                           name="form[contact][{contactID}][accountsFlag]"
                                                           value="Y"
                                                           data-validation="atLeastOne"
                                                           data-type="accounts"
                                                           data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="accounts">Accounts</label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="accounts"
                                                           type="checkbox"
                                                           name="form[contact][0][accountsFlag]"
                                                           value="Y"
                                                           data-validation="atLeastOne"
                                                           data-type="accounts"
                                                           data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                                           className="tick_field"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="inv">Inv</label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="inv"
                                                           type="checkbox"
                                                           name="form[contact][0][mailshot2Flag]"
                                                           value="Y"
                                                           data-validation="atLeastOne"
                                                           data-type="invoice"
                                                           data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                                           className="tick_field"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="news">News</label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="news"
                                                           type="checkbox"
                                                           name="form[contact][0][mailshot3Flag]"
                                                           value="Y"
                                                           className="form-control input-sm"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="stm">Stm<span></span></label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="stm"
                                                           type="checkbox"
                                                           name="form[contact][0][mailshot4Flag]"
                                                           value="Y"
                                                           className="stmCheckBox form-control"
                                                           data-validation="atLeastOneAtMostOne"
                                                           data-type="statement"
                                                           data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="hr">HR<span></span></label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="hr"
                                                           type="checkbox"
                                                           name="form[contact][0][hrUser]"
                                                           value="Y"
                                                           className="form-control input-sm"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="review">Review<span></span></label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="review"
                                                           type="checkbox"
                                                           name="form[contact][0][reviewUser]"
                                                           value="Y"
                                                           data-validation="atLeastOne"
                                                           data-type="review"
                                                           data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                                           className="form-control input-sm"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="top">Top<span></span></label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="top"
                                                           type="checkbox"
                                                           name="form[contact][0][mailshot8Flag]"
                                                           value="Y"
                                                           data-type="topUp"
                                                           data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                                           className="form-control input-sm"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="sr-rep">SR Rep<span></span></label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="sr-rep"
                                                           type="checkbox"
                                                           name="form[contact][0][mailshot11Flag]"
                                                           value="Y"
                                                           className="form-control input-sm"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="mailshot">Mailshot<span></span></label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="mailshot"
                                                           type="checkbox"
                                                           name="form[contact][0][sendMailshotFlag]"
                                                           value="Y"
                                                           className="form-control input-sm"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div className="col-lg-2">
                                            <label htmlFor="pendingLeaver">Pending Leaver</label>
                                            <div className="form-group form-inline">
                                                <label className="switch">
                                                    <input id="pendingLeaver"
                                                           type="checkbox"
                                                           name="form[contact][0][pendingLeaverFlag]"
                                                           value="Y"
                                                           className="form-control input-sm"
                                                    />
                                                    <span className="slider round"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div className="col-lg-2">
                                            <label htmlFor="pendingLeaverDate">Pending Leaver
                                                Date</label>
                                            <label className="tick_container d-block">
                                                <input id="pendingLeaverDate"
                                                       type="date"
                                                       name="pendingLeaverDate"
                                                       value="{pendingLeaverDate}"
                                                       className="jQueryCalendar form-control"
                                                       size="15"
                                                       maxLength="5"
                                                       autoComplete="off"
                                                />
                                            </label>
                                        </div>


                                    </div>
                                    <div className="row">
                                        <div className="col-lg-6">
                                            <button className="btn btn-new btn-sm">
                                                Save Details
                                            </button>
                                            <button className="btn btn-outline-primary btn-sm">
                                                Process as Leaver
                                            </button>
                                        </div>
                                        <div className="col-lg-6">
                                            <button className="btn btn-outline-secondary float-right btn-sm">
                                                Cancel
                                            </button>
                                            <button className="btn btn-outline-danger float-right btn-sm">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        ));
    }

    render() {
        return (
            <div className="mt-3">
                <div className="row">
                    <div className="col-md-12">
                        <h2>Contacts</h2>
                    </div>
                    <div className="col-md-12">
                        <button className="btn btn-sm btn-new mt-3 mb-3">Add Contact</button>
                    </div>
                </div>
                {this.renderContacts()}
            </div>
        )
    }
}

function mapStateToProps(state) {
    return {
        contacts: getAllContacts(state)
    }
}

function mapDispatchToProps(dispatch) {
    return {
        customerValueUpdate: (field, value) => {
            dispatch(updateCustomerField(field, value))
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ContactsComponent)