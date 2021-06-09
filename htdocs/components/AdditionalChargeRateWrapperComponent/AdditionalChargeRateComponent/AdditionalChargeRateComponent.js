import React from "react";
import {AdditionalChargeRateList} from "./subComponents/AdditionalChargeRateList";
import '../../style.css';
import './AdditionalChargeRateComponent.css';
import {AdditionalChargeRateModal} from "./subComponents/AdditionalChargeRateModal";
import MainComponent from "../../shared/MainComponent";


const NEW_ADDITIONAL_CHARGE_RATE = {
    description: '',
    notes: '',
    salePrice: 0,
    specificCustomerPrices: [],
    timeBudgetMinutes: 0
}

export class AdditionalChargeRate extends MainComponent {
    constructor(props, context) {
        super(props, context);
        this.state = {
            ...this.state,
            additionalChargeRates: [],
            editingAdditionalChargeRate: null,
        }

        this.loadAdditionalChargeRates();

    }

    setEditingItem = async (id) => {
        const item = await this.loadById(id)
        this.setState({editingAdditionalChargeRate: item})
    };

    async loadById(id) {
        try {
            const response = await fetch(`?action=getById&id=${id}`);
            const res = await response.json();
            return res.data;
        } catch (error) {
            console.error('Failed to retrieve additional charge rates');
        }
    }


    cancelModal = () => {
        this.setState({
            editingAdditionalChargeRate: null,
        })
    }

    saveAdditionalChargeRate = async (additionalChargeRate) => {

        let action = 'update';
        if (!additionalChargeRate.id) {
            action = 'add';
        }
        try {
            const response = await fetch(`?action=${action}`, {
                method: 'POST',
                body: JSON.stringify(additionalChargeRate)
            });
            const res = await response.json();

            if (res.status !== 'ok') {
                throw new Error('Failed to save: ');
            }
        } catch (exception) {
            console.error(exception);
        }
        this.setState({editingAdditionalChargeRate: null});
        this.loadAdditionalChargeRates();
    }

    render() {
        const {
            additionalChargeRates,
            editingAdditionalChargeRate,
        } = this.state;

        return (
            <React.Fragment>
                {this.getConfirm()}
                {
                    editingAdditionalChargeRate ?
                        <AdditionalChargeRateModal editingAdditionalChargeRate={editingAdditionalChargeRate}
                                                   onClose={this.cancelModal}
                                                   onSave={this.saveAdditionalChargeRate}
                        />
                        : null}

                <AdditionalChargeRateList additionalChargeRates={additionalChargeRates}
                                          onAdd={this.newAdditionalChargeRate}
                                          onEdit={this.setEditingItem}
                                          onDelete={this.deleteItem}
                />

            </React.Fragment>
        )
    }

    deleteItem = async (item) => {
        if (!await this.confirm('Are you sure you want to delete this Additional Charge?')) {
            return;
        }
        const response = await fetch(`?action=delete&id=${item.id}`);
        const res = await response.json();
        this.loadAdditionalChargeRates();
    }

    newAdditionalChargeRate = () => {
        this.setState({
            editingAdditionalChargeRate: NEW_ADDITIONAL_CHARGE_RATE,
        })
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