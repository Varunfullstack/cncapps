"use strict";
import Spinner from './../shared/Spinner/Spinner';
import MainComponent from '../shared/MainComponent'
import React from 'react';
import ReactDOM from 'react-dom';
import './KPIReportComponent.css'
import './../style.css';

export default class KPIReportComponent extends MainComponent {
    
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
        }
    }

    componentDidMount() {
        
    } 
    
    render() {        
        const { _showSpinner} = this.state;
        return  <div>
                    <Spinner show={_showSpinner}></Spinner>
                    <h1>Welcome to KPI</h1>
                </div>;        
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainKPIReport");
    ReactDOM.render(React.createElement(KPIReportComponent), domContainer);
});
