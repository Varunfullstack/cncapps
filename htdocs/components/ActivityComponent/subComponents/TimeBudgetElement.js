import React from 'react';
import * as PropTypes from "prop-types";
import {TeamType} from "../../utils/utils";
import ToolTip from "../../shared/ToolTip";

export function TimeBudgetElement(props) {
    const {
        currentUserTeamId,
        hdRemainMinutes,
        esRemainMinutes,
        imRemainMinutes,
        projectRemainMinutes,
        onExtraTimeRequest
    } = props;
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
        case TeamType.SmallProjects:
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
    return (
        <React.Fragment>
            <ToolTip title="request time">
                <a className="fal fa-hourglass-start fa-2x m-5 pointer icon"
                   onClick={onExtraTimeRequest}
                />
            </ToolTip>
            <h2 style={{color: "red"}}> {`${teamPrefix}:${timeRemaining}`}</h2>
        </React.Fragment>
    )
}

TimeBudgetElement.propTypes = {
    children: PropTypes.node,
    currentUserTeamId: PropTypes.number,
    hdRemainMinutes: PropTypes.number,
    esRemainMinutes: PropTypes.number,
    imRemainMinutes: PropTypes.number,
    projectRemainMinutes: PropTypes.number,
    onExtraTimeRequest: PropTypes.func,
};