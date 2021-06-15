import ReactDOM from "react-dom";
import React from "react";
import {AdditionalChargeRatesSelectorModalComponent} from "./AdditionalChargeRatesSelectorModalComponent/AdditionalChargeRatesSelectorModalComponent";


document.AdditionalChargeRatesSelectorRenderer = (domContainer, customerId, onSelect,onClose) => {

    ReactDOM.render(
        React.createElement(
            AdditionalChargeRatesSelectorModalComponent,
            {
                customerId,
                onSelect,
                onClose
            }
        ),
        domContainer
    );
}

document.unmountComponentAtNode = (node) => {
    ReactDOM.unmountComponentAtNode(node);
}