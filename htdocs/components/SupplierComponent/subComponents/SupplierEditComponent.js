import React from 'react';
import {params} from "../../utils/utils";
import '../../style.css';
import Table from "../../shared/table/table";
import Modal from "../../shared/Modal/modal";
import {SupplierService} from "../../services/SupplierService";

import './SupplierEditComponent.css';

const EmptyEditingContact = {
    title: '',
    position: '',
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    id: '',
    active: true
};

export const VisibilityFilterOptions = {
    SHOW_ALL: 'SHOW_ALL',
    SHOW_ACTIVE: 'SHOW_ACTIVE'
}

export class SupplierEditComponent extends React.PureComponent {


    constructor(props, context) {
        super(props, context);
        const supplierId = params.get('supplierId');
        this.state = {
            supplierId,
            supplier: null,
            activeTab: SupplierEditTabs.MAIN_SUPPLIER_TAB,
            paymentMethods: [],
            visibilityFilter: VisibilityFilterOptions.SHOW_ACTIVE,
            showContactEditModal: false,
            editingContact: EmptyEditingContact
        }
    }


    updateField = ($event) => {
        const value = $event.target.value;
        const property = $event.target.name;
        this.setState({supplier: {...this.state.supplier, [property]: value}});
    };
    saveSupplier = async () => {
        const response = await fetch(
            '/Supplier.php?action=updateSupplier',
            {
                method: 'POST',
                body: JSON.stringify(this.state.supplier)
            }
        );
        const jsonResponse = await response.json();
        if (!jsonResponse || jsonResponse.status !== 'ok') {
            alert(`Could not save supplier: ${jsonResponse?.message}`);
            return;
        }
        const supplierData = await this.fetchSupplierData(this.state.supplier.id);
        this.setState({supplier: supplierData});
    };
    archiveOrReactivateSupplier = () => {
        const {supplier} = this.state;

        return Promise.resolve()
            .then(() => {
                if (supplier.isActive) {
                    return this.archiveSupplier(supplier.id);
                }
                return this.reactivateSupplier(supplier.id);
            })
            .then(() => {
                return this.fetchSupplierData(supplier.id);
            })
            .then(supplierData => {
                this.setState({supplier: supplierData});
            })
    };


    async componentDidMount() {
        try {
            const [supplierData, paymentMethods] = await Promise.all([this.fetchSupplierData(this.state.supplierId), this.fetchPaymentMethods()]);
            this.setState({supplier: supplierData, paymentMethods});
        } catch (error) {
            alert(error);
        }
    }

    async fetchSupplierData(supplierId) {
        return SupplierService.getSupplierById(supplierId);
    }

    async fetchPaymentMethods() {
        const response = await fetch(`/Supplier.php?action=getPaymentMethods`);
        const jsonResponse = await response.json();
        if (!jsonResponse || jsonResponse.status !== 'ok') {
            throw new Error('Failed to retrieve Supplier: ' + jsonResponse.message);
        }
        return jsonResponse.data;
    }

    setActiveTab = (tabToActivate) => {
        return () => {
            this.setState({activeTab: tabToActivate})
        }
    }

    render() {

        const {supplier, activeTab} = this.state;

        if (!supplier) {
            return '';
        }


        return (
            <React.Fragment>

                <div
                    key="tab"
                    className="tab-container"
                >
                    <i
                        key={SupplierEditTabs.MAIN_SUPPLIER_TAB}
                        className={`nowrap ${activeTab === SupplierEditTabs.MAIN_SUPPLIER_TAB ? 'active' : ''}`}
                        onClick={this.setActiveTab(SupplierEditTabs.MAIN_SUPPLIER_TAB)}
                    >
                        Supplier
                    </i>
                    <i
                        key={SupplierEditTabs.CONTACTS_TAB}
                        className={`nowrap ${activeTab === SupplierEditTabs.CONTACTS_TAB ? 'active' : ''}`}
                        onClick={this.setActiveTab(SupplierEditTabs.CONTACTS_TAB)}
                    >
                        Contacts
                    </i>
                </div>
                <div className="tab-content">
                    {activeTab === SupplierEditTabs.MAIN_SUPPLIER_TAB ? this.getSupplierMainTab() : this.getSupplierContactsTab()}
                </div>
            </React.Fragment>
        );
    }

    getSupplierMainTab() {
        const {supplier, paymentMethods} = this.state;
        const activeContacts = supplier.contacts.filter(x => x.active).sort((a, b) => {
            return `${a.firstName} ${a.lastName}`.localeCompare(`${b.firstName} ${b.lastName}`);
        });
        return (
            <React.Fragment>
                <div style={{width: "500px"}}
                     className="supplier_edit_form"
                >
                    <label htmlFor="mainSupplierContactId">
                        Supplier Main Contact
                    </label>
                    <select name="mainSupplierContactId"
                            onChange={this.updateField}
                            required
                            disabled={!supplier.isActive}
                            value={supplier.mainSupplierContactId}
                    >
                        {
                            activeContacts.map(c => <option key={c.id}
                                                            value={c.id}
                            >{`${c.firstName} ${c.lastName}`}</option>)
                        }
                    </select>
                    <label htmlFor="name">
                        Name
                    </label>
                    <input
                        type="text"
                        name="name"
                        value={supplier.name}
                        onChange={this.updateField}
                        maxLength="35"
                        required
                        readOnly={!supplier.isActive}
                    />
                    <label htmlFor="address1">
                        Address1
                    </label>
                    <input
                        type="text"
                        name="address1"
                        value={supplier.address1}
                        onChange={this.updateField}
                        maxLength="35"
                        required
                        readOnly={!supplier.isActive}
                    />
                    <label htmlFor="address2">
                        Address2
                    </label>
                    <input
                        type="text"
                        name="address2"
                        value={supplier.address2 || ''}
                        onChange={this.updateField}
                        maxLength="35"
                        readOnly={!supplier.isActive}
                    />
                    <label htmlFor="town">
                        Town
                    </label>
                    <input
                        type="text"
                        value={supplier.town}
                        name="town"
                        onChange={this.updateField}
                        maxLength="25"
                        required
                        readOnly={!supplier.isActive}
                    />
                    <label htmlFor="county">
                        County
                    </label>
                    <input
                        type="text"
                        name="county"
                        value={supplier.county}
                        onChange={this.updateField}
                        maxLength="25"
                        required
                        readOnly={!supplier.isActive}
                    />
                    <label htmlFor="postcode">
                        Postcode
                    </label>
                    <input
                        type="text"
                        name="postcode"
                        value={supplier.postcode}
                        onChange={this.updateField}
                        maxLength="25"
                        required
                        readOnly={!supplier.isActive}
                    />
                    <label htmlFor="websiteURL">
                        Website URL
                    </label>
                    <input
                        type="text"
                        name="websiteURL"
                        value={supplier.websiteURL || ''}
                        onChange={this.updateField}
                        maxLength="100"
                        readOnly={!supplier.isActive}
                    />
                    <label htmlFor="paymentMethodId">
                        Payment Method
                    </label>
                    <select
                        name="paymentMethodId"
                        value={supplier.paymentMethodId}
                        onChange={this.updateField}
                        required
                        disabled={!supplier.isActive}
                    >
                        {paymentMethods.map(x => <option key={x.id}
                                                         value={x.id}
                        >{x.description}</option>)}
                    </select>
                    <label htmlFor="accountCode">
                        Account Code
                    </label>
                    <input
                        type="text"
                        name="accountCode"
                        value={supplier.accountCode}
                        onChange={this.updateField}
                        maxLength="20"
                        readOnly={!supplier.isActive}
                    />

                </div>
                <button onClick={this.saveSupplier}
                        disabled={!supplier.isActive}
                >Save
                </button>
                {
                    supplier.id ?
                        <button onClick={this.archiveOrReactivateSupplier}>
                            {
                                supplier.isActive ? 'Archive' : 'Reactivate'
                            }
                        </button>
                        : ''
                }
            </React.Fragment>
        )

    }

    static addToolTip(element, title) {
        return (
            <div className="tooltip">
                {element}
                <div className="tooltiptext tooltip-bottom">
                    {title}
                </div>
            </div>)
    }

    getSupplierContactsTab() {

        const {supplier, visibilityFilter} = this.state;


        const contacts = supplier.contacts ?? [];
        let columns = [
            {
                hide: false,
                order: 2,
                path: "firstName",
                key: "firstName",
                hdToolTip: "Name",
                icon: "fal fa-2x  fa-id-card-alt",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (contactRow) => {
                    return `${contactRow.title ? `${contactRow.title}. ` : ''}${(contactRow.firstName + " " + contactRow.lastName) ?? ""}${contactRow.position ? ` (${contactRow.position})` : ''}`
                }

            },
            {
                hide: false,
                order: 3,
                path: "phone",
                key: "phone",
                hdToolTip: "Phone",
                icon: "fal fa-2x  fa-phone",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (contactRow) => {
                    if (!contactRow.phone) {
                        return '';
                    }
                    return <a href={`tel:${contactRow.phone}`}>{contactRow.phone}</a>
                }

            },
            {
                hide: false,
                order: 4,
                path: "email",
                key: "email",
                hdToolTip: "Email",
                icon: "fal fa-2x fa-at",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: contactRow => {
                    if (!contactRow.email) {
                        return '';
                    }
                    return <a href={`mailto:${contactRow.email}`}>{contactRow.email}</a>
                }
            },
            {
                hide: visibilityFilter === VisibilityFilterOptions.SHOW_ACTIVE,
                order: 10,
                path: "active",
                key: "active",
                hdToolTip: "Active",
                icon: "fal fa-2x fa-eye",
                sortable: true,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (contactRow) => {
                    let icon = "fa-times"
                    if (contactRow.active) {
                        icon = "fa-check";
                    }
                    return (
                        <i className={`fal ${icon} fa-2x color-gray`}/>
                    )
                }

            },
            {
                hide: false,
                order: 20,
                path: "id",
                key: "id",
                icon: "fal fa-2x fa-edit",
                sortable: false,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (contactRow) => (<i onClick={this.editContactRowFunction(contactRow)}
                                             className="fal fa-edit fa-2x color-gray pointer"
                />)
            },
        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));

        return (
            <React.Fragment>
                {this.getContactEditModal()}
                <div>
                    <select onChange={this.onToggleVisibility}
                            value={visibilityFilter}
                    >
                        <option value={VisibilityFilterOptions.SHOW_ACTIVE}>Active Only</option>
                        <option value={VisibilityFilterOptions.SHOW_ALL}>Show All</option>
                    </select>
                </div>
                <i className="fal fa-plus fa-2x pointer"
                   onClick={this.addNewContact}
                />
                <Table
                    data={contacts.filter(x => !(visibilityFilter === VisibilityFilterOptions.SHOW_ACTIVE && !x.active))}
                    columns={columns}
                    pk="id"
                    search={true}
                />
            </React.Fragment>
        )
            ;
    }

    archiveContact = async (contact) => {
        const {supplier} = this.state;
        await SupplierService.archiveContact(contact, supplier);
        this.hideContactEditModal();
        const supplierData = await this.fetchSupplierData(this.state.supplier.id);
        this.setState({supplier: supplierData});
    }

    reactivateContact = async (contact) => {
        const {supplier} = this.state;
        await SupplierService.reactivateContact(contact, supplier);
        this.hideContactEditModal();
        const supplierData = await this.fetchSupplierData(this.state.supplier.id);
        this.setState({supplier: supplierData});
    }

    addNewContact = () => {
        this.setState({showContactEditModal: true, editingContact: EmptyEditingContact});
    }
    editingContactChangedField = ($event) => {
        const {editingContact} = this.state;
        const {target} = $event;
        const updatedEditingContact = {...editingContact, [target.name]: target.value};

        this.setState({editingContact: updatedEditingContact});
        this.handleValidation(updatedEditingContact, target);
    }

    handleValidation = (updatedEditingContact, htmlElement) => {
        let isValid = true;
        if (!updatedEditingContact.position || !updatedEditingContact.firstName || !updatedEditingContact.lastName || !updatedEditingContact.phone || !updatedEditingContact.email || (htmlElement && !htmlElement.checkValidity())) {
            isValid = false;
        }
        this.setState({isEditingContactValid: isValid});
    }

    hideContactEditModal = () => {
        this.setState({showContactEditModal: false, editingContact: EmptyEditingContact, isEditingContactValid: false});
    }

    getContactEditModal = () => {
        const {editingContact, showContactEditModal, isEditingContactValid, supplier} = this.state;

        return (
            <Modal show={showContactEditModal}
                   title={`Contact Edit ${!editingContact.active ? ' (Readonly Archived)' : ''}`}
                   onClose={this.hideContactEditModal}
            >
                <div className="contact_edit_form">
                    <label htmlFor="title">
                        Title
                    </label>
                    <input name="title"
                           value={editingContact.title}
                           maxLength="45"
                           onChange={this.editingContactChangedField}
                           readOnly={!editingContact.active}
                    />
                    <label>
                        First Name*
                    </label>
                    <input name="firstName"
                           value={editingContact.firstName}
                           maxLength="25"
                           required
                           readOnly={!editingContact.active}
                           onChange={this.editingContactChangedField}
                    />
                    <label>
                        <span>
                            Last Name*
                        </span>
                        <input name="lastName"
                               value={editingContact.lastName}
                               maxLength="35"
                               required
                               readOnly={!editingContact.active}
                               onChange={this.editingContactChangedField}
                        />
                    </label>
                    <label>
                        <span>
                            Position*
                        </span>
                        <input name="position"
                               value={editingContact.position || ''}
                               maxLength="50"
                               required
                               readOnly={!editingContact.active}
                               onChange={this.editingContactChangedField}
                        />
                    </label>
                    <label>
                        <span>
                            Phone*
                        </span>
                        <input name="phone"
                               value={editingContact.phone}
                               maxLength="25"
                               required
                               readOnly={!editingContact.active}
                               onChange={this.editingContactChangedField}
                        />
                    </label>
                    <label>
                        <span>
                            Email*
                        </span>
                        <input name="email"
                               value={editingContact.email}
                               maxLength="60"
                               required
                               type="email"
                               readOnly={!editingContact.active}
                               onChange={this.editingContactChangedField}
                        />
                    </label>
                </div>
                <button disabled={!isEditingContactValid}
                        onClick={this.saveContact}
                >Save
                </button>
                <button onClick={this.hideContactEditModal}>Cancel</button>
                {this.renderArchiveButtons(editingContact, supplier)}
            </Modal>
        )
    }

    renderArchiveButtons = (editingContact, supplier) => {
        if (!editingContact.id || editingContact.id === supplier.mainSupplierContactId) {
            return '';
        }

        let fn = () => this.archiveContact(editingContact);
        let text = "Archive";
        if (!editingContact.active) {
            fn = () => this.reactivateContact(editingContact);
            text = 'Reactivate';
        }
        return (<button onClick={fn}>{text}</button>);
    }

    saveContact = async () => {
        const {editingContact, supplier} = this.state;

        let promise = SupplierService.createSupplierContact;
        if (editingContact.id) {
            promise = SupplierService.updateSupplierContact
        }
        await promise(supplier, editingContact);
        const supplierData = await this.fetchSupplierData(supplier.id);
        this.setState({supplier: supplierData});
        this.hideContactEditModal();
    }

    editContactRowFunction = (contactRow) => {

        return () => {
            this.setState({
                editingContact: {...contactRow},
                showContactEditModal: true
            });
            this.handleValidation(contactRow)
        }
    }

    onToggleVisibility = () => {
        let visibilityFilterOption = VisibilityFilterOptions.SHOW_ALL;
        if (this.state.visibilityFilter === VisibilityFilterOptions.SHOW_ALL) {
            visibilityFilterOption = VisibilityFilterOptions.SHOW_ACTIVE;
        }
        this.setState({visibilityFilter: visibilityFilterOption});
    }

    archiveSupplier(id) {
        return fetch(`/Supplier.php?action=archiveSupplier`,
            {
                method: 'POST',
                body: JSON.stringify({id})
            }
        )
    }

    reactivateSupplier(id) {
        return fetch(`/Supplier.php?action=reactivateSupplier`,
            {
                method: 'POST',
                body: JSON.stringify({id})
            }
        )
    }
}

export const SupplierEditTabs = {
    MAIN_SUPPLIER_TAB: "MAIN_SUPPLIER_TAB",
    CONTACTS_TAB: 'CONTACTS_TAB'
}
