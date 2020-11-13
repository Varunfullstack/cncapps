import MainComponent from "../shared/MainComponent.js";
import ActivityListComponent from "./subComponents/ActivityListComponent.js";

import React from 'react';
import ReactDOM from 'react-dom';
import '../style.css';

class ActivityTypeComponent extends MainComponent {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        return this.el(ActivityListComponent);
    }
}

export default ActivityTypeComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactCMPActivityType");
    if (domContainer) {
        ReactDOM.render(React.createElement(ActivityTypeComponent), domContainer);
    }
})
