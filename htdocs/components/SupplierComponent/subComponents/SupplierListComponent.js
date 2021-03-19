import React from "react";
import Table from "../../shared/table/table";
import {SupplierService} from "../../services/SupplierService";

import Modal from "../../shared/Modal/modal";
import {VisibilityFilterOptions} from "./SupplierEditComponent";

const NewSupplierForm = {
    town: '',
    county: '',
    postcode: '',
    name: '',
    address1: '',
    address2: '',
    websiteURL: '',
    paymentMethodId: '',
    accountCode: '',
    mainContactTitle: '',
    mainContactPosition: '',
    mainContactFirstName: '',
    mainContactLastName: '',
    mainContactEmail: '',
    mainContactPhone: '',
}

export class SupplierListComponent extends React.PureComponent {


    constructor(props, context) {
        super(props, context);
        this.state = {
            suppliers: [],
            visibilityFilter: VisibilityFilterOptions.SHOW_ACTIVE,
            newSupplier: NewSupplierForm,
            showCreateSupplierModal: false,
            paymentMethods: [],
            isNewSupplierValid: false
        }
    }

    componentDidMount() {

        Promise.all(
            [
                this.fetchSuppliers(),
                this.fetchPaymentMethods()
            ]
        ).then(([suppliers, paymentMethods]) => {
            this.setState({suppliers, paymentMethods});
        });
    }

    async fetchSuppliers() {
        return SupplierService.getSuppliersSummaryData();
    }

    async fetchPaymentMethods() {
        const response = await fetch(`/Supplier.php?action=getPaymentMethods`);
        const jsonResponse = await response.json();
        if (!jsonResponse || jsonResponse.status !== 'ok') {
            throw new Error('Failed to retrieve Supplier: ' + jsonResponse.message);
        }
        return jsonResponse.data;
    }

    getTableElement = () => {
        const {suppliers, visibilityFilter} = this.state;

        let columns = [
            {
                hide: false,
                order: 1,
                path: "name",
                key: "name",
                icon: "fal fa-2x fa-building",
                hdToolTip: "Supplier Name",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
            },
            {
                hide: false,
                order: 2,
                path: "address1",
                key: "address1",

                icon: "fal fa-2x fa-map-marker-alt",
                hdToolTip: "Supplier Address",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (supplierRow) => {
                    return `${supplierRow.address1}${supplierRow.address2 ? `, ${supplierRow.address2}` : ''}, ${supplierRow.town}, ${supplierRow.county}, ${supplierRow.postcode}`
                }

            },
            {
                hide: false,
                order: 3,
                path: "mainContactName",
                key: "mainContactName",
                icon: "fal fa-2x fa-id-card-alt",
                hdToolTip: "Supplier Contact Name",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (supplierRow) => {
                    return `${supplierRow.mainContactTitle ? `${supplierRow.mainContactTitle}. ` : ''}${supplierRow.mainContactName ?? ""}${supplierRow.mainContactPosition ? ` (${supplierRow.mainContactPosition})` : ''}`
                }

            },
            {
                hide: false,
                order: 4,
                path: "mainContactPhone",
                key: "mainContactPhone",
                icon: "fal fa-2x  fa-phone color-gray2 ",
                hdToolTip: "Contact Phone",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (supplierRow) => {
                    if (!supplierRow.mainContactPhone) {
                        return '';
                    }
                    return <a href={`tel:${supplierRow.mainContactPhone}`}>{supplierRow.mainContactPhone}</a>
                }

            },
            {
                hide: false,
                order: 4.1,
                path: "mainContactEmail",
                key: "mainContactEmail",
                icon: "fal fa-2x  fa-at color-gray2 ",
                hdToolTip: "Email",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (supplierRow) => {
                    if (!supplierRow.mainContactEmail) {
                        return '';
                    }
                    return <a href={`mailto:${supplierRow.mainContactEmail}`}>{supplierRow.mainContactEmail}</a>
                }

            },
            {
                hide: visibilityFilter === VisibilityFilterOptions.SHOW_ACTIVE,
                order: 5,
                path: "active",
                key: "id",
                icon: "fal fa-2x fa-eye ",
                hdToolTip: "Active",
                sortable: true,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (supplierRow) => {
                    let icon = "fa-times"
                    if (supplierRow.active) {
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
                key: "address2",
                sortable: false,
                icon: "fal fa-2x fa-edit",
                hdToolTip: "Edit",
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (supplierRow) => (
                    <i onClick={this.editSupplierRowFunction(supplierRow)}
                       className="fal fa-edit fa-2x color-gray pointer"
                    />

                )
            },

        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));

        return <Table
            data={suppliers.filter(x => !(visibilityFilter === VisibilityFilterOptions.SHOW_ACTIVE && !x.active))}
            columns={columns}
            pk="id"
            search={true}
        />
    }

    editSupplierRowFunction = (supplierRow) => {
        return () => {
            // navigate to the edit page
            const newURL = new URL(document.location);
            newURL.searchParams.append('action', 'edit');
            newURL.searchParams.append('supplierId', supplierRow.id);
            window.location = newURL;
        }
    }

    onToggleVisibility = () => {
        let visibilityFilterOption = VisibilityFilterOptions.SHOW_ALL;
        if (this.state.visibilityFilter === VisibilityFilterOptions.SHOW_ALL) {
            visibilityFilterOption = VisibilityFilterOptions.SHOW_ACTIVE;
        }
        this.setState({visibilityFilter: visibilityFilterOption});
    }

    updateField = ($event) => {
        const {target} = $event;
        const updatedSupplier = {...this.state.newSupplier, [target.name]: target.value};
        const isNewSupplierValid = this.isNewSupplierValid(updatedSupplier) && target.checkValidity();
        console.log(isNewSupplierValid);
        this.setState({newSupplier: updatedSupplier, isNewSupplierValid});
    }

    isNewSupplierValid(newSupplier) {
        if (!newSupplier.town) {
            return false;
        }
        if (!newSupplier.county) {
            return false;
        }
        if (!newSupplier.postcode) {
            return false;
        }
        if (!newSupplier.name) {
            return false;
        }
        if (!newSupplier.address1) {
            return false;
        }

        if (!newSupplier.paymentMethodId) {
            return false;
        }
        if (!newSupplier.mainContactTitle) {
            return false;
        }
        if (!newSupplier.mainContactPosition) {
            return false;
        }
        if (!newSupplier.mainContactFirstName) {
            return false;
        }
        if (!newSupplier.mainContactLastName) {
            return false;
        }
        if (!newSupplier.mainContactPhone) {
            return false;
        }
        if (!newSupplier.mainContactEmail) {
            return false;
        }
        return true;

    }

    hideCreateSupplierModal = () => {
        this.setState({newSupplier: NewSupplierForm, showCreateSupplierModal: false, isNewSupplierValid: false});
    }

    createNewSupplier = async () => {
        try {
            await SupplierService.createSupplier(this.state.newSupplier);
            this.hideCreateSupplierModal();
            const suppliers = await this.fetchSuppliers();
            this.setState({suppliers});
        } catch (error) {
            alert(error);
        }
    }

    getCreateSupplierModal = () => {
        const {showCreateSupplierModal, newSupplier, paymentMethods, isNewSupplierValid} = this.state;
        return (
            <Modal show={showCreateSupplierModal}
                   title="Create Supplier"
                   onClose={this.hideCreateSupplierModal}
            >
                <div>
                    Supplier
                </div>
                <div>
                    <label htmlFor=""
                           className="span"
                    >
                        <span>town</span>
                        <input
                            type="text"
                            value={newSupplier.town}
                            name="town"
                            onChange={this.updateField}
                            maxLength="25"
                            required
                        />
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>county</span>
                        <input
                            type="text"
                            name="county"
                            value={newSupplier.county}
                            onChange={this.updateField}
                            maxLength="25"
                            required
                        />
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>postcode</span>
                        <input
                            type="text"
                            name="postcode"
                            value={newSupplier.postcode}
                            onChange={this.updateField}
                            maxLength="25"
                            required
                        />
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>name</span>
                        <input
                            type="text"
                            name="name"
                            value={newSupplier.name}
                            onChange={this.updateField}
                            maxLength="35"
                            required
                        />
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>address1</span>
                        <input
                            type="text"
                            name="address1"
                            value={newSupplier.address1}
                            onChange={this.updateField}
                            maxLength="35"
                            required
                        />
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>address2</span>
                        <input
                            type="text"
                            name="address2"
                            value={newSupplier.address2}
                            onChange={this.updateField}
                            maxLength="35"
                        />
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>websiteURL</span>
                        <input
                            type="text"
                            name="websiteURL"
                            value={newSupplier.websiteURL}
                            onChange={this.updateField}
                            maxLength="100"
                        />
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>Payment Method</span>
                        <select
                            name="paymentMethodId"
                            value={newSupplier.paymentMethodId}
                            onChange={this.updateField}
                            required
                        >
                            <option key="emptyOption"
                                    value=""
                            >-- Pick an option --
                            </option>
                            {paymentMethods.map(x => <option key={x.id}
                                                             value={x.id}
                            >{x.description}</option>)}
                        </select>
                    </label>

                    <label htmlFor=""
                           className="span"
                    >
                        <span>accountCode</span>
                        <input
                            type="text"
                            name="accountCode"
                            value={newSupplier.accountCode}
                            onChange={this.updateField}
                            maxLength="20"
                        />
                    </label>
                </div>
                <div>Main Contact</div>
                <div>
                    <label>
                        <span>
                            Title
                        </span>
                        <input name="mainContactTitle"
                               value={newSupplier.mainContactTitle}
                               maxLength="45"
                               required
                               onChange={this.updateField}
                        />
                    </label>

                    <label>
                        <span>
                            Position*
                        </span>
                        <input name="mainContactPosition"
                               value={newSupplier.mainContactPosition}
                               maxLength="50"
                               required
                               onChange={this.updateField}
                        />
                    </label>
                    <label>
                        <span>
                            First Name*
                        </span>
                        <input name="mainContactFirstName"
                               value={newSupplier.mainContactFirstName}
                               maxLength="25"
                               required
                               onChange={this.updateField}
                        />
                    </label>
                    <label>
                        <span>
                            Last Name*
                        </span>
                        <input name="mainContactLastName"
                               value={newSupplier.mainContactLastName}
                               maxLength="35"
                               required
                               onChange={this.updateField}
                        />
                    </label>
                    <label>
                        <span>
                            Phone*
                        </span>
                        <input name="mainContactPhone"
                               value={newSupplier.mainContactPhone}
                               maxLength="25"
                               required
                               onChange={this.updateField}
                        />
                    </label>
                    <label>
                        <span>
                            Email*
                        </span>
                        <input name="mainContactEmail"
                               value={newSupplier.mainContactEmail}
                               maxLength="60"
                               required
                               type="email"
                               onChange={this.updateField}
                        />
                    </label>
                </div>
                <div>
                    <button disabled={!isNewSupplierValid}
                            onClick={this.createNewSupplier}
                    >Save
                    </button>
                </div>
            </Modal>
        )
    }

    render() {
        const {visibilityFilter} = this.state;

        return (
            <React.Fragment>
                {this.getCreateSupplierModal()}
                <div>
                    <select onChange={this.onToggleVisibility}
                            value={visibilityFilter}
                    >
                        <option value={VisibilityFilterOptions.SHOW_ACTIVE}>Active Only</option>
                        <option value={VisibilityFilterOptions.SHOW_ALL}>Show All</option>
                    </select>
                </div>
                <i className="fal fa-plus fa-2x"
                   onClick={this.showCreateSupplierModal}
                />
                {this.getTableElement()}
            </React.Fragment>
        )
    }

    showCreateSupplierModal = () => {
        this.setState({showCreateSupplierModal: true});
    }
}