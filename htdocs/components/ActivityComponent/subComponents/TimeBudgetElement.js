import React from 'react';
import * as PropTypes from "prop-types";
import {TeamType} from "../../utils/utils";

export function TimeBudgetElement(props) {
    const {currentUserTeamId, hdRemainMinutes, esRemainMinutes, imRemainMinutes, projectRemainMinutes} = props;
    let teamPrefix;
    let timeRemaining;
    switch (currentUserTeamId) {
        case TeamType.Helpdesk:
            teamPrefix = 'HD';
            timeRemaining = hdRemainMinutes;
            break;
        case TeamType.Escalations:
            teamPrefix = 'ES';
            timeRemaining = esRemainMinutes;
            break;
        case TeamType.Small_Projects:
            teamPrefix = 'SP';
            timeRemaining = imRemainMinutes;
            break;
        case TeamType.Projects:
            teamPrefix = 'P';
            timeRemaining = projectRemainMinutes;
            break;
        default:
            return null;
    }
    return <h2 style={{color: "red"}}> {`${teamPrefix}:${timeRemaining}`}</h2>;
}

TimeBudgetElement.propTypes = {
    children: PropTypes.node,
    currentUserTeamId: PropTypes.number,
    hdRemainMinutes: PropTypes.number,
    esRemainMinutes: PropTypes.number,
    imRemainMinutes: PropTypes.number,
    projectRemainMinutes: PropTypes.number,
};