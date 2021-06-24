"use strict";
import React from 'react';
import Select from "./Select";
import EncryptedTextInput from "./EncryptedTextInput";
//import {connect} from "react-redux";
import {getMainContacts, getAllContacts} from "./selectors/selectors";
import {updateCustomerField} from "./actions";
import APICustomers from '../services/APICustomers';
import PureComponent from '../shared/PureComponent';
import Toggle from '../shared/Toggle';
import MainComponent from '../shared/MainComponent';
import APIUser from '../services/APIUser';
import APILeadStatusTypes from '../LeadStatusTypes/services/APILeadStatusTypes';

export default class CustomerEditMain extends MainComponent {
     
    api= new APICustomers();
    apiUsers=new APIUser()
    apiLeadStatusTypes=new APILeadStatusTypes();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            loaded: false,
            data:null,
            contacts:[],
            users:[],
            customerTypes:[],
            sectors:[],
            leadStatus:[]
        };
    }

    componentDidMount() {
        this.getCustomerData();
        this.api.getCustomerContacts(this.props.customerId).then(contacts=>{
            //console.log("contacts",contacts);
            this.setState({contacts})
        });
        this.apiUsers.getActiveUsers().then(users=>{
            //console.log('users',users);
            this.setState({users})
        });
        this.api.getCustomerTypes().then(customerTypes=>{
            //console.log(customerTypes);
            this.setState({customerTypes})
        })
        this.api.getCustomerSectors().then(sectors=>{
            this.setState({sectors})
        })
        this.apiLeadStatusTypes.getAllTypes().then(leadStatus=>{            
            this.setState({leadStatus})
        })
    }
    getCustomerData=()=>{
        this.api.getCustomerData(this.props.customerId).then(data=>{
            console.log("customer",data);
            this.setState({data})
        },error=>{
            this.alert("Error in get customer data");
            console.log(error);
        });
    }
    updateCustomerField = (field, value) => {
        const {customerValueUpdate} = this.props;
        customerValueUpdate(field, value);
    }

    handleFlagUpdate($event) {
        //this.updateCustomerField($event.target.name, $event.target.checked ? "Y" : "N");
        this.setValue($event.target.name, $event.target.checked ? "Y" : "N");

    }

    handleCheckboxFieldUpdate($event) {
        //this.updateCustomerField(event.target.name, event.target.checked);
        this.setValue($event.target.name, $event.target.checked  );

    }

    handleUpdateGenericField = ($event) => {
        //this.updateCustomerField($event.target.name, $event.target.value);
        this.setValue($event.target.name, $event.target.value);
    }
    
    getKeyDetailsCard=()=>{
        const { data } = this.state;
        return (
          <div className="card m-5">
            <div className="card-header">
              <h3>Key Details</h3>
            </div>
            <div className="card-body">
              <div>
                <table>
                  <tbody>
                    <tr>
                      <td className="text-align-right">
                        Customer {data.customerID}
                      </td>
                      <td>
                        <input
                          type="text"
                          onChange={($event) =>
                            this.handleUpdateGenericField($event)
                          }
                          value={data.name || ""}
                          size="50"
                          maxLength="50"
                          name="name"
                          className="form-control input-sm"
                        />
                      </td>
                    </tr>
                    <tr>
                      <td className="text-align-right">Primary Main Contact</td>
                      <td>
                        <select
                          name="primaryMainContactID"
                          className="form-control input-sm"
                          value={data.primaryMainContactID}
                          onChange={($value) =>
                            this.setValue(
                              "primaryMainContactID",
                              $value.target.value
                            )
                          }
                        >
                          {this.state.contacts
                            .filter((contact) => contact.supportLevel == "main")
                            .map((contact) => (
                              <option key={contact.id} value={contact.id}>
                                {contact.firstName + " " + contact.lastName}
                              </option>
                            ))}
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td className="text-align-right">Statement Contact</td>
                      <td>
                        <select
                          name="statementContactId"
                          className="form-control input-sm"
                          value={data.statementContactId}
                          onChange={($value) =>
                            this.setValue(
                              "statementContactId",
                              $value.target.value
                            )
                          }
                        >
                          {this.state.contacts.map((contact) => (
                            <option key={contact.id} value={contact.id}>
                              {contact.firstName + " " + contact.lastName}
                            </option>
                          ))}
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td className="text-align-right">Referred</td>
                      <td>
                        <div
                          className="flex-row "
                          style={{ justifyContent: "space-between",width:150 }}
                        >
                          <Toggle
                            checked={data.referredFlag }
                            onChange={() =>
                              this.setValue(
                                "referredFlag",
                                (!data.referredFlag*1) 
                              )
                            }
                          ></Toggle>
                          <div className="flex-row flex-center ">
                            <span className="mr-2"> 24 Hour Cover </span>
                            <Toggle
                              checked={data.support24HourFlag === "Y"}
                              onChange={() =>
                                this.setValue(
                                  "support24HourFlag",
                                  data.support24HourFlag === "Y" ? "N" : "Y"
                                )
                              }
                            ></Toggle>
                          </div>
                         
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td className="text-align-right">Special Attention</td>
                      <td>
                      <div className="flex-row  " style={{alignItems:"center"}}>
                            
                            <Toggle
                              checked={data.specialAttentionFlag === "Y"}
                              onChange={() =>
                                this.setValue(
                                  "specialAttentionFlag",
                                  data.specialAttentionFlag === "Y" ? "N" : "Y"
                                )
                              }
                            ></Toggle>
                         
                          <span className="mr-2 ml-5"> Until </span>
                        <input
                          type="date"
                          value={data.specialAttentionEndDate || ""}
                          size="10"
                          maxLength="10"                          
                          className="form-control input-sm"
                          onChange={($event) =>
                            this.handleUpdateGenericField($event)
                          }
                          name="specialAttentionEndDate"
                          style={{maxWidth:170}}
                        />
                         </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        );
    }
    getReviewMeetingCard=()=>{
        const { data } = this.state;
        return (
          <div className="card m-5">
            <div className="card-header">
              <h3>Review Meetings</h3>
            </div>
            <div className="card-body">
              <table>
                <tbody>
                  <tr>
                    <td align="right">Last Review Meeting</td>
                    <td>
                      
                      <input
                        type="date"
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        value={data.lastReviewMeetingDate || ""}
                        size="10"
                        maxLength="10"
                        className="form-control input-sm"
                        name="lastReviewMeetingDate"
                      />
                    </td>
                   
                    <td align="right">Frequency</td>
                    <td>
                      <select
                        className="form-control input-sm"
                        name="reviewMeetingFrequencyMonths"
                        value={data.reviewMeetingFrequencyMonths || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                      >
                        <option value="1">Monthly</option>
                        <option value="2">Two Monthly</option>
                        <option value="3">Quarterly</option>
                        <option value="6">Six-Monthly</option>
                        <option value="12">Annually</option>
                      </select>
                    </td>
                    <td align="right">Booked</td>
                    <td>
                      <Toggle
                        checked={data.reviewMeetingBooked}
                        onChange={() =>
                          this.setValue(
                            "reviewMeetingBooked",
                            !data.reviewMeetingBooked
                          )
                        }
                      ></Toggle>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        );
    }
    getAccountsCard=()=>{
        const { data,users,leadStatus } = this.state;
        return (
          <div className="card m-5">
            <div className="card-header">
              <h3>Accounts</h3>
            </div>
            <div className="card-body">
              <table>
                <tbody>
                <tr>
                    <td align="right">Account Manager</td>
                    <td>
                      <select
                        className="form-control input-sm"
                        name="accountManagerUserID"
                        value={data.accountManagerUserID || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                      >
                        {users.map((user) => (
                          <option key={user.id} value={user.id}>
                            {user.name}
                          </option>
                        ))}
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Became Customer</td>
                    <td>
                      <input
                        type="date"
                        value={data.becameCustomerDate || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        size="10"
                        maxLength="10"
                        className="form-control input-sm"
                        name="becameCustomerDate"
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Dropped Date</td>
                    <td>
                      <input
                        type="date"
                        value={data.droppedCustomerDate || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        size="10"
                        maxLength="10"
                        className="form-control input-sm"
                        name="droppedCustomerDate"
                      />
                    </td>
                  </tr>
                 <tr>
                     <td  align="right">Mailshot</td>
                     <td>
                             <Toggle
                              checked={data.mailshotFlag  }
                              onChange={() =>
                                this.setValue(
                                  "mailshotFlag",
                                  (!data.mailshotFlag*1)  
                                )
                              }
                            ></Toggle>
                     </td>
                 </tr>
                 <tr>
                     <td  align="right">Lead Status	</td>
                     <td>                     
                     <select className="form-control" value={data.leadStatusId} onChange={($event)=>this.setValue("leadStatusId",$event.target.value)}>
                              <option value="">None</option>
                              {
                                leadStatus.map(status=><option key={status.id} value={status.id}>{status.name}</option>)
                              }
                              
                     </select>                             
                     </td>
                 </tr>
                </tbody>
              </table>
            </div>
          </div>
        );
    }
    getSectorSizeCard=()=>{
         
        const { data, customerTypes, sectors } = this.state;
        return (
          <div className="card m-5">
            <div className="card-header">
              <h3>Sector and Size</h3>
            </div>
            <div className="card-body">
              <table>
                <tbody>
                  <tr>
                    <td align="right">Type</td>
                    <td>
                      <select
                        className="form-control input-sm"
                        value={data.customerTypeID || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        name="customerTypeID"
                      >
                        {customerTypes.map((type) => (
                          <option key={type.id} value={type.id}>
                            {type.name}
                          </option>
                        ))}
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Sector</td>
                    <td>
                      <select
                        className="form-control input-sm"
                        value={data.sectorID || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        name="sectorID"
                      >
                        {sectors.map((type) => (
                          <option key={type.id} value={type.id}>
                            {type.name}
                          </option>
                        ))}
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">PCs</td>
                    <td>
                      <input
                        type="number"
                        value={data.noOfPCs || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        className="form-control input-sm"
                        name="noOfPCs"
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Servers</td>
                    <td>
                      
                      <input
                        type="number"
                        value={data.noOfServers || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        className="form-control input-sm"
                        name="noOfServers"
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Sort Code</td>
                    <td>
                      <EncryptedTextInput
                        encryptedValue={data.sortCode}
                        onChange={this.handleUpdateGenericField}
                        mask="99-99-99"
                        name="sortCode"
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Account Name</td>
                    <td>                      
                      <EncryptedTextInput
                        className="form-control input-sm"
                        encryptedValue={data.accountName || ""}
                        name="accountName"
                        onChange={this.handleUpdateGenericField}
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Account Number</td>
                    <td>
                      <EncryptedTextInput
                        encryptedValue={data.accountNumber}
                        onChange={this.handleUpdateGenericField}
                        mask="99999999"
                        name="accountNumber"
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Reg</td>
                    <td>                      
                      <input
                        type="text"
                        value={data.regNo || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        size="10"
                        maxLength="10"
                        className="form-control input-sm"
                        name="regNo"
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Pre-pay Top Up</td>
                    <td>                      
                      <input
                        type="text"
                        value={data.gscTopUpAmount || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        size="10"
                        maxLength="10"
                        className="form-control input-sm"
                        name="gscTopUpAmount"
                      />
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Inclusive OOH Call Outs</td>
                    <td>                      
                      <input
                        type="number"
                        value={data.inclusiveOOHCallOuts || ""}
                        onChange={($event) =>
                          this.handleUpdateGenericField($event)
                        }
                        size="10"
                        maxLength="10"
                        className="form-control input-sm"
                        name="inclusiveOOHCallOuts"
                      />
                      
                    </td>
                    <td><span style={{whiteSpace:"nowrap" }}>Per Month</span>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Patch Management Eligible</td>
                    <td>          
                        {data.eligiblePatchManagement}            
                    </td>                    
                  </tr>
                  <tr>
                    <td align="right">Exclude from Webroot Checks</td>
                    <td>          
                         <Toggle checked={data.excludeFromWebrootChecks} onChange={()=>this.setValue("excludeFromWebrootChecks",(!data.excludeFromWebrootChecks*1))} ></Toggle>         
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        );
    }
    getSLAItem=(title,field)=>{
        const {data}=this.state;
        return (
          <div className="flex-row flex-center">
            <span style={{width:15,textAlign:"right"}}>{title}</span>
            <input style={{width:50}} className="form-control" value={data[field]} onChange={($event)=>this.setValue(field,$event.target.value)}></input>
          </div>
        );
    }

    getSLAItemToggle=(title,field)=>{
        const {data}=this.state;
        return (
            <div className="flex-row flex-center">
            <span style={{width:15,textAlign:"right"}}>{title}</span>
            <div  style={{width:50,marginLeft:3}} >
            <Toggle checked={data[field]} onChange={()=>this.setValue(field,!data[field])}></Toggle>
            </div>
             </div>
        );

    }

    getServiceLevelAgreementsCard=()=>{
        const { data } = this.state;
        return (
          <div className="card m-5">
            <div className="card-header">
              <h3>Service Level Agreements</h3>
            </div>
            <div className="card-body">
              <table>
                <tbody>
                  <tr>
                    <td align="right">SLA Response Hours</td>
                    <td>
                      <div className="flex-row">
                        {this.getSLAItem(1, "slaP1")}
                        {this.getSLAItem(2, "slaP2")}
                        {this.getSLAItem(3, "slaP3")}
                        {this.getSLAItem(4, "slaP4")}
                        {this.getSLAItem(5, "slaP5")}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">SLA Response Fix Hours</td>
                    <td>
                      <div className="flex-row">
                        {this.getSLAItem(1, "slaFixHoursP1")}
                        {this.getSLAItem(2, "slaFixHoursP2")}
                        {this.getSLAItem(3, "slaFixHoursP3")}
                        {this.getSLAItem(4, "slaFixHoursP4")}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">SLA Penalties Agreed</td>
                    <td>
                      <div className="flex-row">
                        {this.getSLAItemToggle(1, "slaP1PenaltiesAgreed")}
                        {this.getSLAItemToggle(2, "slaP2PenaltiesAgreed")}
                        {this.getSLAItemToggle(3, "slaP3PenaltiesAgreed")}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td align="right">Last Modified:</td>
                    <td>{data.lastUpdatedDateTime}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        );
    }
    getTechnicalNotesCard=()=>{
        const { data } = this.state;

        return <div className="card m-5">
            <div className="card-header">
              <h3>Technical Notes</h3>
            </div>
                <div className="card-body">
                    <table>
                        <tbody>
                        <tr>
                                <td align="right">Active Directory Name</td>
                                <td>
                                <input type="text"
                                       value={data.activeDirectoryName || ''}
                                       onChange={($event) => this.handleUpdateGenericField($event)}
                                       size="54"
                                       maxLength="255"
                                       className="form-control input-sm"
                                       name="activeDirectoryName"
                                />
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Technical Notes</td>
                                <td>  <textarea className="form-control input-sm"
                                              cols="30"
                                              rows="2"
                                              value={data.techNotes || ''}
                                              onChange={($event) => this.handleUpdateGenericField($event)}
                                              name="techNotes"
                                    /></td>
                            </tr>
                           
                        </tbody>
                    </table>                   
                </div>
            </div>
    }
    getCards=()=>{       
        
        return <div className="row" style={{margin:2}}>
        <div className="col-md-6">
            {this.getKeyDetailsCard()}           
            {this.getSectorSizeCard()}
        </div>
        <div className="col-md-6">
            {this.getReviewMeetingCard()}
            {this.getAccountsCard()}
            {this.getServiceLevelAgreementsCard()}
            {this.getTechnicalNotesCard()}
        </div>
    </div>
    }
    handleSave=()=>{
        const {data}=this.state;
        this.api.updateCustomer(data).then(res=>{
            if(res.status)
            {
                this.alert("Data saved successfully");
                this.getCustomerData();
            }
            else
            this.alert("Data not saved successfully");

        },error=>{
            console.log(error);
            this.alert("Data not saved successfully");
        })
        console.log(data);
    }
    render() {
        const { data } = this.state;
        if (!data) return null;
        return (
          <div>
            {this.getAlert()}
            {this.getCards()}
            <button onClick={this.handleSave} className="ml-5">Save</button>
          </div>
        );
/*
        const {
         
            customerTypes,
            sectors,
            accountManagers,
            mainContacts,
            allContacts
        } = this.props;


        if (!customer) {
            return null;
        }

        const {customerId} = customer;
        return (
            <div className="mt-3">
                <div className="row">
                    <div className="col-md-6 mb-3">
                        <h2>Customer - {data.name}
                            <a href="#">
                                <i className="fal fa-globe"/>
                            </a>
                        </h2>
                    </div>
                    <div className="col-md-6 mb-3">
                        <ul className="list-style-none float-right">
                            <li>
                                <button type="button"
                                        className="btn btn-sm btn-outline-secondary"
                                >Set all
                                    users to no support (not implemented)
                                </button>
                                <button type="button"
                                        className="btn btn-sm btn-outline-secondary"
                                >
                                    <i className="fal fa-filter"/>
                                </button>
                                <button type="button"
                                        className="btn btn-sm btn-outline-secondary"
                                >
                                    <i className="fal fa-ellipsis-v"/>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                
            </div>
            // </React.Profiler>
        )*/
    }

    isProspect() {
        return !(this.props.data.becameCustomerDate && !this.props.data.droppedCustomerDate);
    }
}
/*
function mapStateToProps(state) {
    const {customerEdit} = state;
    return {
        customer: customerEdit.customer,
        customerTypes: customerEdit.customerTypes,
        leadStatuses: customerEdit.leadStatuses,
        sectors: customerEdit.sectors,
        accountManagers: customerEdit.accountManagers,
        reviewEngineers: customerEdit.reviewEngineers,
        mainContacts: getMainContacts(state),
        allContacts : getAllContacts(state)
    }
}

function mapDispatchToProps(dispatch) {
    return {
        customerValueUpdate: (field, value) => {
            dispatch(updateCustomerField(field, value))
        }
    }
}*/

//export default connect(mapStateToProps, mapDispatchToProps)(CustomerEditMain)