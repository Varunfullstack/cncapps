import React from "react";
import {params} from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import ToolTip from "../shared/ToolTip";
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle";

import {ContactEditModalComponent} from "./ContactEdit/ContactEditModalComponent";

export default class CustomerContactsComponent extends MainComponent {
    api = new APICustomers();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            customerId: null,
            contacts: [],
            contactsFiltered: [],
            sites: [],
            letters: [],
            reset: false,
            showModal: false,
            isNew: true,
            filter: {
                showInActive: false,
                reviewUser: false,
                hrUser: false,

                mailshot8Flag: false,
                mailshot2Flag: false,
                mailshot9Flag: false
            },
            showSpinner: true,
            supportLevelOptions: [
                "main",
                "supervisor",
                "support",
                "delegate",
                "furlough",
            ],
            showPasswordModal: false,

        };
    }

    componentDidMount() {
        const customerId = params.get("customerID");
        this.getData();
        this.api.getCustomerSites(customerId, "Y").then((sites) => {
            this.setState({sites, customerId});
        });
        this.api.getLetters().then(res => {
            this.setState({letters: res.data});
        })
    }

    getData = () => {
        this.setState({showSpinner: true});
        const customerId = params.get("customerID");
        this.api.getCustomerContacts(customerId).then((contacts) => {
            this.setState({contacts, customerId, showSpinner: false}, () =>
                this.applyFilter()
            );
        });

    };

    applyFilter = () => {
        const {filter, contacts} = this.state;
        let contactsFiltered = [...contacts];
        contactsFiltered = filter.showInActive ? contactsFiltered.filter((c) => c.active == 0) : contactsFiltered.filter((c) => c.active == 1);

        if (filter.reviewUser)
            contactsFiltered = contactsFiltered.filter((c) => c.reviewUser == 'Y');

        if (filter.hrUser)
            contactsFiltered = contactsFiltered.filter((c) => c.hrUser == 'Y');

        if (filter.mailshot8Flag)
            contactsFiltered = contactsFiltered.filter((c) => c.mailshot8Flag == 'Y');

        if (filter.mailshot2Flag)
            contactsFiltered = contactsFiltered.filter((c) => c.mailshot2Flag == 'Y');

        if (filter.mailshot9Flag)
            contactsFiltered = contactsFiltered.filter((c) => c.mailshot9Flag == 'Y');

        this.setState({contactsFiltered});


    };
    getFilter = () => {
        const {filter} = this.state;
        return (
            <div className="flex-row flex-center" style={{marginTop: 5}}>
                {this.getFilterItem("Show Inactive", "showInActive")}
                {this.getFilterItem("HR User to edit contacts", "hrUser")}
                {this.getFilterItem("PrePay TopUp Notifications", "mailshot8Flag")}
                {this.getFilterItem("Receive Invoices", "mailshot2Flag")}
                {this.getFilterItem("Company Information Reports", "mailshot9Flag")}
            </div>
        );
    };
    getSiteTitle = (siteNo) => {
        const {sites} = this.state;
        const site = sites.find((s) => s.id == siteNo);
        if (site) return site.add1;
        else return "";
    };
    getTable = () => {
        const columns = [
            {
                path: "title",
                label: "Title",
                hdToolTip: "Title",
                sortable: true,
                width: 50,
            },
            {
                path: "firstName",
                label: "Name",
                hdToolTip: "Name",
                sortable: true,
                content: (contact) => contact.firstName + " " + contact.lastName,
            },

            {
                path: "siteNo",
                label: "Site",
                hdToolTip: "Site",
                sortable: true,
                content: (contact) => this.getSiteTitle(contact.siteNo),
            },
            {
                path: "position",
                label: "Position",
                hdToolTip: "Position",
                sortable: true,
            },
            {
                path: "email",
                label: "Email",
                hdToolTip: "Email",
                sortable: true,
            },
            {
                path: "phone",
                label: "Phone",
                hdToolTip: "Phone",
                icon: "pointer",
                sortable: true,
                content:contact=><a href={`tel:${contact.phone}`}>{contact.phone}</a>
            },
            {
                path: "mobilePhone",
                label: "Mobile",
                hdToolTip: "Mobile",
                icon: "pointer",
                sortable: true,
            },
            {
                path: "supportLevel",
                label: "Support Level",
                hdToolTip: "Support Level",
                icon: "pointer",
                sortable: true,

                content: (contact) => this.capitalizeFirstLetterSupportLevel(contact.supportLevel),
            },
            
            {
                path: "linkedInURL",
                label: "",
                hdToolTip: "LinkdIn",
                icon: "fab fa-linkedin-in    color-gray2  ",
                sortable: true,
                content: (contact) => <a style={{display: contact.linkedInURL ? "block" : "none"}}
                                         href={contact.linkedInURL} target="_blank"><i
                    className="fab fa-linkedin-in pointer icon"/></a>,
            },
            {
                path: "portalPassword",
                //label: "",
                hdToolTip: "Portal Password",
                icon: "fal fa-2x  fa-user-secret color-gray2  ",
                sortable: true,
                content: (contact) => this.getPassword(contact)
            },

            {
                path: "edit",
                label: "",
                hdToolTip: "Edit contact",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,
                content: (contact) =>
                    this.getEditElement(contact, () => this.handleEdit(contact)),
            },
            {
                path: "delete",
                label: "",
                hdToolTip: "Delete contact",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,
                content: (contact) =>
                    this.getDeleteElement(
                        contact,
                        () => this.handleDelete(contact),
                        contact.isDeletable
                    ),
            },
        ];
        return (
            <Table
                key="contacts"
                pk="id"
                columns={columns}
                data={this.state.contactsFiltered || []}
                search={true}
            />
        );
    };

    handlePassword = (contact) => {
        this.setState({data: {...contact}, showPasswordModal: true})
    }

    getPassword = (contact) => {
        if (contact.portalPassword)
            return (
                <ToolTip title="Password set">
                    <i className="fas fa-key pointer" onClick={() => this.handlePassword(contact)}> </i>
                </ToolTip>
            );
        else
            return (
                <ToolTip title="Password not set">
                    <i className="fal fa-key pointer" onClick={() => this.handlePassword(contact)}> </i>
                </ToolTip>
            );
    }

    getPasswordModal = () => {
        const {data} = this.state;
        if (!data)
            return null;
        return <Modal
            width={400}
            show={this.state.showPasswordModal}
            title={`Set ${data.firstName + " " + data.lastName} Portal Password`}
            onClose={() => this.setState({showPasswordModal: false})}
            content={
                <div>
                    <div className="form-group">
                        <label>Password</label>
                        <input onChange={($event) => this.setValue("portalPassword", $event.target.value)}
                               className="form-control"/>
                    </div>

                </div>
            }
            footer={<div key="passwordActions">
                <button onClick={this.handlePasswordSave}>Save</button>
                <button onClick={() => this.setState({showPasswordModal: false})}>Cancel</button>
            </div>}
        >

        </Modal>
    }
    handlePasswordSave = () => {
        const {data} = this.state;
        if (!data.portalPassword) {
            this.alert("Please enter password");
            return;
        }
        this.api.setContactPassword(data.id, data.portalPassword).then(res => {

            if (res.status == "error") {
                this.alert(res.error)
            } else {
                this.setState({showPasswordModal: false});
                this.getData();
            }
        }, error => {

        })
    }

    capitalizeFirstLetterSupportLevel(string) {
        if (!string) {
            return 'None';
        }
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    newContact() {
        return {
            id: "",
            customerID: params.get("customerID"),
            title: "",
            position: "",
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            mobilePhone: "",
            fax: "",
            portalPassword: "",
            mailshot: "",
            mailshot2Flag: "",
            mailshot3Flag: "",
            mailshot8Flag: "",
            mailshot9Flag: "",
            mailshot11Flag: "",
            notes: "",
            failedLoginCount: "",
            reviewUser: "",
            hrUser: "",
            supportLevel: "",
            initialLoggingEmail: "",
            othersInitialLoggingEmailFlag: "",
            othersWorkUpdatesEmailFlag: "",
            othersFixedEmailFlag: "",
            pendingLeaverFlag: "",
            pendingLeaverDate: "",
            specialAttentionContactFlag: "",
            linkedInURL: "",
            pendingFurloughAction: "",
            pendingFurloughActionDate: "",
            pendingFurloughActionLevel: "",
            siteNo: "",
            active: 1,
        };
    }

    handleEdit = (contact) => {
        this.setState({contact: {...contact}, showModal: true, isNew: false});
    };

    handleDelete = async (contact) => {
        if (await this.confirm("Are you sure you want to delete this contact?")) {
            this.api.deleteCustomerContact(contact.id).then((res) => {
                this.getData();
            });
        }
    };

    handleNewItem = () => {
        this.setState({
            showModal: true,
            isNew: true,
            contact: {...this.newContact()},
        });
    };

    getCheckBox = (name, yesNo = true) => {
        const {data} = this.state;
        let trueValue = "Y";
        let falseValue = "N";
        if (!yesNo) {
            trueValue = 1;
            falseValue = 0;
        }
        return (
            <input
                checked={data[name] == trueValue}
                onChange={() =>
                    this.setValue(name, data[name] == trueValue ? falseValue : trueValue)
                }
                type="checkbox"
            />
        );
    };

    handleClose = () => {
        this.setState({showModal: false});
    };

    handleSave = (contactData) => {
        const {isNew} = this.state;
        if (!this.isFormValid("contactformdata")) {
            this.alert("Please enter required data");
            return;
        }
        if (!isNew) {
            this.api.updateCustomerContact(contactData).then((res) => {
                if (res.status == 200) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        } else {
            contactData.id = null;
            this.api.addCustomerContact(contactData).then((res) => {
                if (res.status == 200) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        }
    };

    getModal = () => {
        const {isNew, showModal, contact} = this.state;
        if (!showModal) return null;
        return (
            <ContactEditModalComponent
                width={800}
                title={isNew ? "Create Contact" : "Update Contact"}
                show={showModal}
                customerId={params.get("customerID")}
                contact={contact}
                onSave={this.handleSave}
                onClose={this.handleClose}
            />
        );
    };


    getFilterItem = (label, name) => {
        const {filter} = this.state;
        return <div>
            <label className="mr-3 ml-5">{label}</label>
            <Toggle
                checked={filter[name]}
                onChange={() =>
                    this.handleFilterChange(name, !filter[name])
                }
            />
        </div>
    }

    handleFilterChange = (prop, value) => {
        this.setFilter(prop, value, () => this.applyFilter());
    };
    calcSummary = () => {
        const {contacts} = this.state;
        return contacts.reduce((summary, contact) => {
            if (!contact.active) {
                return summary;
            }
            if (!contact.supportLevel) {
                summary.noLevel++;
            } else {
                summary[contact.supportLevel]++;
            }
            summary.total++;
            return summary;

        }, {
            main: 0,
            supervisor: 0,
            support: 0,
            delegate: 0,
            furlough: 0,
            noLevel: 0,
            total: 0
        })
    }
    getSummaryElement = () => {
        const summary = this.calcSummary();
        return <table className="table" style={{maxWidth: 500}}>
            <thead>
            <tr>
                <th>Main</th>
                <th>Supervisor</th>
                <th>Support</th>
                <th>Delegate</th>
                <th>Furlough</th>
                <th>None</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td className="text-center">{summary.main}</td>
                <td className="text-center">{summary.supervisor}</td>
                <td className="text-center">{summary.support}</td>
                <td className="text-center">{summary.delegate}</td>
                <td className="text-center">{summary.furlough}</td>
                <td className="text-center">{summary.noLevel}</td>
                <td className="text-center">{summary.total}</td>
            </tr>
            </tbody>
        </table>
    }
    handleClearSupportLevel = async () => {
        if (await this.confirm("This is will change all contacts to the support level of None & refer the customer. Please confirm that you want to continue.")) {
            this.api.removeSupportAndRefer(this.state.customerId).then(res => {
                this.getData();
            })
        }
    }

    render() {
        if (this.state.showSpinner)
            return <Spinner show={this.state.showSpinner}/>;
        return (
            <div>
                {this.getFilter()}
                <div className="flex-row m-5">
                    <button onClick={this.handleClearSupportLevel}>Clear Support Level</button>
                    <ToolTip title="New Item" width={30}>
                        <i
                            className="fal fa-2x fa-plus color-gray1 pointer"
                            onClick={this.handleNewItem}
                        />
                    </ToolTip>
                </div>
                {this.getConfirm()}
                {this.getAlert()}
                {this.getPasswordModal()}
                {this.getSummaryElement()}
                {this.getTable()}
                <div className="modal-style">{this.getModal()}</div>
            </div>
        );
    }
}
