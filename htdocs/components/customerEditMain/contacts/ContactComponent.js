import React from 'react';
import * as PropTypes from "prop-types";

export class ContactComponent extends React.Component {


    render() {
        return <div className="row"

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
                        <td>{this.props.contact.title} {this.props.contact.firstName} {this.props.contact.lastName}</td>
                        <td>{this.props.contact.position}</td>
                        <td>{this.props.contact.phone}</td>
                        <td>{this.props.contact.mobilePhone}</td>
                        <td>{this.props.contact.email}</td>
                        <td>{this.props.contact.supportLevel}</td>
                        <td>{this.props.contact.inv}</td>
                        <td>{this.props.contact.stm}</td>
                        <td>{this.props.contact.hr}</td>
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
                                                    name="siteNo"
                                                    required
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
                                                name="title"
                                                size="2"
                                                maxLength="10"
                                                value={this.props.contact.title}
                                                onChange={this.props.onChange}
                                                required
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
                                            <label htmlFor="">Notes<span/></label>
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
                                                Login<span/></label>
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
                                                <span className="slider round"/>
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
                                                <span className="slider round"/>
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
                                                <span className="slider round"/>
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
                                                <span className="slider round"/>
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
                                                <span className="slider round"/>
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
                                                <span className="slider round"/>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-lg-2">
                                        <label htmlFor="hr">HR<span/></label>
                                        <div className="form-group form-inline">
                                            <label className="switch">
                                                <input id="hr"
                                                       type="checkbox"
                                                       name="form[contact][0][hrUser]"
                                                       value="Y"
                                                       className="form-control input-sm"
                                                />
                                                <span className="slider round"/>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-lg-2">
                                        <label htmlFor="review">Review<span/></label>
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
                                                <span className="slider round"/>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-lg-2">
                                        <label htmlFor="top">Top<span/></label>
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
                                                <span className="slider round"/>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-lg-2">
                                        <label htmlFor="sr-rep">SR Rep<span/></label>
                                        <div className="form-group form-inline">
                                            <label className="switch">
                                                <input id="sr-rep"
                                                       type="checkbox"
                                                       name="form[contact][0][mailshot11Flag]"
                                                       value="Y"
                                                       className="form-control input-sm"
                                                />
                                                <span className="slider round"/>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-lg-2">
                                        <label htmlFor="mailshot">Mailshot<span/></label>
                                        <div className="form-group form-inline">
                                            <label className="switch">
                                                <input id="mailshot"
                                                       type="checkbox"
                                                       name="form[contact][0][sendMailshotFlag]"
                                                       value="Y"
                                                       className="form-control input-sm"
                                                />
                                                <span className="slider round"/>
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
                                                <span className="slider round"/>
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
        </div>;
    }
}

ContactComponent.propTypes = {
    contact: PropTypes.any,
    onChange: PropTypes.any
};
