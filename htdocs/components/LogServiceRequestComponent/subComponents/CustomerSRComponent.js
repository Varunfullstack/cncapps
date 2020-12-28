import Table from "./../../shared/table/table";
import React from 'react';
import moment from "moment";

class CustomerSRComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {}
    }

    addToolTip = (element, title) => {
        return this.el(
            "div",
            {className: "tooltip"},
            element,
            this.el("div", {className: "tooltiptext tooltip-bottom"}, title)
        );
    }
    getTableElement = (items, showContactColumn) => {
        const {el} = this;
        const {openProblemHistory, newSrActivity} = this.props;
        let columns = [
            {
                hide: false,
                order: 1,
                path: null,
                key: "newSr",
                label: "",
                sortable: false,
                toolTip: "Log Activity",
                content: (problem) =>
                    el("i", {
                        className:
                            "fal fa-2x fa-play color-gray pointer float-right inbox-icon",
                        key: "starIcon",
                        onClick: () => newSrActivity(problem.problemID, problem.activityID)
                    })
            },
            {
                hide: false,
                order: 1.1,
                path: null,
                key: "customerIcon",
                label: "",
                sortable: false,
                toolTip: "Special Attention customer or contact",
                content: (problem) => {
                    if (!problem.isSpecialAttention) {
                        return null;
                    }
                    return <i className="fal fa-2x fa-star color-gray pointer float-right inbox-icon"/>
                }
            },
            {
                hide: false,
                order: 1.2,
                path: "problemID",
                label: "",
                key: "problemId",
                hdToolTip: "Problem Id",
                icon: "fal fa-2x fa-hashtag  color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
            },
            {
                hide: false,
                order: 2,
                path: "dateRaised",
                label: "",
                key: "dateRaised",
                hdToolTip: "Raised Date",
                icon: "fal fa-2x fa-calendar-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content: problem => {
                    return moment(problem.dateRaised, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YYYY HH:mm')
                }
            },
            {
                hide: !showContactColumn,
                order: 2.1,
                path: "contactName",
                label: "",
                key: "contactName",
                hdToolTip: "Contact",
                icon: "fal fa-2x fa-user color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "",
            },
            {
                hide: false,
                order: 3,
                path: "reason",
                label: "",
                key: "reason",
                hdToolTip: "Reason",
                icon: "fal fa-2x fa-file-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                content: (problem) => el('div', {
                    className: "pointer",
                    style: {color: 'blue'},
                    onClick: () => openProblemHistory(problem.problemID),
                    dangerouslySetInnerHTML: {__html: problem.reason}
                }),

            },
            {
                hide: false,
                order: 4,
                path: "priority",
                label: "",
                hdToolTip: "Service Request Priority",
                icon: "fal fa-2x fa-signal color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content: problem => {
                    if (problem.priority !== 1) {
                        return problem.priority;
                    }
                    return this.addToolTip(
                        <i className="fal fa-2x fa-exclamation-triangle color-gray"/>,
                        `Priority 1`)
                }
            },
            {
                hide: false,
                order: 5,
                path: "engineerName",
                label: "",
                hdToolTip: "Allocated To",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "",
            }
        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));

        return el(Table, {
            id: "Sr",
            data: items || [],
            columns: columns,
            pk: "activityID",
            search: true,
        });
    }

    render() {
        const {items, showContactColumn} = this.props;
        const {getTableElement, el} = this;

        return (
            el('div', null, getTableElement(items, showContactColumn))
        );
    }
}

export default CustomerSRComponent;