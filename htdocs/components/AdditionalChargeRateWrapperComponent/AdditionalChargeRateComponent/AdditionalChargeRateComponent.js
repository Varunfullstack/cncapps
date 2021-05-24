import React from "react";
import {AdditionalChargeRateList} from "./subComponents/AdditionalChargeRateList";

export class AdditionalChargeRate extends React.Component {


    constructor(props, context) {
        super(props, context);
        this.state = {
            additionalChargeRates: []
        }

        this.loadAdditionalChargeRates();
    }

    render() {
        const {additionalChargeRates} = this.state;
        return (
            <React.Fragment>
                <AdditionalChargeRateList additionalChargeRates={additionalChargeRates}/>;
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