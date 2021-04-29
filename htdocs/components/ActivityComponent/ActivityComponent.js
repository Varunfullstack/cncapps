"use strict";

import ReactDOM from 'react-dom';
import React from 'react';
import ActivityDisplayComponent from "./subComponents/ActivityDisplayComponent";
import ActivityEditComponent from "./subComponents/ActivityEditComponent";
import GatherFixedInformationComponent from "./subComponents/GatherFixedInformationComponent";
import GatherManagementReviewDetailsComponent from "./subComponents/GatherManagementReviewDetailsComponent";
import './ActivityComponent.css';
import '../style.css';
import {params} from "../utils/utils";

class ActivityComponent extends React.Component {
    constructor(props) {
        super(props);
    }

    getAppropriateElement(action) {
        switch (action) {
            case 'displayActivity':
                return <ActivityDisplayComponent/>
            case 'editActivity' :
                return <ActivityEditComponent/>
            case 'gatherFixedInformation' :
                return <GatherFixedInformationComponent/>
            case 'gatherManagementReviewDetails' :
                return <GatherManagementReviewDetailsComponent/>
            default:
                return null
        }
    }

    render() {
        const action = params.get('action');
        return (
            <div>
                {this.getAppropriateElement(action)}
            </div>
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainActivity");
    ReactDOM.render(React.createElement(ActivityComponent), domContainer);
});
