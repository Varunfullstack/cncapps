import Modal from "../../shared/Modal/modal";
import React from 'react';

import {Tooltip} from "@material-ui/core";

import "./AdditionalChargeRatesSelectorModalComponent.css"

export class AdditionalChargeRatesSelectorModalComponent extends React.Component {


    constructor(props, context) {
        super(props, context);
        this.state = {
            additionalCharges: []
        }
    }

    componentDidMount() {
        this.fetchCustomerAdditionalCharges()
    }

    onClose = () => {
        this.props.onClose();
    }

    render() {
        const {additionalCharges} = this.state;
        return (
            <Modal show={true}
                   title="Select an Additional Charge"
                   onClose={this.onClose}
                   width="400px"
                   footer={
                       <button onClick={this.onClose}>Cancel</button>
                   }
            >
                <table key="something" style={{width: "100%"}}>
                    <thead>
                    <tr>
                        <th key="description" style={{textAlign: 'center'}}>
                            <Tooltip title="Description">
                                <i className="fal fa-2x fa-file-alt color-gray"/>
                            </Tooltip>
                        </th>
                        <th key="salePrice" style={{textAlign: 'center'}}>
                            <Tooltip title="Sale Price">
                                <i className="fal fa-2x fa-coins color-gray"/>
                            </Tooltip>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {
                        additionalCharges.map((a, index) => (
                                <tr key={index} className="line-select"
                                    onClick={() => this.additionalChargeSelected(a)}>
                                    <td key="description">
                                        {a.description}
                                    </td>
                                    <td key="salePrice" style={{textAlign: "right"}}>
                                        {a.salePrice}
                                    </td>
                                </tr>
                            )
                        )
                    }
                    </tbody>
                </table>
            </Modal>
        )
    }

    async fetchCustomerAdditionalCharges() {
        const {customerId} = this.props;
        const response = await fetch(`/internal-api/customerAdditionalChargeRates/${customerId}`);
        const additionalCharges = await response.json();
        this.setState({additionalCharges});
    }

    additionalChargeSelected(a) {
        this.props.onSelect(a);
    }
}