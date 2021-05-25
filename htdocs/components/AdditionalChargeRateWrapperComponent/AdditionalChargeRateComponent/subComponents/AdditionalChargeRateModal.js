import React from "react";
import APICustomers from "../../../services/APICustomers";
import Modal from "../../../shared/Modal/modal";
import CNCCKEditor from "../../../shared/CNCCKEditor";
import CustomerSearch from "../../../shared/CustomerSearch";
import Table from "../../../shared/table/table";
import * as PropTypes from "prop-types";

const EDITING_CUSTOMER_PRICE_INITIAL_STATE = {
    customerId: '',
    salePrice: ''
}

export class AdditionalChargeRateModal extends React.Component {


    constructor(props, context) {
        super(props, context);
        this.state = {
            isEditingAdditionalChargeRateValid: this.isAdditionalChargeRateValid(this.props.editingAdditionalChargeRate),
            editingAdditionalChargeRate: this.props.editingAdditionalChargeRate,
            editingCustomerPrice: EDITING_CUSTOMER_PRICE_INITIAL_STATE,
            isSpecificCustomerPriceValid: false,
            isEditSpecificCustomerPrice: false,
            customersById: {},
        }
        this.loadCustomers();
    }

    isAdditionalChargeRateValid(additionalChargeRate) {
        return additionalChargeRate.description && additionalChargeRate.salePrice;
    }

    updateEditingAdditionalChargeRateField = ($event, fieldName = null) => {
        let value = $event
        if (!fieldName) {
            fieldName = $event.target.name;
            value = $event.target.value;
            if ($event.target.type === 'number') {
                value = parseFloat(value);
            }

        }
        const updatedAdditionalChargeRate = {...this.state.editingAdditionalChargeRate, [fieldName]: value};
        this.setState({
            editingAdditionalChargeRate: updatedAdditionalChargeRate,
            isEditingAdditionalChargeRateValid: this.isAdditionalChargeRateValid(updatedAdditionalChargeRate)
        });
    }

    updateEditingCustomerPriceCustomerId = (customer) => {

        const updatedSpecificCustomerPrice = {...this.state.editingCustomerPrice, customerId: customer.id};
        this.setState(
            {
                editingCustomerPrice: updatedSpecificCustomerPrice,
                isSpecificCustomerPriceValid: this.isSpecificCustomerPriceValid(updatedSpecificCustomerPrice)
            }
        )
    }

    setSpecificCustomerPriceEditingItem = (item) => {
        this.setState(
            {
                editingCustomerPrice: {...item},
                isEditSpecificCustomerPrice: true,
                isSpecificCustomerPriceValid: this.isSpecificCustomerPriceValid(item)
            })

    }

    updateEditingCustomerPriceSalePrice = ($event) => {
        const updatedSpecificCustomerPrice = {
            ...this.state.editingCustomerPrice,
            salePrice: parseFloat($event.target.value)
        };
        this.setState(
            {
                editingCustomerPrice: updatedSpecificCustomerPrice,
                isSpecificCustomerPriceValid: this.isSpecificCustomerPriceValid(updatedSpecificCustomerPrice)
            }
        )
    }

    addOrUpdateSpecificCustomerPrice = () => {
        const {editingAdditionalChargeRate, editingCustomerPrice, isEditSpecificCustomerPrice} = this.state;
        const {specificCustomerPrices} = editingAdditionalChargeRate;
        let modifiedSpecificCustomerPrices = specificCustomerPrices.map(x => x.customerId === editingCustomerPrice.customerId ? {...editingCustomerPrice} : x);
        if (!isEditSpecificCustomerPrice) {
            modifiedSpecificCustomerPrices = [...specificCustomerPrices, {...editingCustomerPrice}];
        }
        this.setState({
            editingAdditionalChargeRate: {
                ...editingAdditionalChargeRate,
                specificCustomerPrices: modifiedSpecificCustomerPrices
            },
            isEditSpecificCustomerPrice: false,
            editingCustomerPrice: EDITING_CUSTOMER_PRICE_INITIAL_STATE,
            isSpecificCustomerPriceValid: false
        })
    }


    apiCustomer = new APICustomers();
    loadCustomers = async () => {
        try {
            const customers = await this.apiCustomer.getCustomers()
            this.setState({
                customersById: customers.reduce((acc, customer) => {
                    acc[customer.id] = customer;
                    return acc
                }, {})
            });
        } catch (error) {
            console.error('Failed to retrieve additional charge rates');
        }
    }

    isSpecificCustomerPriceValid(specificCustomerPrice) {
        return (specificCustomerPrice.customerId && specificCustomerPrice.salePrice);
    }

    saveAdditionalChargeRate = () => {
        this.props.onSave(this.state.editingAdditionalChargeRate);
    }

    render() {

        const {
            isEditingAdditionalChargeRateValid,
            editingAdditionalChargeRate,
            customersById,
            editingCustomerPrice,
            isEditSpecificCustomerPrice,
            isSpecificCustomerPriceValid
        } = this.state;
        const {onClose} = this.props;
        const {salePrice, notes, description, specificCustomerPrices, id} = editingAdditionalChargeRate;
        return <Modal show={true}
                      title={id ? "Edit" : "Add"}
                      onClose={onClose}
                      footer={
                          <React.Fragment>
                              <button disabled={!isEditingAdditionalChargeRateValid}
                                      onClick={this.saveAdditionalChargeRate}
                              >Save
                              </button>
                              <button onClick={onClose}>Cancel</button>
                          </React.Fragment>
                      }
        >
            <div className="additional_charge_rate_form">
                <label htmlFor="description"> Description </label>
                <input name="description"
                       value={description}
                       maxLength="45"
                       required
                       onChange={this.updateEditingAdditionalChargeRateField}
                />
                <label htmlFor="salePrice"> Sale Price </label>
                <input name="salePrice"
                       value={salePrice || ""}
                       type="number"
                       required
                       onChange={this.updateEditingAdditionalChargeRateField}
                />
                <label> Notes </label>
                <div className="modal_editor">
                    <CNCCKEditor value={notes} type="inline"
                                 name="notes"
                                 onChange={(value) => this.updateEditingAdditionalChargeRateField(value, 'notes')}/>
                </div>
                <div className="specificCustomerPriceEditForm">
                    <CustomerSearch onChange={this.updateEditingCustomerPriceCustomerId}
                                    customerID={editingCustomerPrice.customerId}
                                    customerName={customersById[editingCustomerPrice.customerId]?.name || ""}
                                    disabled={isEditSpecificCustomerPrice}
                    />
                    <div>
                        <label htmlFor="salePrice">Specific Sale Price </label>
                        <input name="salePrice"
                               value={editingCustomerPrice.salePrice}
                               type="number"
                               required
                               onChange={this.updateEditingCustomerPriceSalePrice}
                        />
                    </div>
                    <button
                        disabled={!isSpecificCustomerPriceValid}
                        onClick={this.addOrUpdateSpecificCustomerPrice}>{isEditSpecificCustomerPrice ? "Update" : "Add"}</button>
                </div>
                <div>
                    <Table data={specificCustomerPrices}
                           pk="customerId"
                           key="specificCustomerPrices"
                           columns={
                               [
                                   {
                                       path: "customerId",
                                       label: "Customer",
                                       sortable: true,
                                       content: (item) => {
                                           return customersById[item.customerId]?.name;
                                       }
                                   },
                                   {
                                       path: "salePrice",
                                       label: "Sale Price",
                                   },
                                   {
                                       path: "",
                                       label: "",
                                       content: (item) => {
                                           return <i
                                               onClick={() => this.setSpecificCustomerPriceEditingItem(item)}
                                               className="fal fa-edit fa-2x m-5 pointer icon"/>

                                       }
                                   }
                               ]
                           }
                    />
                </div>
            </div>

        </Modal>;
    }
}

AdditionalChargeRateModal.propTypes = {
    editingAdditionalChargeRate: PropTypes.any,
    onClose: PropTypes.func,
    onSave: PropTypes.func,
};