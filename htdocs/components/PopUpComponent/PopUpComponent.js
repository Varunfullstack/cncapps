import MainComponent from "../shared/MainComponent";
import DailyStatsComponent from "../shared/DailyStatsComponent/DailyStatsComponent"
import {params} from "../utils/utils"
import React from 'react';
import ReactDOM from 'react-dom';

import './../style.css';
import TimeBreakdownComponent from "../ActivityComponent/subComponents/TimeBreakdownComponent";

class PopUpComponent extends MainComponent {
    el = React.createElement

    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        const {el} = this;
        const action = params.get("action");
        if (action == "dailyStats") {
            return <DailyStatsComponent className="resizable"/>;
        }
        if (action == "timeBreakdown")
            return el(TimeBreakdownComponent);

        return el('label', null, "Not Found")
    }
}

export default PopUpComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainPopup");
    ReactDOM.render(React.createElement(PopUpComponent), domContainer);
})
