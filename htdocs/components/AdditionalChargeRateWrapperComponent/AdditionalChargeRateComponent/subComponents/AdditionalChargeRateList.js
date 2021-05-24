import React from "react";
import Table from "../../../shared/table/table";
import {TrueFalseIconComponent} from "../../../shared/TrueFalseIconComponent/TrueFalseIconComponent";
import * as PropTypes from "prop-types";

export class AdditionalChargeRateList extends React.Component {
    render() {
        return <div>
            <button onClick={this.props.onAdd}>Add</button>
            <Table
                id="additionalChargeRates"
                data={this.props.additionalChargeRates || []}
                pk="id"
                columns={
                    [
                        {
                            path: "description",
                            label: "Description",
                            sortable: true,
                        },
                        {
                            path: "salePrice",
                            label: "Sale Price",
                        },
                        {
                            path: "notes",
                            label: "Notes"
                        },
                        {
                            path: "customerSpecificPriceAllowed",
                            label: "Allows Specific Customer Prices",
                            content: (additionalCharge) => {
                                return <TrueFalseIconComponent
                                    value={additionalCharge.customerSpecificPriceAllowed}/>
                            }
                        }
                    ]
                }
            >

            </Table>
        </div>;
    }
}

AdditionalChargeRateList.propTypes = {
    additionalChargeRates: PropTypes.any,
    onAdd: PropTypes.func
};