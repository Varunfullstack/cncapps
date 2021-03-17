import React from 'react';
import ReactDOM from "react-dom";
import '../style.css';
import SupplierSelectorComponent from "./subComponents/SupplierSelectorComponent";
import PropTypes from "prop-types";
import SupplierContactSelectorComponent from "./subComponents/SupplierContactSelectorComponent";

export class PurchaseOrderSupplierAndContactInputsComponent extends React.PureComponent {
    supplierInput;
    supplierContactInput;

    constructor(props, context) {
        super(props, context);
        this.state = {
            supplierId: this.props.supplierId,
            supplierContactId: this.props.supplierContactId,
            selectedSupplier: null,
            selectedSupplierContact: null,
        }
        this.supplierInput = document.getElementById(this.props.supplierIdInputId);
        this.supplierContactInput = document.getElementById(this.props.supplierContactIdInputId);
    }

    onSupplierChange = (supplier) => {
        if (!supplier) {
            return this.clearSupplier();
        }
        if (supplier.id === this.state.supplierId) {
            return;
        }
        this.setSupplierValue(supplier);
    }

    clearSupplier = () => {
        this.setSupplierValue(null);
    }

    setSupplierValue = (supplier) => {
        this.setState({supplierId: supplier?.id, selectedSupplier: supplier});
        this.setSupplierInputValue(supplier?.id);
        this.clearSupplierContact();
    }

    setSupplierInputValue = (value) => {
        this.supplierInput.value = value ?? "";
    }

    clearSupplierContact = () => {
        this.setSupplierContactValue(null);
    }

    setSupplierContactValue = (supplierContact) => {
        this.setState({supplierContactId: supplierContact?.id, selectedSupplierContact: supplierContact});
        this.setSupplierContactInputValue(supplierContact?.id);
    }


    setSupplierContactInputValue = (value) => {
        this.supplierContactInput.value = value ?? "";
    }

    onSupplierContactChange = (supplierContact) => {
        this.setSupplierContactValue(supplierContact);
    }

    renderContactLinks = () => {
        const {selectedSupplierContact} = this.state;
        if (!selectedSupplierContact) {
            return '';
        }
        return (
            <React.Fragment>
                <a href={`tel:${selectedSupplierContact.phone}`}
                   target="_blank"
                   style={{marginRight:"4px"}}
                >
                    <i className="fal fa-phone"/>
                </a>
                <a href={`emailto:${selectedSupplierContact.email}`}
                   target="_blank"
                >
                    <i className="fal fa-envelope"/>
                </a>
            </React.Fragment>
        )
    }

    render() {
        const {supplierId, supplierContactId} = this.state;
        return (
            <table width="700px"
                   border="0"
            >
                <tbody>

                <tr>
                    <td className="promptText">Supplier</td>
                    <td className="field">
                        <SupplierSelectorComponent supplierId={supplierId}
                                                   onChange={this.onSupplierChange}
                        />
                        {this.renderSupplierWebsiteLink()}
                    </td>
                </tr>
                <tr>
                    <td className="promptText">Contact</td>
                    <td className="field">
                        <SupplierContactSelectorComponent supplierId={supplierId}
                                                          supplierContactId={supplierContactId}
                                                          onChange={this.onSupplierContactChange}
                        />
                        {this.renderContactLinks()}
                    </td>
                </tr>
                </tbody>
            </table>
        )
    }

    renderSupplierWebsiteLink() {
        const {selectedSupplier} = this.state;
        if (!selectedSupplier || !selectedSupplier.websiteURL) {
            return '';
        }
        return (
            <a href={selectedSupplier.websiteURL}
               target="_blank"
            >
                <i className="fal fa-globe fa-2x"/>
            </a>
        );
    }
}

PurchaseOrderSupplierAndContactInputsComponent.propTypes = {
    supplierId: PropTypes.number,
    supplierContactId: PropTypes.number,
    supplierIdInputId: PropTypes.string,
    supplierContactIdInputId: PropTypes.string
}


document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById("reactPurchaseOrderSupplierAndContactInputs");
    ReactDOM.render(
        React.createElement(
            PurchaseOrderSupplierAndContactInputsComponent,
            {
                supplierId: parseInt(domContainer.dataset.supplierId),
                supplierContactId: parseInt(domContainer.dataset.supplierContactId),
                supplierIdInputId: domContainer.dataset.supplierIdInputId,
                supplierContactIdInputId: domContainer.dataset.supplierContactIdInputId,
            }
        ),
        domContainer
    );
})