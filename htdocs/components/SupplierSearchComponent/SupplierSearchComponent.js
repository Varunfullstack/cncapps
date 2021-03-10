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
            <SupplierSearch onChange={this.handleChange}/>
        )
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById("reactSupplierSearchContainer");
    ReactDOM.render(React.createElement(SupplierSearchComponent, {inputId: domContainer.dataset.inputId}), domContainer);
})