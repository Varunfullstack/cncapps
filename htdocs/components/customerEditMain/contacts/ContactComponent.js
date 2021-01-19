import React from 'react';
import * as PropTypes from "prop-types";

export class ContactComponent extends React.Component {


    constructor(props, context) {
        super(props, context);
        this.updateContact = this.updateContact.bind(this);
        this.state = {
            originalContact: {...this.props.contact},
            updatedContact: {...this.props.contact}
        }
    }

    updateContact($event) {
        const updatedContact = {
            ...this.state.updatedContact,
            [$event.target.name]: $event.target.value,
        }
        this.setState({updatedContact});
    }

    render() {
        const {sites, supportLevelOptions} = this.props;
        const {updatedContact} = this.state;
        return <React.Fragment>
            <tr data-toggle="collapse"
                data-target={`#accordion{contact${updatedContact.id}}`}
                className="clickable"
            >
                <td>{updatedContact.title} {updatedContact.firstName} {updatedContact.lastName}</td>
                <td>{updatedContact.position}</td>
                <td>{updatedContact.phone}</td>
                <td>{updatedContact.mobilePhone}</td>
                <td>{updatedContact.email}</td>
                <td>{updatedContact.supportLevel}</td>
                <td>{updatedContact.inv}</td>
                <td>{updatedContact.hr}</td>
            </tr>
            <tr>
                <td colSpan="10">
                    <div id={`accordion{contact${updatedContact.id}}`}
                         className="collapse p-1"
                    >
                        <div className="row">
                            <div className="col-lg-4">
                                <div className="form-group">
                                    <label htmlFor="site">Site <span>*</span></label>
                                    <select id="site"
                                            name="siteNo"
                                            required
                                            className="form-control input-sm"
                                            onChange={this.updateContact}
                                            value={updatedContact.siteNo || ""}
                                    >

                                        {
                                            sites.map(site => <option key={site}
                                                                      value={site.siteNo}
                                            >
                                                {`${site.town}`}
                                            </option>)
                                        }
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
                                        value={updatedContact.title || ""}
                                        onChange={this.updateContact}
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
                                        name="firstName"
                                        size="10"
                                        maxLength="50"
                                        value={updatedContact.firstName || ""}
                                        onChange={this.updateContact}
                                        required
                                        className="form-control input-sm"
                                    />
                                </div>
                            </div>
                            <div className="col-lg-4">
                                <div className="form-group">
                                    <label htmlFor="">Last Name
                                        <span>*</span></label>
                                    <input
                                        name="lastName"
                                        size="10"
                                        maxLength="50"
                                        value={updatedContact.lastName || ""}
                                        onChange={this.updateContact}
                                        required
                                        className="form-control input-sm"
                                    />
                                </div>
                            </div>
                            <div className="col-lg-4">
                                <div className="form-group">
                                    <label htmlFor="">Email <span>*</span></label>
                                    <input
                                        type="email"
                                        name="email"
                                        value={updatedContact.email || ""}
                                        onChange={this.updateContact}
                                        size="25"
                                        maxLength="50"
                                        className="form-control input-sm"
                                    />
                                </div>
                            </div>
                            <div className="col-lg-4">
                                <div className="form-group">
                                    <label htmlFor="">Phone
                                        <span>*</span></label>
                                    <input
                                        name="phone"
                                        value={updatedContact.phone || ""}
                                        onChange={this.updateContact}
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
                                        name="mobilePhone"
                                        value={updatedContact.mobilePhone || ""}
                                        onChange={this.updateContact}
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
                                    <input
                                        name="position"
                                        value={updatedContact.position || ""}
                                        onChange={this.updateContact}
                                        size="10"
                                        maxLength="20"
                                        required
                                        className="form-control input-sm"
                                    />
                                </div>
                            </div>
                            <div className="col-lg-4">
                                <div className="form-group">
                                    <label htmlFor="">Support Level
                                        <span>*</span></label>
                                    <select name="supportLevel"
                                            value={updatedContact.supportLevel}
                                            onChange={this.updateContact}
                                            data-validation="atLeastOneMain"
                                            data-type="mainSelector"
                                            data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"
                                            className="form-control input-sm"
                                    >
                                        {
                                            supportLevelOptions.map(supportLevelOption =>
                                                <option
                                                    value={supportLevelOption.value}
                                                >
                                                    {supportLevelOption.description}
                                                </option>
                                            )}
                                    </select>
                                </div>
                            </div>
                            {/*<div className="col-lg-4">*/}
                            {/*    <div className="form-group">*/}
                            {/*        <label htmlFor="">Notes<span/></label>*/}
                            {/*        <input*/}

                            {/*            name="form[contact][{contactID}][notes]"*/}
                            {/*            size="5"*/}
                            {/*            maxLength="200"*/}
                            {/*            value="{notes}"*/}
                            {/*            className="form-control input-sm"*/}
                            {/*        />*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}

                            {/*    <label htmlFor="password">Password</label>*/}
                            {/*    <div className="form-group">*/}
                            {/*        <button id="password"*/}
                            {/*                type="button"*/}
                            {/*                className="form-control input-sm"*/}
                            {/*                onClick="editEncrypted('sortCode',this)"*/}
                            {/*        >*/}
                            {/*            <i className="fal fa-lock {sortCodePencilColor}">*/}
                            {/*            </i>*/}
                            {/*        </button>*/}
                            {/*        <input type="hidden"*/}
                            {/*               data-contact-id="{contactID}"*/}
                            {/*               name="form[customer][{customerID}][sortCode]"*/}
                            {/*               className="encrypted input-sm form-control {portalPasswordButtonClass} btn btn-outline-secondary btn-block fullwidth"*/}
                            {/*        />*/}
                            {/*    </div>*/}

                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <div className="form-group">*/}
                            {/*        <label htmlFor="">Failed*/}
                            {/*            Login<span/></label>*/}
                            {/*        <input*/}

                            {/*            name="form[contact][{contactID}][failedLoginCount]"*/}
                            {/*            value="{failedLoginCount}"*/}
                            {/*            size="2"*/}
                            {/*            maxLength="5"*/}
                            {/*            className="form-control input-sm"*/}
                            {/*        />*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="initialLogging">Initial Logging</label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="initialLogging"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][{contactID}][initialLoggingEmailFlag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   className="tick_field"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="fixed">Fixed</label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input className="tick_field"*/}
                            {/*                   id="fixed"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[site][{customerID}{siteNo}][nonUKFlag]"*/}
                            {/*                   title="Check to show this site is overseas and not in the UK"*/}
                            {/*                   value="Y"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="otherInitial">Other Initial</label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input className="tick_field"*/}
                            {/*                   id="otherInitial"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][{contactID}][accountsFlag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   data-validation="atLeastOne"*/}
                            {/*                   data-type="accounts"*/}
                            {/*                   data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="accounts">Accounts</label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="accounts"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][accountsFlag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   data-validation="atLeastOne"*/}
                            {/*                   data-type="accounts"*/}
                            {/*                   data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                            {/*                   className="tick_field"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="inv">Inv</label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="inv"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][mailshot2Flag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   data-validation="atLeastOne"*/}
                            {/*                   data-type="invoice"*/}
                            {/*                   data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                            {/*                   className="tick_field"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="news">News</label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="news"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][mailshot3Flag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   className="form-control input-sm"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="hr">HR<span/></label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="hr"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][hrUser]"*/}
                            {/*                   value="Y"*/}
                            {/*                   className="form-control input-sm"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="review">Review<span/></label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="review"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][reviewUser]"*/}
                            {/*                   value="Y"*/}
                            {/*                   data-validation="atLeastOne"*/}
                            {/*                   data-type="review"*/}
                            {/*                   data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                            {/*                   className="form-control input-sm"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="top">Top<span/></label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="top"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][mailshot8Flag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   data-type="topUp"*/}
                            {/*                   data-except="$('#referred').prop('checked') || $('#prospectFlag').prop('checked')"*/}
                            {/*                   className="form-control input-sm"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="sr-rep">SR Rep<span/></label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="sr-rep"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][mailshot11Flag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   className="form-control input-sm"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="mailshot">Mailshot<span/></label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="mailshot"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][sendMailshotFlag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   className="form-control input-sm"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="pendingLeaver">Pending Leaver</label>*/}
                            {/*    <div className="form-group form-inline">*/}
                            {/*        <label className="switch">*/}
                            {/*            <input id="pendingLeaver"*/}
                            {/*                   type="checkbox"*/}
                            {/*                   name="form[contact][0][pendingLeaverFlag]"*/}
                            {/*                   value="Y"*/}
                            {/*                   className="form-control input-sm"*/}
                            {/*            />*/}
                            {/*            <span className="slider round"/>*/}
                            {/*        </label>*/}
                            {/*    </div>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-2">*/}
                            {/*    <label htmlFor="pendingLeaverDate">Pending Leaver*/}
                            {/*        Date</label>*/}
                            {/*    <label className="tick_container d-block">*/}
                            {/*        <input id="pendingLeaverDate"*/}
                            {/*               type="date"*/}
                            {/*               name="pendingLeaverDate"*/}
                            {/*               value="{pendingLeaverDate}"*/}
                            {/*               className="jQueryCalendar form-control"*/}
                            {/*               size="15"*/}
                            {/*               maxLength="5"*/}
                            {/*               autoComplete="off"*/}
                            {/*        />*/}
                            {/*    </label>*/}
                            {/*</div>*/}
                        </div>
                        <div className="row"
                             key="secondRow"
                        >
                            {/*<div className="col-lg-6">*/}
                            {/*    <button className="btn btn-new btn-sm">*/}
                            {/*        Save Details*/}
                            {/*    </button>*/}
                            {/*    <button className="btn btn-outline-primary btn-sm">*/}
                            {/*        Process as Leaver*/}
                            {/*    </button>*/}
                            {/*</div>*/}
                            {/*<div className="col-lg-6">*/}
                            {/*    <button className="btn btn-outline-secondary float-right btn-sm">*/}
                            {/*        Cancel*/}
                            {/*    </button>*/}
                            {/*    <button className="btn btn-outline-danger float-right btn-sm">*/}
                            {/*        Delete*/}
                            {/*    </button>*/}
                            {/*</div>*/}
                        </div>
                    </div>
                </td>
            </tr>
        </React.Fragment>
    }
}

ContactComponent.propTypes = {
    contact: PropTypes.any,
    onChange: PropTypes.any,
    sites: PropTypes.any,
    supportLevelOptions: PropTypes.any
};
