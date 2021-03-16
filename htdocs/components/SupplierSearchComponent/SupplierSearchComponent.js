import SupplierSearch from "../shared/SupplierSearch";
import ReactDOM from "react-dom";
import React from "react";
import '../style.css';

export class SupplierSearchComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    handleChange = (value) => {
        const input = document.getElementById(this.props.inputId);
        input.value = value.id;
    }

    render() {
        return (
            <SupplierSearch onChange={this.handleChange}
                            disabled={this.props.disabled}
            />
        )
    }
}

document.renderSupplierSearchComponent = (domContainer, inputId) => {
    const element = document.getElementById(inputId)
    const instance = ReactDOM.render(React.createElement(SupplierSearchComponent, {inputId}), domContainer);
    element.reactInstance = instance;
}
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById("reactSupplierSearchContainer");
    const inputId = domContainer.dataset.inputId;
    document.renderSupplierSearchComponent(domContainer,inputId);
})


document.unmountComponentAtNode = (node) => {
    ReactDOM.unmountComponentAtNode(node);
}