import React from "react";
import Table from "../../shared/table/table";
import {TrueFalseIconComponent} from "../../shared/TrueFalseIconComponent/TrueFalseIconComponent";

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
        return <div>
            <button>Add</button>
            <Table
                id="additionalChargeRates"
                data={additionalChargeRates || []}
                pk="id"
                columns={
                    [
                        {
                            path: 'description',
                            label: 'Description',
                            sortable: true,
                        },
                        {
                            path: 'salePrice',
                            label: 'Sale Price',
                        },
                        {
                            path: 'notes',
                            label: 'Notes'
                        },
                        {
                            path: 'customerSpecificPriceAllowed',
                            label: 'Allows Specific Customer Prices',
                            content: additionalCharge => <TrueFalseIconComponent
                                value={additionalCharge.customerSpecificPriceAllowed}/>
                        }
                    ]
                }
            >

            </Table>
        </div>;
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