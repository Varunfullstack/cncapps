import ReactDOM from "react-dom";
import React from "react";
import AssetListSelectorComponent from "../shared/AssetListSelectorComponent/AssetListSelectorComponent";

document.AssetPickerComponentRender = (elementId, customerId, emptyAssetReason, assetName, assetTitle, onChange) => {
    const domContainer = document.querySelector(elementId);
    ReactDOM.render(React.createElement(AssetListSelectorComponent, {
        customerId,
        emptyAssetReason,
        assetName,
        assetTitle,
        onChange
    }), domContainer);
}

document.unmountComponentAtNode = (node) => {
    ReactDOM.unmountComponentAtNode(node);
}