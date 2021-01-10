"use strict";
import {params} from "../utils/utils";
import ReactDOM from 'react-dom';
import React from 'react';
import './HomeComponent.css';
import '../style.css';

class HomeComponent extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {        
        return (
            <div>
                <h1>Welcome to new Dashboard</h1>       
            </div>
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactHome");
    ReactDOM.render(React.createElement(HomeComponent), domContainer);
});
