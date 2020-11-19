import Table from "./../../shared/table/table";
import CurrentActivityService from "../services/CurrentActivityService";

import React from 'react';
import {ColumnRenderer} from "./ColumnRenderer";

class InboxSalesComponent extends React.Component {
    code = "S";
    el = React.createElement;
    apiCurrentActivityService;

    constructor(props) {
        super(props);
        this.apiCurrentActivityService = new CurrentActivityService();
    }

    addToolTip = (element, title) => {
        return this.el(
            "div",
            {className: "tooltip"},
            element,
            this.el("div", {className: "tooltiptext tooltip-bottom"}, title)
        );
    };
    getTableElement = () => {
        const {el, addToolTip} = this;
        const {
            getMoveElement,
            srDescription,
            allocateAdditionalTime,
            requestAdditionalTime,
            startWork,
            getAllocatedElement,
        } = this.props;
        let columns = [
            ColumnRenderer.getWorkIconColumn(startWork, this.code),
            ColumnRenderer.getSpecialAttentionColumn(),
            ColumnRenderer.getFutureWorkColumn(),
            {
                hide: true,
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
                content: (problem) => [
                    el(
                        "div",
                        {
                            key: "img1",
                            style: {display: "flex", flexDirection: "row", width: "100%", alignItems: "center"},
                            onClick: () => requestAdditionalTime(problem)
                        },
                        el(
                            "i",
                            {
                                className: "fal fa-2x fa-hourglass-end color-gray inbox-icon float-left",
                                style: {cursor: "pointer", width: 20},
                            },
                            ""
                        ),
                        el(
                            "span",
                            {
                                key: "span1",
                                className: "float-right",
                                style: {},
                            },
                            `${problem.minutesRemaining}`
                        )
                    ),
                ],
            },
           ColumnRenderer.getOnHoldColumn(),

            ColumnRenderer.getSLABreachedColumn(),
            {
                hide: false,
                order: 8,
                path: "hoursRemainingForSLA",
                key: "hoursRemainingLabel",
                label: "",
                hdToolTip: "Hours the Service Request has been open",
                icon: "fal fa-2x  fa-clock color-gray2 ",
                sortable: false,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (problem) => [
                    el(
                        "label",
                        {key: "label", style: {verticalAlign: "middle"}},
                        problem.hoursRemaining
                    ),
                ],
            },
            {
                hide: false,
                order: 5,
                path: null,
                label: "",
                key: "moverequest",
                hdToolTip: "Move Service Request to another queue",
                icon: "fal fa-2x fa-person-carry color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content: (problem) => getMoveElement(this.code, problem),
            },
            {
                hide: false,
                order: 6,
                path: "problemID",
                label: "",
                hdToolTip: "Service Request number",
                icon: "fal fa-2x fa-hashtag color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content: (problem) =>
                    el(
                        "a",
                        {
                            href: `Activity.php?action=displayLastActivity&problemID=${problem.problemID}`,
                            target: "_blank",
                            key: "link",
                        },
                        problem.problemID
                    ),
            },
            {
                hide: false,
                order: 7,
                path: "customerName",
                label: "",
                hdToolTip: "Customer",
                icon: "fal fa-2x fa-building color-gray2 ",
                sortable: false,
                width: "220",
                hdClassName: "text-center",
                content: (problem) =>
                    el(
                        "a",
                        {
                            href: `Customer.php?action=dispEdit&customerID=${problem.customerID}`,
                            target: "_blank",
                            key: "link",
                        },
                        problem.customerName
                    ),
            },
            ColumnRenderer.getPriorityColumn(),
            {
                hide: false,
                order: 12,
                path: "reason",
                label: "",
                hdToolTip: "Description of the Service Request",
                icon: "fal fa-2x fa-file-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                content: (problem) =>
                    el(
                        "a",
                        {
                            className: "pointer",
                            onClick: () => srDescription(problem),
                            dangerouslySetInnerHTML: {__html: problem.reason}
                        },
                    ),
            },
            {
                hide: false,
                order: 13,
                path: null,
                label: "",
                key: "assignedUser",
                hdToolTip: "Service Request is assigned to this person",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                sortable: false,
                hdClassName: "text-center",

                content: (problem) => getAllocatedElement(problem, this.code),
            },
        ];
        if (this.props?.currentUser?.isSDManger)
            columns.push({
                hide: false,
                order: 9,
                path: null,
                label: "",
                key: "additionalTime",
                hdToolTip: "Allocate additional time",
                icon: "fal fa-2x fa-alarm-plus color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                toolTip: "Allocate more time",
                content: (problem) =>
                    el(
                        "div",
                        {onClick: () => allocateAdditionalTime(problem)},
                        el("i", {
                            className: "fal fa-2x fa-hourglass-start color-gray inbox-icon",
                            //title: "Allocate more time",
                            style: {cursor: "pointer"},
                        })
                    ),
            });
        columns = columns
            .filter((c) => c.hide === false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.props;

        return el(Table, {
            id: "helpDesk",
            data: data || [],
            columns: columns,
            pk: "problemID",
            search: true,
        });
    };

    getSrByUsersSummaryElement = () => {
        const {el} = this;
        const {data} = this.props;
        if (data) {
            const future = data.filter((p) => moment(p.alarmDateTime) > moment())
                .length;
            if (data.length > 0) {
                const items = data
                    .reduce((prev, current) => {
                        //check index
                        const index = prev.findIndex(
                            (p) => p.name === current.engineerName
                        );
                        if (index === -1)
                            prev.push({name: current.engineerName, total: 1});
                        else prev[index].total += 1;
                        return prev;
                    }, [])
                    .map((p) => {
                        if (p.name != null && p.name !== "") {
                            p.name = p.name.replace("  ", " ");
                            const arr = p.name.split(" ");
                            p.name = arr[0][0] + arr[1][0];
                        }
                        return p;
                    })
                    .sort((a, b) => (a.name > b.name ? 1 : -1))
                    .map((item) => {
                        return [
                            el(
                                "dt",
                                {key: "name", style: {paddingLeft: 10}},
                                (item.name || "Unassigned") + ":"
                            ),
                            el("dd", {key: "total"}, item.total),
                        ];
                    })
                    .concat([
                        el(
                            "dt",
                            {key: "name", style: {paddingLeft: 10}},
                            "Future" + ":"
                        ),
                        el("dd", {key: "total"}, future),
                    ]);

                return [
                    ...[
                        el(
                            "dt",
                            {key: "nameFuture", style: {paddingLeft: 10}},
                            "Total" + ":"
                        ),
                        el("dt", {key: "totalFuture"}, data.length),
                    ],
                    ...items,
                ];
            }
        }
        return null;
    };

    render() {
        const {el, getTableElement, getSrByUsersSummaryElement} = this;
        const {data} = this.props;


        return [
            el(
                "div",
                {key: "summary", style: {display: "flex", flexDirection: "row"}},
                getSrByUsersSummaryElement()
            ),
            getTableElement(),
        ];
    }
}

export default InboxSalesComponent;
