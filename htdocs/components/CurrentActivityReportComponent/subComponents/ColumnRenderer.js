import React from 'react';
import {getWorkIconClassName} from "../../utils/utils";
import moment from "moment";

export class ColumnRenderer {
    static addToolTip(element, title) {
        return (
            <div className="tooltip">
                {element}
                <div className="tooltiptext tooltip-bottom">
                    {title}
                </div>
            </div>)
    }

    static getWorkIconColumn(startWork, code) {
        return {
            hide: false,
            order: 1,
            path: null,
            label: "",
            key: "work",
            sortable: false,
            hdClassName: "text-center",
            className: "text-center",
            content: (problem) =>
                ColumnRenderer.addToolTip(
                    (
                        <div key="img1"
                             onClick={() => startWork(problem, code)}
                        >
                            <i className={getWorkIconClassName(problem)}/>
                        </div>
                    ),
                    problem.workBtnTitle
                ),
        }
    }

    static getFixSLAWarningColumn() {
        return {
            hide: false,
            order: 2.1,
            path: null,
            key: "fixSLAWarning",
            label: "",
            sortable: false,
            content: (problem) => {

                if (problem.priority > 3) {
                    return null;
                }

                let fixHours;
                let penaltiesAgreed;

                switch (problem.priority) {
                    case 1:
                        penaltiesAgreed = problem.slaP1PenaltiesAgreed;
                        fixHours = problem.slaFixHoursP1;
                        break;
                    case 2:
                        penaltiesAgreed = problem.slaP2PenaltiesAgreed;
                        fixHours = problem.slaFixHoursP2;
                        break;
                    case 3:
                        penaltiesAgreed = problem.slaP3PenaltiesAgreed;
                        fixHours = problem.slaFixHoursP3;
                        break;
                }

                if (!penaltiesAgreed) {
                    return null;
                }

                return ColumnRenderer.addToolTip(
                    (
                        <i className="fal fa-2x fa-bullseye-arrow color-gray pointer float-right inbox-icon"/>
                    ),
                    `Fix SLA of ${fixHours} hours for this SR. Treat as urgent.`
                )
            }
        }
    }

    static getSpecialAttentionColumn() {
        return {
            hide: false,
            order: 2,
            path: null,
            key: "custsomerIcon",
            label: "",
            sortable: false,
            toolTip: "Special Attention customer / contact",
            content: (problem) => {
                if (!problem.specialAttentionCustomer) {
                    return null
                }
                return <i className="fal fa-2x fa-star color-gray pointer float-right inbox-icon"
                          key="starIcon"
                />
            }
        }
    }

    static getFutureWorkColumn() {
        return {
            hide: false,
            order: 4.1,
            path: null,
            key: "Future Icon",
            label: "",
            sortable: false,
            content: (problem) => {
                const momentAlarmDateTime = moment(problem.alarmDateTime, 'YYYY-MM-DD HH:mm:ss');
                if (!problem.alarmDateTime || !momentAlarmDateTime.isValid() || (momentAlarmDateTime.isSameOrBefore(moment()))) {
                    return null;
                }
                return this.addToolTip(
                    <i className="fal fa-2x fa-alarm-snooze color-gray pointer float-right inbox-icon"
                       key="starIcon"
                    />,
                    `This Service Request is scheduled for the future date of ${momentAlarmDateTime.format("DD/MM/YYYY HH:mm")}`)
            }
        };
    }

    static getRequestTimeColumn(requestAdditionalTime) {
        return {
            hide: false,
            order: 10,
            path: null,
            label: "",
            key: "moreTime",
            hdToolTip: "Amount of time left on the Service Request",
            icon: "fal fa-2x fa-stopwatch color-gray2 ",
            width: "40",
            sortable: false,
            hdClassName: "text-center",
            className: "text-center",
            toolTip: "Request more time",
            content: (problem) => {
                return (
                    <div key="img1"
                         style={{display: "flex", flexDirection: "row", width: "100%", alignItems: "center"}}
                         onClick={() => requestAdditionalTime(problem)}
                    >
                        <i className="fal fa-2x fa-hourglass-end color-gray inbox-icon float-left"
                           style={{cursor: "pointer", width: 20}}
                        />
                        <span className="float-right">
                            {problem.minutesRemaining}
                        </span>
                    </div>
                )
            }
        }
    }

    static getOnHoldColumn() {
        return {
            hide: false,
            order: 3,
            path: null,
            key: "hoursRemainingIcon",
            label: "",
            sortable: false,
            toolTip: "On Hold",
            className: "text-center",
            content: (problem) => {
                if (!problem.awaitingCustomerResponse) {
                    return;
                }
                return (
                    <i className="fal  fa-user-clock color-gray pointer inbox-icon"
                       key="icon"
                       style={{float: "right"}}
                    />
                )
            }
        }
    }

    static getSLABreachedColumn() {
        return {
            hide: false,
            order: 4,
            path: null,
            key: "problemIdIcon",
            label: "",
            sortable: false,
            className: "text-center",
            toolTip: "SLA Failed for this Service Request",
            content: (problem) => {
                if (!problem.isSLABreached) {
                    return null;
                }
                return (
                    <i className="fal fa-2x fa-bell-slash color-gray pointer inbox-icon"
                       title=""
                       key="icon"
                    />
                )
            }
        }
    }

    static getPriorityColumn() {
        return {
            hide: false,
            order: 11,
            path: "priority",
            label: "",
            hdToolTip: "Service Request Priority",
            icon: "fal fa-2x fa-signal color-gray2 ",
            sortable: false,
            hdClassName: "text-center",
            className: "text-center",
            classNameColumn: 'priorityClass',
            content: (problem) => {
                if (problem.priority !== 1) {
                    return problem.priority;
                }
                return this.addToolTip(
                    <i className="fal fa-2x fa-exclamation-triangle color-gray"/>,
                    `Priority 1`)
            }
        }
    }
}
