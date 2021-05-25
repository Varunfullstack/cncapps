import React from "react";
import Table from "../../../shared/table/table";
import {TrueFalseIconComponent} from "../../../shared/TrueFalseIconComponent/TrueFalseIconComponent";
import * as PropTypes from "prop-types";

export class AdditionalChargeRateList extends React.Component {
    render() {
        return <div>
            <button onClick={() => this.props.onAdd()}>Add</button>
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
                            path: 'id',
                            label: '',
                            content: (item) => {
                                return (
                                    <i onClick={() => this.props.onEdit(item.id)}
                                       className="fal fa-edit fa-2x m-5 pointer icon"/>
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
    onEdit: PropTypes.func
};