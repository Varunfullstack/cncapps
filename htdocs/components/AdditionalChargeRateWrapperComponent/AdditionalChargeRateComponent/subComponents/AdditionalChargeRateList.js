import React from "react";
import Table from "../../../shared/table/table";
import * as PropTypes from "prop-types";
import ToolTip from "../../../shared/ToolTip";
import APICustomers from "../../../services/APICustomers";
import {poundFormat} from "../../../utils/utils";

const globalType = 'Standard Base Rate';

export class AdditionalChargeRateList extends React.Component {


    constructor(props, context) {
        super(props, context);
        this.state = {
            customersById: {},
            customersLoaded: false
        }
    }

    componentDidMount() {
        this.loadCustomers();
    }

    mapAdditionalChargesToRows(additionalCharges) {

        const {customersById, customersLoaded} = this.state;
        if (!customersLoaded) {
            return [];
        }

        return additionalCharges.reduce(
            (acc, additionalCharge) => {
                acc.push({
                    id: additionalCharge.id,
                    type: globalType,
                    description: additionalCharge.description,
                    salePrice: additionalCharge.salePrice,
                    timeBudgetMinutes: additionalCharge.timeBudgetMinutes,
                    notes: additionalCharge.notes,
                })

                additionalCharge.specificCustomerPrices.forEach(specificCustomerPrice => {
                    acc.push({
                        id: `${additionalCharge.id}-${specificCustomerPrice.customerId}`,
                        type: customersById[specificCustomerPrice.customerId]?.name,
                        description: additionalCharge.description,
                        salePrice: specificCustomerPrice.salePrice,
                        timeBudgetMinutes: specificCustomerPrice.timeBudgetMinutes,
                        notes: '',
                    })
                })
                return acc;
            },
            []
        ).sort((a, b) => {
            const lowerCaseDescriptionA = a.description.toLowerCase();
            const lowerCaseDescriptionB = b.description.toLowerCase();
            if (lowerCaseDescriptionA === lowerCaseDescriptionB) {
                const lowerCaseTypeA = a.type.toLowerCase();
                const lowerCaseGlobalType = globalType.toLowerCase();
                if (lowerCaseTypeA === lowerCaseGlobalType) {
                    return -1;
                }
                const lowerCaseTypeB = b.type.toLowerCase();
                if (lowerCaseTypeB === lowerCaseGlobalType) {
                    return 1;
                }
                return lowerCaseTypeA.localeCompare(lowerCaseTypeB);
            }
            return lowerCaseDescriptionA.localeCompare(lowerCaseDescriptionB);
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
                }, {}),
                customersLoaded: true
            });
        } catch (error) {
            console.error('Failed to retrieve additional charge rates');
        }
    }


    render() {
        return <div>
            <div style={{width: "30px"}} onClick={() => this.props.onAdd()}>
                <ToolTip title="New Additional Charge">
                    <i className="fal fa-2x fa-plus color-gray1 pointer"/>
                </ToolTip>
            </div>
            <Table
                id="additionalChargeRates"
                data={this.mapAdditionalChargesToRows(this.props.additionalChargeRates || [])}
                pk="id"
                columns={
                    [
                        {
                            hdToolTip: "Customer",
                            hdClassName: "text-center",
                            icon: "fal fa-2x fa-building color-gray2 pointer",
                            path: "type",
                            label: "",
                        },
                        {
                            hdToolTip: "Description",
                            hdClassName: "text-center",
                            icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                            path: "description",
                            label: "",
                        },
                        {
                            hdToolTip: "Sale Price",
                            hdClassName: "text-center",
                            icon: "fal fa-2x fa-coins color-gray2 pointer",
                            path: "salePrice",
                            className: 'text-right',
                            content: (item => {
                                return poundFormat(item.salePrice);
                            })
                        },
                        {
                            hdToolTip: "Expected time for the task",
                            hdClassName: "text-center",
                            className: 'text-right',
                            icon: "fal fa-2x fa-clock color-gray2 pointer",
                            path: "timeBudgetMinutes",
                        },
                        {
                            hdToolTip: "Notes",
                            hdClassName: "text-center",
                            icon: "fal fa-2x fa-file color-gray2 pointer",
                            path: "notes",
                        },
                        {
                            path: 'id',
                            label: '',
                            content: (item) => {
                                if (item.type !== globalType) {
                                    return '';
                                }
                                return (
                                    <React.Fragment>

                                        <i onClick={() => this.props.onEdit(item.id)}
                                           className="fal fa-edit fa-2x m-5 pointer color-gray"/>
                                        {
                                            item.canDelete ?
                                                <i onClick={() => this.props.onDelete(item)}
                                                   className="fal fa-trash-alt fa-2x m-5 pointer color-gray"/>
                                                : null
                                        }
                                    </React.Fragment>
                                )
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
    onAdd: PropTypes.func,
    onEdit: PropTypes.func,
    onDelete: PropTypes.func
};