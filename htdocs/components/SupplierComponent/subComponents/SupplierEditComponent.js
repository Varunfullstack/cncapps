import React from 'react';
import Select from "../../customerEditMain/Select";
import EncryptedTextInput from "../../customerEditMain/EncryptedTextInput";
import {params} from "../../utils/utils";

export class SupplierEditComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
        const supplierId = params.get('supplierId');
        this.state = {
            supplierId,
            supplier: null
        }
    }

    async componentDidMount() {
        const response = await fetch(`/Supplier.php?action=getSupplierData&supplierId=${this.state.supplierId}`);
        const jsonResponse = await response.json();
        if (!jsonResponse || jsonResponse.status !== 'ok') {
            alert('Failed to retrieve Supplier: ' + jsonResponse.message);
        } else {
            this.setState({supplier: jsonResponse.data});
        }
    }

    render() {

        const {supplier} = this.state;

        if (!supplier) {
            return '';
        }

        return (
            <div className="mt-3">
                <div className="row">
                    <div className="col-md-6 mb-3">
                        <h2>Customer - {supplier.name}
                            <a href="#">
                                <i className="fal fa-globe"/>
                            </a>
                        </h2>
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <div className="card mb-3">
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-md-12">
                                        <h5>Key Details</h5>
                                    </div>
                                    <div className="col-lg-6">
                                        <label>Customer {supplier.id}</label>
                                        <div className="form-group">
                                            <input type="text"
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   value={supplier.name || ''}
                                                   size="50"
                                                   maxLength="50"
                                                   name="name"
                                                   className="form-control input-sm"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-3">
                                        <label>Referred</label>
                                        <div className="form-group form-inline pt-1">
                                            <label className="switch">
                                                <input type="checkbox"
                                                       checked={customer.referredFlag === 'Y'}
                                                       onChange={$event => this.handleFlagUpdate($event)}
                                                       name="referredFlag"
                                                />
                                                <span className="slider round"/>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-lg-3">
                                        <label>24 Hour Cover</label>
                                        <div className="form-group form-inline pt-1">
                                            <label className="switch"
                                            >
                                                <input type="checkbox"
                                                       checked={customer.support24HourFlag === 'Y'}
                                                       onChange={$event => this.handleFlagUpdate($event)}
                                                       name="support24HourFlag"
                                                />
                                                <span className="slider round"/>
                                            </label>

                                        </div>
                                    </div>
                                    <div className="col-lg-6">
                                        <label htmlFor="">Special Attention</label>
                                        <div className="form-group form-inline">
                                            <label className="switch mr-3">
                                                <input type="checkbox"
                                                       onChange={$event => this.handleFlagUpdate($event)}
                                                       checked={customer.specialAttentionFlag === 'Y'}
                                                       name="specialAttentionFlag"
                                                />
                                                <span className="slider round"/>
                                            </label>
                                            <div className="form-group mr-3">
                                                <label className="pr-3"
                                                >
                                                    Until
                                                </label>
                                                <input type="date"
                                                       value={customer.specialAttentionEndDate || ''}
                                                       size="10"
                                                       maxLength="10"
                                                       className="form-control input-sm"
                                                       onChange={($event) => this.handleUpdateGenericField($event)}
                                                       name="specialAttentionEndDate"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="card mb-3">
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-md-12"><h5>Review Meetings</h5></div>
                                    <div className="col-lg-12">
                                        <div className="form-inline">
                                            <div className="form-group mr-3">
                                                <label htmlFor="ex3"
                                                       className="col-form-label pr-3"
                                                >Last Review Meeting
                                                </label>
                                                <input type="date"
                                                       onChange={($event) => this.handleUpdateGenericField($event)}
                                                       value={customer.lastReviewMeetingDate || ''}
                                                       size="10"
                                                       maxLength="10"
                                                       className="form-control input-sm"
                                                       name="lastReviewMeetingDate"
                                                />
                                            </div>
                                            <div className="checkbox mr-3 d-flex p-2 justify-content-between align-items-center">
                                                <label className="pr-3">Booked</label>
                                                <label className="switch inline"
                                                >
                                                    <input type="checkbox"
                                                           onChange={this.handleCheckboxFieldUpdate}
                                                           checked={customer.reviewMeetingBooked}
                                                           name="reviewMeetingBooked"
                                                    />
                                                    <span className="slider round"/>
                                                </label>

                                            </div>
                                            <div className="form-group">
                                                <label htmlFor="ex4"
                                                       className="col-form-label pr-3"
                                                >Frequency</label>
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
                                                    selectedOption={customer.reviewMeetingFrequencyMonths || ''}
                                                    onChange={($event) => this.handleUpdateGenericField($event)}
                                                    name="reviewMeetingFrequencyMonths"
                                                    className="form-control input-sm"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="card mb-3">
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-md-12"><h5>Accounts</h5></div>
                                    <div className="col-lg-4">
                                        <label>Became Customer</label>
                                        <div className="form-group">

                                            <input type="date"
                                                   value={customer.becameCustomerDate || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="10"
                                                   maxLength="10"
                                                   className="form-control input-sm"
                                                   name="becameCustomerDate"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label>Dropped Date</label>
                                        <div className="form-group">
                                            <input type="date"
                                                   value={customer.droppedCustomerDate || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="10"
                                                   maxLength="10"
                                                   className="form-control input-sm"
                                                   name="droppedCustomerDate"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label>Account Manager</label>
                                        <div className="form-group">
                                            <Select options={accountManagers}
                                                    selectedOption={customer.accountManagerUserID || ''}
                                                    onChange={($event) => this.handleUpdateGenericField($event)}
                                                    key={'accountManager'}
                                                    className="form-control input-sm"
                                                    name="accountManagerUserID"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="card">
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-md-12"><h5>Sector and Size</h5></div>
                                    <div className="col-lg-6">
                                        <label htmlFor="">Type</label>
                                        <div className="form-group">
                                            <Select options={customerTypes}
                                                    className="form-control input-sm"
                                                    selectedOption={customer.customerTypeID || ''}
                                                    onChange={($event) => this.handleUpdateGenericField($event)}
                                                    name="customerTypeID"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-6">
                                        <label htmlFor="">Sector</label>
                                        <div className="form-group">
                                            <Select options={sectors}
                                                    selectedOption={customer.sectorID || ''}
                                                    onChange={($event) => this.handleUpdateGenericField($event)}
                                                    className="form-control input-sm"
                                                    name="sectorID"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label htmlFor="">PCs</label>
                                        <div className="form-group">
                                            <input type="number"
                                                   value={customer.noOfPCs || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   className="form-control input-sm"
                                                   name="noOfPCs"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label>Servers</label>
                                        <div className="form-group">
                                            <input type="number"
                                                   value={customer.noOfServers || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   className="form-control input-sm"
                                                   name="noOfServers"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label>Sites</label>
                                        <div className="form-group">
                                            <input type="number"
                                                   value={customer.noOfSites || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="2"
                                                   maxLength="2"
                                                   className="form-control input-sm"
                                                   name="noOfSites"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label htmlFor="">Sort Code</label>
                                        <div className="form-group">
                                            <EncryptedTextInput encryptedValue={customer.sortCode}
                                                                onChange={this.handleUpdateGenericField}
                                                                mask='99-99-99'
                                                                name="sortCode"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label htmlFor="">Account Name</label>
                                        <div className="form-group">
                                            <EncryptedTextInput className="form-control input-sm"
                                                                encryptedValue={customer.accountName || ''}
                                                                name="accountName"
                                                                onChange={this.handleUpdateGenericField}
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label htmlFor="">Account Number</label>
                                        <div className="form-group">
                                            <EncryptedTextInput
                                                encryptedValue={customer.accountNumber}
                                                onChange={this.handleUpdateGenericField}
                                                mask='99999999'
                                                name="accountNumber"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label>Reg</label>
                                        <div className="form-group">
                                            <input type="text"
                                                   value={customer.regNo || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="10"
                                                   maxLength="10"
                                                   className="form-control input-sm"
                                                   name="regNo"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label>Pre-pay Top Up</label>
                                        <div className="form-group">
                                            <input type="text"
                                                   value={customer.gscTopUpAmount || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="10"
                                                   maxLength="10"
                                                   className="form-control input-sm"
                                                   name="gscTopUpAmount"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className="card mb-3">
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-md-12"><h5>Service Level Agreements</h5></div>

                                    <div className="col-lg-12">
                                        <label htmlFor="">SLA Response Hours</label>
                                        <div className="form-group form-inline">
                                            <label style={{margin: "0 .5rem"}}>1</label>
                                            <input type="number"
                                                   value={customer.slaP1 || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="1"
                                                   maxLength="3"
                                                   className="form-control col-sm-4"
                                                   name="slaP1"
                                            />
                                            <label style={{margin: "0 .5rem"}}>2</label>
                                            <input type="number"
                                                   value={customer.slaP2 || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="1"
                                                   maxLength="3"
                                                   className="form-control col-sm-4"
                                                   name="slaP2"
                                            />
                                        </div>
                                        <div className="form-group form-inline">

                                            <label style={{margin: "0 .5rem"}}>3</label>
                                            <input type="number"
                                                   value={customer.slaP3 || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="1"
                                                   maxLength="3"
                                                   className="form-control col-sm-4"
                                                   name="slaP3"
                                            />
                                            <label style={{margin: "0 .5rem"}}>4</label>
                                            <input type="number"
                                                   value={customer.slaP4 || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="1"
                                                   maxLength="3"
                                                   className="form-control col-sm-4"
                                                   name="slaP3"
                                            />

                                        </div>

                                        <div className="form-group form-inline">
                                            <label style={{margin: "0 .5rem"}}>5</label>
                                            <input type="number"
                                                   value={customer.slaP5 || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="1"
                                                   maxLength="3"
                                                   className="form-control col-sm-4"
                                                   name="slaP5"
                                            />
                                        </div>
                                    </div>
                                    <div className="col-lg-12">
                                        <label htmlFor="">SLA Response Fix Hours</label>
                                        <div className="form-group form-inline">
                                            <label style={{margin: "0 .5rem"}}>1</label>
                                            <input value={customer.slaFixHoursP1 || ''}
                                                   type="number"
                                                   size="1"
                                                   step="0.1"
                                                   maxLength="4"
                                                   max="999.9"
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   className="form-control col-sm-4"
                                                   name="slaFixHoursP1"
                                            />
                                            <label style={{margin: "0 .5rem"}}>2</label>
                                            <input value={customer.slaFixHoursP2 || ''}
                                                   type="number"
                                                   size="1"
                                                   step="0.1"
                                                   maxLength="4"
                                                   max="999.9"
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   className="form-control col-sm-4"
                                                   name="slaFixHoursP2"
                                            />
                                        </div>
                                        <div className="form-group form-inline">
                                            <label style={{margin: "0 .5rem"}}>3</label>
                                            <input value={customer.slaFixHoursP3 || ''}
                                                   type="number"
                                                   size="1"
                                                   step="0.1"
                                                   maxLength="4"
                                                   max="999.9"
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   className="form-control col-sm-4"
                                                   name="slaFixHoursP3"
                                            />
                                            <label style={{margin: "0 .5rem"}}>4</label>
                                            <input value={customer.slaFixHoursP4 || ''}
                                                   type="number"
                                                   size="1"
                                                   step="0.1"
                                                   maxLength="4"
                                                   max="999.9"
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   className="form-control col-sm-4"
                                                   name="slaFixHoursP4"
                                            />

                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label htmlFor="">SLA Penalties Agreed</label>
                                        <div className="form-group form-inline pt-1 d-flex">
                                            <div className="toggle-inline">
                                                <label>1</label>
                                                <label className="switch"
                                                >
                                                    <input type="checkbox"
                                                           checked={customer.slaP1PenaltiesAgreed || ''}
                                                           onChange={($event) => this.handleCheckboxFieldUpdate($event)}
                                                           name="slaP1PenaltiesAgreed"
                                                    />
                                                    <span className="slider round"/>
                                                </label>
                                            </div>

                                            <div className="toggle-inline">
                                                <label>2</label>
                                                <label className="switch"
                                                >
                                                    <input type="checkbox"
                                                           checked={customer.slaP2PenaltiesAgreed || ''}
                                                           onChange={($event) => this.handleCheckboxFieldUpdate($event)}
                                                           name="slaP2PenaltiesAgreed"
                                                    />
                                                    <span className="slider round"/>
                                                </label>
                                            </div>
                                            <div className="toggle-inline">
                                                <label>3</label>
                                                <label className="switch"
                                                >
                                                    <input type="checkbox"
                                                           checked={customer.slaP3PenaltiesAgreed || ''}
                                                           onChange={($event) => this.handleCheckboxFieldUpdate($event)}
                                                           name="slaP3PenaltiesAgreed"
                                                    />
                                                    <span className="slider round"/>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-lg-4">
                                        <label>Last Modified:</label>
                                        <div className="form-group">
                                            <h6>{customer.lastModified}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="card">
                            <div className="card-body">
                                <div className="row">
                                    <div className="col-md-12"><h5>Technical Notes</h5></div>
                                    <div className="col-lg-6">
                                        <label>Technical Notes</label>
                                        <div className="form-group">
                                                <textarea className="form-control input-sm"
                                                          cols="30"
                                                          rows="2"
                                                          value={customer.techNotes || ''}
                                                          onChange={($event) => this.handleUpdateGenericField($event)}
                                                          name="techNotes"
                                                />
                                        </div>
                                    </div>
                                    <div className="col-lg-6">
                                        <label>Active Directory Name</label>
                                        <div className="form-group">
                                            <input type="text"
                                                   value={customer.activeDirectoryName || ''}
                                                   onChange={($event) => this.handleUpdateGenericField($event)}
                                                   size="54"
                                                   maxLength="255"
                                                   className="form-control input-sm"
                                                   name="activeDirectoryName"
                                            />
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