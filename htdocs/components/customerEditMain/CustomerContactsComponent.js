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
            sites:[],
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
        this.api.getCustomerContacts(customerId).then(sites=>{
            this.setState({sites,customerId});
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
                content:(site)=> this.capitalizeFirstLetter(site.supportLevel)
             },
             {
                path: "edit",
                label: "",
                hdToolTip: "Edit site",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,                              
                content:(site)=>this.getEditElement(site,()=>this.handleEdit(site))
             },
             {
                path: "delete",
                label: "",
                hdToolTip: "Delete site",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,                              
                content:(site)=>this.getDeleteElement(site,()=>this.handleDelete(site),site.isDeletable)
             },
        ]
        return <Table           
                     
        key="sites"  
        pk="id"
        style={{maxWidth:1300}}
        columns={columns}
        data={this.state.sites||[]}
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
        if (!data.description) {
            this.alert("Please enter description");
            return;
        }
        if (!isNew) {
            this.api.updateCustomerContact(data).then((res) => {
                if (res.state) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        } else {
            data.id = null;
            this.api.addCustomerContact(data).then((res) => {
                if (res.state) {
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
                    <td className="text-right">First</td>
                    <td><input required
                               value={data.firstName}
                               onChange={(event) => this.setValue("firstName", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Last</td>
                    <td><input required
                               value={data.lastName}
                               onChange={(event) => this.setValue("lastName", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                    <td className="text-right">Position</td>
                    <td><input required
                               value={data.position}
                               onChange={(event) => this.setValue("position", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Email</td>
                    <td><input required
                               value={data.email}
                               onChange={(event) => this.setValue("email", event.target.value)}
                               className="form-control"
                    /></td>
                </tr>
                <tr>
                    <td className="text-right">Phone</td>
                    <td><input required
                               value={data.phone}
                               onChange={(event) => this.setValue("phone", event.target.value)}
                               className="form-control"
                    /></td>
                     <td className="text-right">Mobile</td>
                    <td><input required
                               value={data.mobilePhone}
                               onChange={(event) => this.setValue("mobilePhone", event.target.value)}
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
