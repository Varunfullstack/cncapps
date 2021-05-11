import ReactDOM from "react-dom";
import React from "react";
import '../style.css';
import SupplierSelectorComponent
    from "../PurchaseOrderSupplierAndContactInputsComponent/subComponents/SupplierSelectorComponent";
import PropTypes from "prop-types";
import ItemSelectorComponent from "../shared/ItemSelectorComponent/ItemSelectorComponent";

export class ItemSelectorWrapperComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    handleChange = (value) => {

        let id = null;
        if (value) {
            id = value.itemID;
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
            <ItemSelectorComponent onChange={this.handleChange}
                                   itemId={this.props.itemId}
            />
        )
    }
}


ItemSelectorWrapperComponent.propTypes = {
    itemId: PropTypes.number,
    inputId: PropTypes.string,
    onChange: PropTypes.func
}

document.renderItemSelectorComponent = (domContainer, {inputId, itemId, onChange}) => {
    const element = document.getElementById(inputId)
    const renderedInstance =
        ReactDOM.render(React.createElement(ItemSelectorWrapperComponent, {
            inputId,
            itemId,
            onChange,
        }), domContainer);
    if (element) {
        element.reactInstance = renderedInstance;
    }
}

document.unmountComponentAtNode = (node) => {
    ReactDOM.unmountComponentAtNode(node);
}