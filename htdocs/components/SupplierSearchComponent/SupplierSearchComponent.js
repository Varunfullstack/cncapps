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
                            defaultText={this.props.defaultText}
            />
        )
    }
}

document.renderSupplierSearchComponent = (domContainer, {inputId, defaultText}) => {
    const element = document.getElementById(inputId)
    element.reactInstance = ReactDOM.render(React.createElement(SupplierSearchComponent, {
        inputId,
        defaultText
    }), domContainer);
}

document.unmountComponentAtNode = (node) => {
    ReactDOM.unmountComponentAtNode(node);
}