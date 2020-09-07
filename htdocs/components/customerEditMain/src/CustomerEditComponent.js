import Skeleton from "react-loading-skeleton";
import React from 'react';

class CustomerEditComponent extends React.Component {
    render() {
        if (!this.state.loaded) {
            return (
                <Skeleton>
                </Skeleton>
            )
        }

        return (
            <div id="main">
                <div className="container-fluid">
                    <div className="row">
                        <div className="col-md-12">
                            <div className="card">
                                <div className="card-body">
                                    <nav>
                                        <div className="nav nav-tabs"
                                             id="nav-tab"
                                             role="tablist"
                                        >
                                            <a className="nav-item nav-link active"
                                               id="nav-home-tab"
                                               data-toggle="tab"
                                               href="#nav-home"
                                               role="tab"
                                               aria-controls="nav-home"
                                               aria-selected="true"
                                            >Customer</a>
                                            <a className="nav-item nav-link"
                                               id="nav-profile-tab"
                                               data-toggle="tab"
                                               href="#nav-profile"
                                               role="tab"
                                               aria-controls="nav-profile"
                                               aria-selected="false"
                                            >Projects</a>
                                            <a className="nav-item nav-link"
                                               id="nav-contact-tab"
                                               data-toggle="tab"
                                               href="#nav-portal-documents-tab"
                                               role="tab"
                                               aria-controls="nav-portal-documents-tab"
                                               aria-selected="false"
                                            >Portal Documents</a>
                                            <a className="nav-item nav-link"
                                               id="nav-sites-tab"
                                               data-toggle="tab"
                                               href="#nav-sites"
                                               role="tab"
                                               aria-controls="nav-sites"
                                               aria-selected="false"
                                            >Sites</a>
                                            <a className="nav-item nav-link"
                                               id="nav-orders-tab"
                                               data-toggle="tab"
                                               href="#nav-orders"
                                               role="tab"
                                               aria-controls="nav-orders"
                                               aria-selected="false"
                                            >Orders</a>
                                        </div>
                                    </nav>
                                    <div className="tab-content"
                                         id="nav-tabContent"
                                    >
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
                                                                <label>Customer {customerID}</label>
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
                                                                            {primaryMainContactDescription}
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
                                                                    <select name="form[customer][{customerID}][customerTypeID]"
                                                                            className="form-control"
                                                                    >
                                                                        <option value="{customerTypeID}"
                                                                        >{customerTypeDescription}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div className="col-lg-6">
                                                                <label htmlFor="">Sector</label>
                                                                <div className="form-group">
                                                                    <select name="form[customer][{customerID}][sectorID]"
                                                                            className="form-control"
                                                                    >
                                                                        <option value="">Please select</option>

                                                                        <option value="{sectorID}"
                                                                        >{sectorDescription}
                                                                        </option>
                                                                    </select>
                                                                    <span className="formErrorMessage">{SectorMessage}</span>
                                                                </div>
                                                            </div>

                                                            <div className="col-lg-4">
                                                                <label htmlFor="">PCs</label>
                                                                <div className="form-group">
                                                                    <select name="form[customer][{customerID}][noOfPCs]"
                                                                            className="form-control"
                                                                    >
                                                                        <option value="{noOfPCsValue}"
                                                                        >{noOfPCsValue}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div className="col-lg-4">
                                                                <label>Servers</label>
                                                                <div className="form-group">
                                                                    <input name="form[customer][{customerID}][noOfServers]"
                                                                           type="text"
                                                                           value="{noOfServers}"
                                                                           size="10"
                                                                           maxLength="10"
                                                                           className="form-control"
                                                                    />
                                                                </div>
                                                            </div>

                                                            <div className="col-lg-4">
                                                                <label>Reg</label>
                                                                <div className="form-group">
                                                                    <input name="form[customer][{customerID}][regNo]"
                                                                           type="text"
                                                                           value="{regNo}"
                                                                           size="10"
                                                                           maxLength="10"
                                                                           className="form-control"
                                                                    />
                                                                </div>
                                                            </div>
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
                                                                    <h6>{modifyDate}</h6>
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
                                        >{techNotes}</textarea>
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
                                        <div className="tab-pane fade customerEditProjects"
                                             id="nav-profile"
                                             role="tabpanel"
                                             aria-labelledby="nav-profile-tab"
                                        >
                                            <div className="customerEditProjects container-fluid mt-3 mb-3">
                                                <div className="row">
                                                    <div className="col-md-12">
                                                        <h2>Projects</h2>
                                                    </div>
                                                    <div className="col-md-12">
                                                        {/*<a href="{addProjectURL}">*/}
                                                        {/*    <button className="btn btn-primary mt-3 mb-3">Add Project</button>*/}
                                                        {/*</a>*/}
                                                    </div>
                                                </div>
                                                <div className="row">
                                                    <div className="col-md-12">
                                                        <table className="table table-striped table-bordered"
                                                               border="0"
                                                               cellPadding="2"
                                                               cellSpacing="1"
                                                        >
                                                            <thead>
                                                            <tr>
                                                                <td>Items</td>
                                                                <td>Notes</td>
                                                                <td>Starts</td>
                                                                <td>Expires</td>
                                                                <td/>
                                                                <td/>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>{projectName}</td>
                                                                <td>{notes}</td>
                                                                <td>{startDate}</td>
                                                                <td>{expiryDate}</td>
                                                                <td>
                                                                    {/*<a href="{editProjectLink}">*/}
                                                                    <button className="btn btn-outline-secondary">
                                                                        <i className="fa fa-edit"/>
                                                                    </button>
                                                                    {/*</a>*/}
                                                                </td>
                                                                <td>
                                                                    {/*<a href="{deleteProjectLink}">*/}
                                                                    <button className="btn btn-outline-danger">
                                                                        <i className="fa fa-trash"/>
                                                                    </button>
                                                                    {/*</a>*/}
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                        <nav aria-label="Page navigation example">
                                                            <ul className="pagination justify-content-end">
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >Previous</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >1</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >2</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >3</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >Next</a>
                                                                </li>
                                                            </ul>
                                                        </nav>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="tab-pane fade"
                                             id="nav-portal-documents-tab"
                                             role="tabpanel"
                                             aria-labelledby="nav-portal-documents-tab"
                                        >
                                            <div className="container-fluid mt-3 mb-3">
                                                <div className="row">
                                                    <div className="col-md-12">
                                                        <h2>Portal Documents</h2>
                                                    </div>
                                                    <div className="col-md-12">
                                                        <button className="btn btn-primary mt-3 mb-3">Add Document
                                                        </button>
                                                    </div>
                                                </div>
                                                <div className="row">
                                                    <div className="col-md-12">

                                                        <table className="table table-striped table-bordered"
                                                               width="50%"
                                                        >
                                                            <thead>
                                                            <tr>
                                                                <td>Description</td>
                                                                <td>Files</td>
                                                                <td>Starters Form</td>
                                                                <td>Leavers Form</td>
                                                                <td>Main Contact Only</td>
                                                                <td/>
                                                                <td/>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>
                                                                    {/*<a href="{urlViewFile}"                                                                       title="View attached document"*/}
                                                                    {/*>{description}</a>*/}
                                                                </td>
                                                                <td>
                                                                    {/*<a href="{urlViewFile}"*/}
                                                                    {/*   title="View attached document"*/}
                                                                    {/*>{filename}</a>*/}
                                                                </td>
                                                                <td>{startersFormFlag}
                                                                </td>
                                                                <td>{leaversFormFlag}
                                                                </td>
                                                                <td>{mainContactOnlyFlag}
                                                                </td>

                                                                <td>
                                                                    {/*<a href="{urlEditDocument}">*/}
                                                                    <button className="btn btn-outline-secondary">
                                                                        <i className="fa fa-edit"/>
                                                                    </button>
                                                                    {/*</a>*/}
                                                                </td>
                                                                <td>
                                                                    {/*<a href="{urlDeleteDocument}"*/}
                                                                    {/*   title="Delete attached document"*/}
                                                                    {/*   onClick="if(!confirm('Are you sure you want to remove this document?')) return(false)"*/}
                                                                    {/*>*/}
                                                                    <button className="btn btn-outline-danger">
                                                                        <i className="fa fa-trash"/>
                                                                    </button>
                                                                    {/*</a>*/}
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                        <nav aria-label="Page navigation example">
                                                            <ul className="pagination justify-content-end">
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >Previous</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >1</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >2</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >3</a>
                                                                </li>
                                                                <li className="page-item">
                                                                    <a className="page-link"
                                                                       href="#"
                                                                    >Next</a>
                                                                </li>
                                                            </ul>
                                                        </nav>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                        <div className="tab-pane fade show"
                                             id="nav-sites"
                                             role="tabpanel"
                                             aria-labelledby="nav-sites-tab"
                                        >

                                            <div className="container-fluid">
                                                <div className="row">
                                                    <div className="col-md-12">
                                                        <h2>Sites</h2>
                                                    </div>
                                                    <div className="col-md-12">
                                                        {/*<a href="{addSiteURL}">*/}
                                                        <button className="btn btn-primary mt-3 mb-3">Add Site</button>
                                                        {/*</a>*/}
                                                    </div>
                                                </div>
                                                <div className="row">
                                                    <div className="col-md-12">
                                                        <div className="customerEditSites">
                                                            <div className="addSite">
                                                                {/*<a href="{addSiteURL}">{addSiteText}</a>*/}
                                                            </div>
                                                            <div className="accordion"
                                                                 id="accordionExample1"
                                                            >
                                                                <div className="site"
                                                                     style="width: 100%"
                                                                >
                                                                    <div className="card">
                                                                        <div className="card-header"
                                                                             id="heading{siteNo}"
                                                                             style="width: 100%;"
                                                                        >
                                                                            <h5 className="mb-0">
                                                                                {/*<button className="btn btn-link"*/}
                                                                                {/*        type="button"*/}
                                                                                {/*        data-toggle="collapse"*/}
                                                                                {/*        data-target="#collapse{siteNo}"*/}
                                                                                {/*        aria-expanded="false"*/}
                                                                                {/*        aria-controls="collapse{siteNo}"*/}
                                                                                {/*>*/}
                                                                                {/*    {add1}*/}
                                                                                {/*</button>*/}
                                                                            </h5>
                                                                        </div>
                                                                        <input type="hidden"
                                                                               name="form[site][{customerID}{siteNo}][sageRef]"
                                                                               value="{sageRef}"
                                                                               size="3"
                                                                               maxLength="6"
                                                                        />
                                                                        <input type="hidden"
                                                                               name="form[site][{customerID}{siteNo}][siteNo]"
                                                                               value="{siteNo}"
                                                                        />
                                                                        <input type="hidden"
                                                                               name="form[site][{customerID}{siteNo}][customerID]"
                                                                               value="{customerID}"
                                                                        />
                                                                        <input type="hidden"
                                                                               name="form[site][{customerID}{siteNo}][debtorCode]"
                                                                               value="{debtorCode}"
                                                                        />
                                                                        <div id="collapse{siteNo}"
                                                                             className="collapse"
                                                                             aria-labelledby="{siteNo}"
                                                                             data-parent="#accordionExample1"
                                                                        >
                                                                            <div className="card-body">
                                                                                <div className="row">
                                                                                    <div className="col-lg-4">
                                                                                        <div className="form-group">

                                                                                            <label>Site Address</label>
                                                                                            <input name="form[site][{customerID}{siteNo}][add1]"
                                                                                                   value="{add1}"
                                                                                                   size="35"
                                                                                                   maxLength="35"
                                                                                                   className="form-control mb-3"
                                                                                            />
                                                                                            <input name="form[site][{customerID}{siteNo}][add2]"
                                                                                                   value="{add2}"
                                                                                                   size="35"
                                                                                                   maxLength="35"
                                                                                                   className="form-control mb-3"

                                                                                            />
                                                                                            <input name="form[site][{customerID}{siteNo}][add3]"
                                                                                                   value="{add3}"
                                                                                                   size="35"
                                                                                                   maxLength="35"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>

                                                                                    <div className="col-lg-4">
                                                                                        <label htmlFor="town">Town</label>
                                                                                        <div className="form-group">
                                                                                            <input id="town"
                                                                                                   name="form[site][{customerID}{siteNo}][town]"
                                                                                                   value="{town}"
                                                                                                   size="25"
                                                                                                   maxLength="25"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-4">
                                                                                        <label htmlFor="country">Country</label>
                                                                                        <div className="form-group">
                                                                                            <input id="country"
                                                                                                   name="form[site][{customerID}{siteNo}][county]"
                                                                                                   value="{county}"
                                                                                                   size="25"
                                                                                                   maxLength="25"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-4">
                                                                                        <label htmlFor="postcode">Postcode</label>
                                                                                        <div className="form-group">
                                                                                            <input id="postcode"
                                                                                                   name="form[site][{customerID}{siteNo}][postcode]"
                                                                                                   value="{postcode}"
                                                                                                   size="15"
                                                                                                   maxLength="15"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-4">
                                                                                        <label htmlFor="phone">Phone</label>
                                                                                        <div className="form-group">
                                                                                            <input id="phone"
                                                                                                   name="form[site][{customerID}{siteNo}][phone]"
                                                                                                   value="{sitePhone}"
                                                                                                   size="20"
                                                                                                   maxLength="20"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-4">
                                                                                        <label htmlFor="form[site][{customerID}{siteNo}][maxTravelHours]">Max
                                                                                            Travel Hours</label>
                                                                                        <div className="form-group">
                                                                                            <input name="form[site][{customerID}{siteNo}][maxTravelHours]"
                                                                                                   id="form[site][{customerID}{siteNo}][maxTravelHours]"
                                                                                                   value="{maxTravelHours}"
                                                                                                   size="5"
                                                                                                   maxLength="5"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-2">
                                                                                        <label htmlFor="default-voices">Default
                                                                                            Invoice</label>
                                                                                        <div className="form-group form-inline">
                                                                                            <input id="default-voices"
                                                                                                   type="radio"
                                                                                                   name="form[customer][{customerID}][invoiceSiteNo]"
                                                                                                   value="{siteNo}"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-2">
                                                                                        <label htmlFor="default-delivery">Default
                                                                                            Delivery</label>
                                                                                        <div className="form-group form-inline">
                                                                                            <input id="default-delivery"
                                                                                                   type="radio"
                                                                                                   name="form[customer][{customerID}][deliverSiteNo]"
                                                                                                   value="{siteNo}"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-4">
                                                                                        <label htmlFor="invoice-contact">Invoice
                                                                                            Contact</label>
                                                                                        <div className="form-group">
                                                                                            <select
                                                                                                name="form[site][{customerID}{siteNo}][invoiceContactID]"
                                                                                                className="form-control"
                                                                                            >
                                                                                                <option value="{selectInvoiceContactBlockContactID}"
                                                                                                >
                                                                                                    {selectInvoiceContactBlockFirstName}
                                                                                                    {selectInvoiceContactBlockLastName}
                                                                                                </option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-4">
                                                                                        <label htmlFor="default-contact">Delivery
                                                                                            Contact</label>
                                                                                        <div className="form-group">
                                                                                            <select
                                                                                                id="default-contact"
                                                                                                name="form[site][{customerID}{siteNo}][deliverContactID]"
                                                                                                className="form-control"
                                                                                            >
                                                                                                <option value="{selectDeliverContactBlockContactID}"
                                                                                                >
                                                                                                    {selectDeliverContactBlockFirstName}
                                                                                                    {selectDeliverContactBlockLastName}
                                                                                                </option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-2">
                                                                                        <label htmlFor="non-uk">Non
                                                                                            UK</label>
                                                                                        <div className="form-group form-inline">
                                                                                            <input type="checkbox"
                                                                                                   name="form[site][{customerID}{siteNo}][nonUKFlag]"
                                                                                                   title="Check to show this site is overseas and not in the UK"
                                                                                                   value="Y"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-2">
                                                                                        <label>Active</label>
                                                                                        <div className="form-group form-inline">
                                                                                            <input type="checkbox"
                                                                                                   name="form[site][{customerID}{siteNo}][activeFlag]"
                                                                                                   value="Y"
                                                                                                   className="form-control"
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-lg-12">
                                                                                        <button type="button"
                                                                                                className="btn btn-primary"
                                                                                                onClick="addContact({siteNo})"
                                                                                        >Add
                                                                                            Contact
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div className="tab-pane fade show"
                                             id="nav-orders"
                                             role="tabpanel"
                                             aria-labelledby="nav-orders-tab"
                                        >
                                            <div className="container-fluid mt-3 mb-3">
                                                <div className="row">
                                                    <div className="col-md-12">
                                                        <h2>Orders</h2>
                                                    </div>
                                                </div>
                                                <div className="row">
                                                    <div className="col-md-12">
                                                        <table className="table table-striped table-bordered">
                                                            <thead>
                                                            <tr>
                                                                {/*<td className="fitwidth">Order No.</td>*/}
                                                                {/*<td className="fitwidth">Type</td>*/}
                                                                {/*<td className="fitwidth">Date</td>*/}
                                                                {/*<td className="fitwidth">Cast PO Ref</td>*/}
                                                                {/*<td className="fitwidth">Contract</td>*/}

                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                {/*<td><h6>687980</h6></td>*/}
                                                                {/*<td><h6>687980</h6></td>*/}
                                                                {/*<td><h6>31 / 03 / 2019</h6></td>*/}
                                                                {/*<td><h6>Lorem ipsum.</h6></td>*/}
                                                                {/*<td><h6>Lorem ipsum.</h6></td>*/}

                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                        <nav aria-label="Page navigation example">
                                                            <ul className="pagination justify-content-end">
                                                                <li className="page-item"><a className="page-link"
                                                                                             href="#"
                                                                >Previous</a>
                                                                </li>
                                                                <li className="page-item"><a className="page-link"
                                                                                             href="#"
                                                                >1</a></li>
                                                                <li className="page-item"><a className="page-link"
                                                                                             href="#"
                                                                >2</a></li>
                                                                <li className="page-item"><a className="page-link"
                                                                                             href="#"
                                                                >3</a></li>
                                                                <li className="page-item"><a className="page-link"
                                                                                             href="#"
                                                                >Next</a>
                                                                </li>
                                                            </ul>
                                                        </nav>
                                                    </div>
                                                </div>
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
}

export default CustomerEditComponent;

