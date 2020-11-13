"use strict";
import {params} from "../utils/utils";
import ReactDOM from 'react-dom';
import React from 'react';
import ActivityDisplayComponent from "./subComponents/ActivityDisplayComponent";
import ActivityEditComponent from "./subComponents/ActivityEditComponent";
import GatherFixedInformationComponent from "./subComponents/GatherFixedInformationComponent";
import GatherManagementReviewDetailsComponent from "./subComponents/GatherManagementReviewDetailsComponent";

import 'ActivityComponent.css';

class ActivityComponent extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        const action = params.get('action');
        return (
            <div>
                {action === 'displayActivity' ? ActivityDisplayComponent : null}
                {action === 'editActivity' ? ActivityEditComponent : null}
                {action === 'gatherFixedInformation' ? GatherFixedInformationComponent : null}
                {action === 'gatherManagementReviewDetails' ? GatherManagementReviewDetailsComponent : null}
            </div>
        );
    }
}

const domContainer = document.querySelector("#reactMainActivity");
ReactDOM.render(React.createElement(ActivityComponent), domContainer);