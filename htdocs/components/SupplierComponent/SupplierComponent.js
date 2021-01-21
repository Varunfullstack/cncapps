"use strict";
import React from 'react';
import ReactDOM from 'react-dom';
import {params} from "../utils/utils";
import '../style.css';
import {SupplierListComponent} from "./subComponents/SupplierListComponent";

export class SupplierComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    getElement(action) {
        switch (action) {
            default:
                return <SupplierListComponent key="test"/>
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
