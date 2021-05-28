import React from "react";
import Table from "../../../shared/table/table";
import {TrueFalseIconComponent} from "../../../shared/TrueFalseIconComponent/TrueFalseIconComponent";
import * as PropTypes from "prop-types";
import ToolTip from "../../../shared/ToolTip";

export class AdditionalChargeRateList extends React.Component {
    render() {
        return <div>
            <div style={{width: "30px"}} onClick={() => this.props.onAdd()}>
                <ToolTip title="New Additional Charge">
                    <i className="fal fa-2x fa-plus color-gray1 pointer"/>
                </ToolTip>
            </div>
            <Table
                id="additionalChargeRates"
                data={this.props.additionalChargeRates || []}
                pk="id"
                columns={
                    [
                        {
                            hdToolTip: "Description",
                            hdClassName: "text-center",
                            icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                            path: "description",
                            label: "",
                            sortable: true,
                        },
                        {
                            hdToolTip: "Sale Price",
                            hdClassName: "text-center",
                            icon: "fal fa-2x fa-coins color-gray2 pointer",
                            path: "salePrice",
                        },
                        {
                            hdToolTip: "Expected time for the task",
                            hdClassName: "text-center",
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