"use strict";
import React from 'react';
import ReactDOM from 'react-dom';
import {params} from "../utils/utils";
import '../style.css';
import {SupplierListComponent} from "./subComponents/SupplierListComponent";
import {SupplierEditComponent} from "./subComponents/SupplierEditComponent";

export class SupplierComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    getElement(action) {
        switch (action) {
            case "edit":
                return <SupplierEditComponent key="supplierEdit"/>
            default:
                return <SupplierListComponent key="supplierList"/>
        }
    }

    render() {
        const action = params.get('action');
        return (
            <div>
                {this.getElement(action)}
            </div>
        );
    }

}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainActivity");
    ReactDOM.render(React.createElement(SupplierComponent), domContainer);
});
