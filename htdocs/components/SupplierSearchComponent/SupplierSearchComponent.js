import ReactDOM from "react-dom";
import React from "react";
import '../style.css';
import SupplierSelectorComponent
    from "../PurchaseOrderSupplierAndContactInputsComponent/subComponents/SupplierSelectorComponent";
import PropTypes from "prop-types";

export class SupplierSearchComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    handleChange = (value) => {
        const input = document.getElementById(this.props.inputId);
        let id = null;
        if (value) {
            id = value.id;
        }
        input.value = id;
    }

    render() {
        return (
            <SupplierSelectorComponent onChange={this.handleChange}
                                       supplierId={this.props.supplierId}
            />
        )
    }
}


SupplierSearchComponent.propTypes = {
    supplierId: PropTypes.number,
    inputId: PropTypes.string.isRequired
}

document.renderSupplierSearchComponent = (domContainer, {inputId, supplierId}) => {
    const element = document.getElementById(inputId)
    element.reactInstance = ReactDOM.render(React.createElement(SupplierSearchComponent, {
        inputId,
        supplierId
    }), domContainer);
}

document.unmountComponentAtNode = (node) => {
    ReactDOM.unmountComponentAtNode(node);
}