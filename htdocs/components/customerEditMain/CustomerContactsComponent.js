import React from 'react';
import { params} from "../utils/utils";
import APICustomers from '../services/APICustomers';
import MainComponent from '../shared/MainComponent';
import Table from '../shared/table/table';
import ToolTip from '../shared/ToolTip';
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from '../shared/Toggle';


export default class CustomerContactsComponent extends MainComponent {
    api =new APICustomers();
    constructor(props) {
        super(props);
        this.state={
            ...this.state,
            customerId:null,
            contacts:[],
            reset: false,
            showModal: false,
            isNew: true,
            data: {...this.getInitData()},
        }
    }
    componentDidMount() {
        this.getData();
    }
    getData=()=>{
        const customerId=params.get("customerID");
        this.api.getCustomerContacts(customerId).then(contacts=>{
            this.setState({contacts,customerId});
        })
    }
    getTable=()=>{
        const columns=[
            {
               path: "firstName",
               label: "First",
               hdToolTip: "First",               
               sortable: true,         
               width:200                     
            },
            {
                path: "lastName",
                label: "Last",
                hdToolTip: "Last",               
                sortable: true,         
                width:150                     
             },
             {
                path: "position",
                label: "Position",
                hdToolTip: "Position",               
                sortable: true,         
                width:150                     
             },
             {
                path: "email",
                label: "Email",
                hdToolTip: "Email",               
                sortable: true,         
                width:150                     
             },
             {
                path: "phone",
                label: "Phone",
                hdToolTip: "Phone",               
                icon: "pointer",
                sortable: true,      
                width:150,
             },
             {
                path: "mobilePhone",
                label: "Mobile",
                hdToolTip: "Mobile",               
                icon: "pointer",
                sortable: true,      
                width:150,
             },
             {
                path: "supportLevel",
                label: "Support Level",
                hdToolTip: "Support Level",               
                icon: "pointer",
                sortable: true,      
                width:150,
                content:(contact)=> this.capitalizeFirstLetter(contact.supportLevel)
             },
             {
                path: "edit",
                label: "",
                hdToolTip: "Edit contact",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,                              
                content:(contact)=>this.getEditElement(contact,()=>this.handleEdit(contact))
             },
             {
                path: "delete",
                label: "",
                hdToolTip: "Delete contact",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,                              
                content:(contact)=>this.getDeleteElement(contact,()=>this.handleDelete(contact),contact.isDeletable)
             },
        ]
        return <Table           
                     
        key="contacts"  
        pk="id"
        style={{maxWidth:1300}}
        columns={columns}
        data={this.state.contacts||[]}
        search={true}
        >
        </Table>
    }

    capitalizeFirstLetter(string) {
        if(string != null) {
            return string.charAt(0).toUpperCase() + string.slice(1); 
        }
        return '';
    }

    getInitData() {
        return {
            id: '',
            customerID: '',
            title: '',
            position: '',
            firstName: '',
            lastName: '',
            email: '',
            phone: '',
            mobilePhone: '',
            fax: '',
            portalPassword: '',
            mailshot: '',
            mailshot2Flag: '',
            mailshot3Flag: '',
            mailshot8Flag: '',
            mailshot9Flag: '',
            mailshot11Flag: '',
            notes: '',
            failedLoginCount: '',
            reviewUser: '',
            hrUser: '',
            supportLevel: '',
            initialLoggingEmail: '',
            othersInitialLoggingEmailFlag: '',
            othersWorkUpdatesEmailFlag: '',
            othersFixedEmailFlag: '',
            pendingLeaverFlag: '',
            pendingLeaverDate: '',
            specialAttentionContactFlag: '',
            linkedInURL: '',
            pendingFurloughAction: '',
            pendingFurloughActionDate: '',
            pendingFurloughActionLevel: '',
            siteNo: '',
            active: '',
        };
    }

    handleEdit=(contact)=>{
        //contact['customerID'] = params.get("customerID");
        console.log("Edit Contact",contact);
        this.setState({data: contact, showModal: true, isNew: false});
    }

    handleDelete=async (contact)=>{
        console.log("Delete contact",contact);
        if(await this.confirm("Are you sure you want to delete this contact?")){
            this.api.deleteCustomerContact(contact.id).then(res=>{
                console.log(res);
                this.getData();
            })
        }
    }

    handleNewItem = () => {
        this.setState({showModal: true, isNew: true, data: {...this.getInitData()}});
    }

    getCheckBox = (name, yesNo = true) => {
        const {data} = this.state;
        let trueValue = 'Y';
        let falseValue = 'N';
        if (!yesNo) {
            trueValue = 1;
            falseValue = 0;
        }
        return <input checked={data[name] == trueValue}
                      onChange={() => this.setValue(name, data[name] == trueValue ? falseValue : trueValue)}
                      type="checkbox"
        />
    }
  
    handleClose = () => {
        this.setState({showModal: false});
    }

    handleSave = () => {
        const {data, isNew} = this.state;
        console.log(data)
        if (!data.firstName) {
            this.alert("Please enter firstname");
            return;
        }
        if (!isNew) {
            this.api.updateCustomerContact(data).then((res) => {
                if (res.status == 200) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        } else {
            data.id = null;
            this.api.addCustomerContact(data).then((res) => {
                if (res.status == 200) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        }
    }

    getModal = () => {
        const {isNew, showModal} = this.state;
        if (!showModal)
            return null;
        return <Modal
            width={800}
            title={isNew ? "Create Contact" : "Update Contact"}
            show={showModal}
            content={this.getModalContent()}
            footer={<div key="footer">
                <button onClick={this.handleClose}
                        className="btn btn-secodary"
                >Cancel
                </button>
                <button onClick={this.handleSave}>Save</button>
            </div>}
            onClose={this.handleClose}
        >

        </Modal>
    }

    getModalContent = () => {
        const {data} = this.state;
        return <div key="content">
            <table className="table">
                <tbody>
                <tr>
                    <td className="text-right">Site</td>
                    <td><input required
                               value={data.siteNo||""}
                               onChange={(event) => this.setValue("siteNo", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Title</td>
                     <td><input required
                               value={data.title||""}
                               onChange={(event) => this.setValue("title", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                    <td className="text-right">First</td>
                    <td><input required
                               value={data.firstName||""}
                               onChange={(event) => this.setValue("firstName", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Last</td>
                    <td><input required
                               value={data.lastName||""}
                               onChange={(event) => this.setValue("lastName", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                    <td className="text-right">Position</td>
                    <td><input
                               value={data.position||""}
                               onChange={(event) => this.setValue("position", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Email</td>
                    <td><input required
                               value={data.email||""}
                               onChange={(event) => this.setValue("email", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                    <td className="text-right">Phone</td>
                    <td><input
                               value={data.phone||""}
                               onChange={(event) => this.setValue("phone", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Mobile</td>
                    <td><input
                               value={data.mobilePhone||""}
                               onChange={(event) => this.setValue("mobilePhone", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                    <td className="text-right">Failed Logins</td>
                    <td><input
                               value={data.failedLoginCount||""}
                               onChange={(event) => this.setValue("failedLoginCount", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Notes</td>
                    <td><input
                               value={data.notes||""}
                               onChange={(event) => this.setValue("notes", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                    <td className="text-right">Linkedin</td>
                    <td><input
                               value={data.linkedInURL||""}
                               onChange={(event) => this.setValue("linkedInURL", event.target.value)}
                               className="form-control"
                    /></td>
                    <td className="text-right"> Pending Leaver Date</td>
                        <td><input type="date"
                               value={data.pendingLeaverDate||""}
                               onChange={(event) => this.setValue("pendingLeaverDate", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                <td className="text-right">Active?</td>
                        <td >
                        <Toggle
                                checked={data.activeFlag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "activeFlag",
                                    data.activeFlag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        <td className="text-right">Initial Logging?</td>
                        <td >
                        <Toggle
                                checked={data.initialLoggingEmail === 1}
                                onChange={() =>
                                    this.setValue(
                                    "initialLoggingEmail",
                                    data.initialLoggingEmail === 1 ? 0 : 1
                                    )
                                }
                                ></Toggle>
                        </td>
                </tr>
                <tr>
                    <td className="text-right">Others Initial Logging</td>
                    <td >
                        <Toggle
                                checked={data.othersInitialLoggingEmailFlag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "othersInitialLoggingEmailFlag",
                                    data.othersInitialLoggingEmailFlag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                    </td>
                    <td className="text-right"> Others Fixed?</td>
                    <td >
                        <Toggle
                                checked={data.othersFixedEmailFlag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "othersFixedEmailFlag",
                                    data.othersFixedEmailFlag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        </tr>
                        <tr>
                        <td className="text-right">Inv</td>
                        <td >
                        <Toggle
                                checked={data.mailshot2Flag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "mailshot2Flag",
                                    data.mailshot2Flag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        <td className="text-right"> News</td>
                        <td >
                        <Toggle
                                checked={data.mailshot3Flag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "mailshot3Flag",
                                    data.mailshot3Flag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        </tr>
                        <tr>
                        <td className="text-right"></td>
                        <td ></td>
                        <td className="text-right"> HR</td>
                        <td >
                        <Toggle
                                checked={data.hrUser === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "hrUser",
                                    data.hrUser === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        </tr>
                        <tr>
                        <td className="text-right">Review</td>
                        <td >
                        <Toggle
                                checked={data.reviewUser === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "reviewUser",
                                    data.reviewUser === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        <td className="text-right"> Top</td>
                        <td >
                        <Toggle
                                checked={data.mailshot8Flag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "mailshot8Flag",
                                    data.mailshot8Flag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        </tr>
                        <tr>
                        <td className="text-right">SR Rep</td>
                        <td >
                        <Toggle
                                checked={data.mailshot11Flag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "mailshot11Flag",
                                    data.mailshot11Flag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        <td className="text-right"> Rep</td>
                        <td >
                        <Toggle
                                checked={data.mailshot9Flag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "mailshot9Flag",
                                    data.mailshot9Flag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        </tr>
                        <tr>
                        <td className="text-right">Pending Leaver</td>
                        <td >
                        <Toggle
                                checked={data.pendingLeaverFlag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "pendingLeaverFlag",
                                    data.pendingLeaverFlag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        <td className="text-right">Special Attention</td>
                        <td >
                        <Toggle
                                checked={data.specialAttentionContactFlag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                    "specialAttentionContactFlag",
                                    data.specialAttentionContactFlag === "Y" ? "N" : "Y"
                                    )
                                }
                                ></Toggle>
                        </td>
                        </tr>
                        <tr>
                        
                        <td className="text-right"></td>
                        <td ></td>
                        <td ></td>
                        <td ></td>
                        </tr>
                </tbody>
            </table>
        </div>
    }

    render() {
        return <div>
<Spinner show={this.state.showSpinner}/>
            <ToolTip title="New Item"
                     width={30}
            >
                <i className="fal fa-2x fa-plus color-gray1 pointer"
                   onClick={this.handleNewItem}
                />
            </ToolTip>
            {this.getConfirm()}
            {this.getTable()}
            <div className="modal-style">
                {this.getModal()}
            </div>
        </div>      
    }
}
