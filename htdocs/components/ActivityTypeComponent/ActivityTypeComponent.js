import MainComponent from "../shared/MainComponent.js";
import ActivityListComponent from "./subComponents/ActivityListComponent.js";

import React, {Fragment} from 'react';
import ReactDOM from 'react-dom';
import '../style.css';

class ActivityTypeComponent extends MainComponent {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        return <Fragment>
            <i className="fal fa-plus"
               onClick={() => {
                   window.location = '/ActivityType.php?action=createActivityType'
               }}
            />
            <ActivityListComponent/>
        </Fragment>
    }
}

export default ActivityTypeComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactCMPActivityType");
    if (domContainer) {
        ReactDOM.render(React.createElement(ActivityTypeComponent), domContainer);
    }
})
