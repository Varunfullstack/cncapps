import React from "react";
import Table from "../../shared/table/table";

export class AdditionalChargeRate extends React.Component {
    render() {
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
                            content: additionalCharge => {
                                if(additionalCharge.customerSpecificPriceAllowed){
                                    return
                                }
                            }
                        }
                    ]
                }
            >

            </Table>
        </div>;
    }
}