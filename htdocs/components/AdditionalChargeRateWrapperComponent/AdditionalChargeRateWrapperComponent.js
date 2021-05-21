import ReactDOM from "react-dom";
import React from "react";
import '../style.css';
import {AdditionalChargeRate} from "./AdditionalChargeRateComponent/AdditionalChargeRateComponent";


export function AdditionalChargeRateWrapperComponent() {

    return (
        <AdditionalChargeRate/>
    )
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById('AdditionalChargeRateContainer')
    ReactDOM.render(React.createElement(AdditionalChargeRateWrapperComponent,), domContainer);
})