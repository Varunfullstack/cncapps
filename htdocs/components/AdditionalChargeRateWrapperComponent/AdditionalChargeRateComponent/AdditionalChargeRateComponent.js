import React from "react";
import {AdditionalChargeRateList} from "./subComponents/AdditionalChargeRateList";
import AddInternalNoteModalComponent from "../../Modals/AddInternalNoteModalComponent";
import Modal from "../../shared/Modal/modal";
import CNCCKEditor from "../../shared/CNCCKEditor";

import '../../style.css';
import './AdditionalChargeRateComponent.css';
import CustomerSearch from "../../shared/CustomerSearch";
import Table from "../../shared/table/table";

export class AdditionalChargeRate extends React.Component {


    constructor(props, context) {
        super(props, context);
        this.state = {
            additionalChargeRates: [],
            showAddOrEditModal: true,
            isAdd: true,
            editingAdditionalChargeRate: {
                specificCustomerPrices: [
                    {
                        customer
                    }
                ]
            }
        }

        this.loadAdditionalChargeRates();
    }

    cancelModal = () => {
        this.setState({showAddOrEditModal: false, isAdd: false, editingAdditionalChargeRate: null})
    }

    render() {
        const {
            additionalChargeRates,
            isAdd,
            editingAdditionalChargeRate,
            isEditingAdditionalChargeRateValid
        } = this.state;
        return (
            <React.Fragment>
                {
                    editingAdditionalChargeRate ?

                        <Modal show={true} title={isAdd ? 'Add' : 'Edit'} footer={
                            <React.Fragment>
                                <button disabled={!isEditingAdditionalChargeRateValid}
                                        onClick={this.saveContact}
                                >Save
                                </button>
                                <button onClick={this.cancelModal}>Cancel</button>
                            </React.Fragment>
                        }>
                            <div className="additional_charge_rate_form">
                                <label htmlFor="description"> Description </label>
                                <input name="description"
                                       value={editingAdditionalChargeRate.description}
                                       maxLength="45"
                                       required
                                       onChange={this.editingContactChangedField}
                                />
                                <label htmlFor="salePrice"> Sale Price </label>
                                <input name="salePrice"
                                       value={editingAdditionalChargeRate.salePrice}
                                       type="number"
                                       required
                                       onChange={this.editingContactChangedField}
                                />
                                <label> Notes </label>
                                <div className="modal_editor">
                                    <CNCCKEditor value={editingAdditionalChargeRate.notes} type="inline"
                                                 onChange={this.editingContactChangedField}/>
                                </div>
                                <div className="specificCustomerPriceEditForm">
                                    <CustomerSearch/>
                                    <div>
                                        <label htmlFor="salePrice">Specific Sale Price </label>
                                        <input name="salePrice"
                                               value={editingAdditionalChargeRate.salePrice}
                                               type="number"
                                               required
                                               onChange={this.editingContactChangedField}
                                        />
                                    </div>
                                    <button>Add</button>
                                </div>
                                <div>
                                    <Table>

                                    </Table>
                                </div>
                            </div>

                        </Modal> : null
                }
                <AdditionalChargeRateList additionalChargeRates={additionalChargeRates}/>
            </React.Fragment>
        )
    }

    async loadAdditionalChargeRates() {
        try {

            const response = await fetch('?action=getAdditionalChargeRates');
            const res = await response.json();
            this.setState({additionalChargeRates: res.data});
        } catch (error) {
            console.error('Failed to retrieve additional charge rates');
        }

    }
}