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

        let id = null;
        if (value) {
            id = value.id;
        }
        if (this.props.inputId) {
            const input = document.getElementById(this.props.inputId);
            input.value = id;
        }
        if (this.props.onChange) {
            this.props.onChange(id);
        }

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
    inputId: PropTypes.string,
    onChange: PropTypes.func
}

document.renderSupplierSearchComponent = (domContainer, {inputId, supplierId, onChange}) => {
    const element = document.getElementById(inputId)
    const renderedInstance =
        ReactDOM.render(React.createElement(SupplierSearchComponent, {
            inputId,
            supplierId,
            onChange,
        }), domContainer);
    if (element) {
        element.reactInstance = renderedInstance;
    }
}

document.unmountComponentAtNode = (node) => {
    ReactDOM.unmountComponentAtNode(node);
}