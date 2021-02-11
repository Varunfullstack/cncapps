import ReactDOM from "react-dom";
import React from "react";
import AssetListSelectorComponent from "../shared/AssetListSelectorComponent/AssetListSelectorComponent";

document.AssetPickerComponentRender = (elementId, customerId, onChange) => {
    const domContainer = document.querySelector(elementId);
    ReactDOM.render(React.createElement(AssetListSelectorComponent,{customerId, onChange}), domContainer);
}