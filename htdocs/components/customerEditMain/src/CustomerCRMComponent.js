import React from "react";
import Select from "./Select";

export class CustomerCRMComponent extends React.Component {

    constructor(props, context) {
        super(props, context);
        this.state = {
            customer: {},
            site: {}
        };


    }

    render() {
        return (
            <div className="tab-pane fade show"
                 id="nav-crm"
                 role="tabpanel"
            >
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
                                    <table className="table table-borderless">
                                        <tbody>
                                        <tr>
                                            <td>Site Address</td>
                                            <td>
                                                <input type="text"
                                                       className="form-control input-sm"
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td/>
                                            <td>
                                                <input type="text"
                                                       className="form-control input-sm"
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td/>
                                            <td>
                                                <input type="text"
                                                       className="form-control input-sm"
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Town</td>
                                            <td><input type="text"
                                                       className="form-control input-sm"
                                            />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>County</td>
                                            <td>
                                                <input type="text"
                                                       className="form-control input-sm"
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Postcode</td>
                                            <td>
                                                <input type="text"
                                                       className="form-control input-sm"
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Phone</td>
                                            <td>
                                                <input type="text"
                                                       className="form-control input-sm"
                                                />
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div className="card-footer">
                                    <button className="btn btn-sm btn-new">Add Contact</button>

                                </div>
                            </div>

                        </div>
                        <div className="col-md-3">
                            <div className="card">
                                <div className="card-body p-1">
                                    <table className="table table-borderless">
                                        <tbody>
                                        <tr>
                                            <td>Lead Status</td>
                                            <td>
                                                <Select
                                                    options={this.state.leadStatuses}
                                                    selectedOption={this.state.customer.leadStatusId || ''}
                                                    onChange={this.handleLeadStatusIdUpdate}
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
                                                               checked={this.state.customer.mailshotFlag === 'Y'}
                                                               onChange={this.handleMailshotFlagUpdate}
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
                                                       value={this.state.customer.dateMeetingConfirmed}
                                                       className="form-control input-sm"
                                                />
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Mailshot</td>
                                            <td>
                                                <div className="form-inline pt-1">
                                                    <label className="switch">
                                                        <input type="checkbox"
                                                               {disabled}
                                                               name="form[customer][{customerID}][mailshotFlag]"
                                                               value="Y"
                                                               {mailshotFlagChecked}
                                                        />
                                                        <span className="slider round"/>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                        <div className="col-md-6">
                            <div className="card">
                                <div className="card-body p-1">
                                    <div className="pw-wisywig">
                                        <div id="alerts"/>
                                        <div className="btn-toolbar editor"
                                             data-role="editor-toolbar"
                                             data-target="#editor-one"
                                        >
                                            <div className="btn-group">
                                                <a className="btn dropdown-toggle"
                                                   data-toggle="dropdown"
                                                   title="Font"
                                                ><i className="fal fa-font"/><b className="caret"/></a>
                                                <ul className="dropdown-menu">
                                                </ul>
                                            </div>
                                            <div className="btn-group">
                                                <a className="btn dropdown-toggle"
                                                   data-toggle="dropdown"
                                                   title="Font Size"
                                                ><i className="fal fa-text-height"/>&nbsp;<b className="caret"/></a>
                                                <ul className="dropdown-menu">
                                                    <li>
                                                        <a data-edit="fontSize 5">
                                                            <p style="font-size:17px">Huge</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a data-edit="fontSize 3">
                                                            <p style="font-size:14px">Normal</p>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a data-edit="fontSize 1">
                                                            <p style="font-size:11px">Small</p>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div className="btn-group">
                                                <a className="btn"
                                                   data-edit="bold"
                                                   title="Bold (Ctrl/Cmd+B)"
                                                >
                                                    <i className="fal fa-bold"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="italic"
                                                   title="Italic (Ctrl/Cmd+I)"
                                                >
                                                    <i className="fal fa-italic"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="strikethrough"
                                                   title="Strikethrough"
                                                >
                                                    <i className="fal fa-strikethrough"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="underline"
                                                   title="Underline (Ctrl/Cmd+U)"
                                                >
                                                    <i className="fal fa-underline"/>
                                                </a>
                                            </div>
                                            <div className="btn-group">
                                                <a className="btn"
                                                   data-edit="insertunorderedlist"
                                                   title="Bullet list"
                                                >
                                                    <i className="fal fa-list-ul"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="insertorderedlist"
                                                   title="Number list"
                                                >
                                                    <i className="fal fa-list-ol"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="outdent"
                                                   title="Reduce indent (Shift+Tab)"
                                                >
                                                    <i className="fal fa-dedent"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="indent"
                                                   title="Indent (Tab)"
                                                >
                                                    <i className="fal fa-indent"/>
                                                </a>
                                            </div>
                                            <div className="btn-group">
                                                <a className="btn btn-primary"
                                                   data-edit="justifyleft"
                                                   title="Align Left (Ctrl/Cmd+L)"
                                                >
                                                    <i className="fal fa-align-left"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="justifycenter"
                                                   title="Center (Ctrl/Cmd+E)"
                                                >
                                                    <i className="fal fa-align-center"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="justifyright"
                                                   title="Align Right (Ctrl/Cmd+R)"
                                                >
                                                    <i className="fal fa-align-right"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="justifyfull"
                                                   title="Justify (Ctrl/Cmd+J)"
                                                >
                                                    <i className="fal fa-align-justify"/>
                                                </a>
                                            </div>
                                            <div className="btn-group">
                                                <a className="btn dropdown-toggle"
                                                   data-toggle="dropdown"
                                                   title="Hyperlink"
                                                >
                                                    <i className="fal fa-link"/>
                                                </a>
                                                <div className="dropdown-menu input-append">
                                                    <input className="span2"
                                                           placeholder="URL"
                                                           type="text"
                                                           data-edit="createLink"
                                                    />
                                                    <button className="btn"
                                                            type="button"
                                                    >Add
                                                    </button>
                                                </div>
                                                <a className="btn"
                                                   data-edit="unlink"
                                                   title="Remove Hyperlink"
                                                >
                                                    <i className="fal fa-cut"/>
                                                </a>
                                            </div>
                                            <div className="btn-group">
                                                <a className="btn"
                                                   title="Insert picture (or just drag &amp; drop)"
                                                   id="pictureBtn"
                                                >
                                                    <i className="fal fa-picture-o"/>
                                                </a>
                                                <input type="file"
                                                       data-role="magic-overlay"
                                                       data-target="#pictureBtn"
                                                       data-edit="insertImage"
                                                />
                                            </div>
                                            <div className="btn-group">
                                                <a className="btn"
                                                   data-edit="undo"
                                                   title="Undo (Ctrl/Cmd+Z)"
                                                >
                                                    <i className="fal fa-undo"/>
                                                </a>
                                                <a className="btn"
                                                   data-edit="redo"
                                                   title="Redo (Ctrl/Cmd+Y)"
                                                >
                                                    <i className="fal fa-repeat"/>
                                                </a>
                                            </div>
                                        </div>
                                        <div id="editor-one"
                                             className="editor-wrapper placeholderText"
                                             contentEditable="true"
                                        />
                                        <textarea name="descr"
                                                  id="descr"
                                                  style="display:none;"
                                        />
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div className="col-lg-3">

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
                                    <td>{title} {firstName} {lastName}</td>
                                    <td>{position}</td>
                                    <td>{phone}</td>
                                    <td>{mobilePhone}</td>
                                    <td>{email}</td>
                                    <td>{supportLevel}</td>
                                    <td>{inv}</td>
                                    <td>{stm}</td>
                                    <td>{hr}</td>
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
                                                            <!-- BEGIN selectSiteBlock -->
                                                            <option {siteSelected}
                                                                    value="{selectSiteNo}"
                                                            >{selectSiteDesc}
                                                            </option>
                                                            <!-- END selectSiteBlock -->
                                                        </select>
                                                    </div>

                                                </div>
                                                <div className="col-lg-4">
                                                    <div className="form-group">
                                                        <label htmlFor="">Title <span>*</span></label>
                                                        <input {disabled}
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
                                                        <input {disabled}
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
                                                        <input {disabled}
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
                                                        <input {disabled}
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
                                                        <input {disabled}
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
                                                        <input {disabled}
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
                                                               {disabled}
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
                                                            <!-- BEGIN supportLevelBlock -->
                                                            <option {supportLevelSelected}
                                                                    data-test="{supportLevelSelected}"
                                                                    value="{supportLevelValue}"
                                                            >
                                                                {supportLevelDescription}
                                                            </option>
                                                            <!-- END supportLevelBlock -->
                                                        </select>
                                                    </div>
                                                </div>
                                                <div className="col-lg-4">
                                                    <div className="form-group">
                                                        <label htmlFor="">Notes<span/></label>
                                                        <input {disabled}
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
                                                        <label htmlFor="">
                                                            Failed Login<span/>
                                                        </label>
                                                        <input {disabled}
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
                                                                   {disabled}
                                                                   type="checkbox"
                                                                   name="form[contact][{contactID}][initialLoggingEmailFlag]"
                                                                   value="Y"
                                                                   {initialLoggingEmailFlagChecked}
                                                                   className="tick_field"
                                                            />
                                                            <span className="slider round"/>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div className="col-lg-2">
                                                    <label htmlFor="work">Work</label>
                                                    <div className="form-group form-inline">
                                                        <label className="switch">
                                                            <input id="work"
                                                                   {disabled}
                                                                   type="checkbox"
                                                                   name="form[contact][{contactID}][workUpdatesEmailFlag]"
                                                                   value="Y"
                                                                   {workUpdatesEmailFlagChecked}
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
                                                                   {disabled}=""
                                                                   name="form[site][{customerID}{siteNo}][nonUKFlag]"
                                                                   title="Check to show this site is overseas and not in the UK"
                                                                   value="Y"
                                                                   {nonukflagchecked}=""
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
                                                                   {accountsFlagChecked}
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
                                                    <label htmlFor="stm">Stm<span/></label>
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
                                                                   {topUpValidation}
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
                                                               type="text"
                                                               name="form[contact][0][pendingLeaverDate]"
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
                                                    <button className="btn btn-new btn-sm">Save
                                                        Details
                                                    </button>
                                                    <button className="btn btn-outline-primary btn-sm">
                                                        Process
                                                        as Leaver
                                                    </button>
                                                </div>
                                                <div className="col-lg-6">
                                                    <button className="btn btn-outline-secondary float-right btn-sm">
                                                        Cancel
                                                    </button>
                                                    <button className="btn btn-danger float-right btn-sm">
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
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <h4>Notes</h4>
                        <div className="row">
                            <div className="col-md-4">
                                <div className="form-group">
                                    <label htmlFor="reviewDate">To be received on:</label>
                                    <input {disabled}
                                           type="text"
                                           name="form[customer][{customerID}][reviewDate]"
                                           id="reviewDate"
                                           value="{reviewDate}"
                                           maxLength="10"
                                           autoComplete="off"
                                           className="jQueryCalendar form-control input-sm"
                                    />
                                </div>
                            </div>
                            <div className="col-md-4">
                                <div className="form-group">
                                    <label htmlFor="">Time:</label>
                                    <input {disabled}
                                           name="form[customer][{customerID}][reviewTime]"
                                           value="{reviewTime}"
                                           size="5"
                                           maxLength="5"
                                           className="form-control input-sm"
                                    />
                                </div>
                            </div>
                            <div className="col-md-4">
                                <div className="form-group">

                                    <label>By:</label>
                                    <select
                                        {disabled}
                                        name="form[customer][{customerID}][reviewUserID]"
                                        onChange="setFormChanged();"
                                        className="form-control input-sm"
                                    >
                                        <!-- BEGIN reviewUserBlock -->
                                        <option {reviewUserSelected}
                                                value="{reviewUserID}"
                                        >{reviewUserName}
                                        </option>
                                        <!-- END reviewUserBlock -->
                                    </select>
                                    <span
                                        className="formErrorMessage formError"
                                    >{reviewTimeMessage}</span>
                                </div>

                            </div>
                        </div>


                        <div className="form-group customerReviewAction">
                                        <textarea title="Action to be taken"
                                                  {disabled}
                                                  cols="120"
                                                  rows="3"
                                                  name="form[customer][{customerID}][reviewAction]"
                                                  className="form-control input-sm"
                                        >{reviewAction}</textarea>
                        </div>
                        <div className="form-group customerNoteHistory">
                                        <textarea cols="30"
                                                  rows="12"
                                                  readOnly="readonly"
                                                  {disabled}
                                                  id="customerNoteHistory"
                                                  className="form-control input-sm"
                                        />

                            <div className="customerNoteNav mt-3 mb-3">
                                <button {disabled}
                                        type="button"
                                        name="First"
                                        aria-hidden="true"
                                        onClick="loadNote('first')"
                                        className="btn secondary"
                                >
                                    <i className="fal fa-step-backward fa-lg">
                                    </i>
                                </button>

                                <button {disabled}
                                        type="button"
                                        name="Previous"
                                        onClick="loadNote('previous')"
                                        className="btn secondary"
                                >
                                    <i className="fal fa-backward fa-lg"
                                       aria-hidden="true"
                                    >
                                    </i>
                                </button>
                                <button {disabled}
                                        type="button"
                                        name="Next"
                                        onClick="loadNote('next')"
                                        className="btn secondary"
                                >
                                    <i className="fal fa-forward fa-lg"
                                       aria-hidden="true"
                                    >
                                    </i>
                                </button>

                                <button {disabled}
                                        type="button"
                                        name="Last"
                                        onClick="loadNote('last')"
                                        className="btn secondary"
                                >
                                    <i className="fal fa-step-forward fa-lg"
                                       aria-hidden="true"
                                    >
                                    </i>
                                </button>
                                <button {disabled}
                                        type="button"
                                        name="Delete"
                                        onClick="deleteNote()"
                                        className="btn secondary"
                                >
                                    <i className="fal fa-trash-alt fa-lg"
                                       aria-hidden="true"
                                    >
                                    </i>
                                </button>
                                <button {disabled}
                                        type="button"
                                        name="New"
                                        onClick="newNote()"
                                        className="btn secondary"
                                >
                                    <i className="fal fa-plus fa-lg"
                                       aria-hidden="true"
                                    >
                                    </i>
                                </button>
                                <button {disabled}
                                        type="button"
                                        name="Save"
                                        onClick="saveNote()"
                                        className="btn secondary"
                                >
                                    <i className="fal fa-floppy-o fa-lg"
                                       aria-hidden="true"
                                    >
                                    </i>

                                </button>

                            </div>
                            {customerNotePopupLink}
                            <!--<h6>Note History</h6>-->

                            <!--<ul>-->
                            <!--<li>28/09/2010 by Valerie</li>-->
                            <!--<li>28/09/2010 by Valerie</li>-->
                            <!--<li>28/09/2010 by Valerie</li>-->
                            <!--</ul>-->

                        </div>

                    </div>
                    <div className="col-md-6">
                        <div className="form-group customerNoteDetails">
                                <textarea
                                    {disabled}
                                    name="customerNoteDetails"
                                    id="customerNoteDetails"
                                    cols="120"
                                    onChange="setCustomerNotesChanged()"
                                    rows="12"
                                    className="form-control input-sm"
                                >{customerNoteDetails}
                                </textarea>
                        </div>
                        <div>
                            {lastContractSent}
                        </div>
                    </div>

                </div>

            </div>
        );
    }
}