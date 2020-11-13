import APICustomers from "../../services/APICutsomer";
import AutoComplete from "../../shared/AutoComplete/autoComplete.js";
import Table from "./../../shared/table/table";
import CurrentActivityService from "../services/CurrentActivityService";
import React from 'react';

class InboxOpenSRComponent extends React.Component {
    code = "OSR";
    el = React.createElement;
    apiCurrentActivityService;
    apiCustomer = new APICustomers();

    constructor(props) {
        super(props);
        this.apiCurrentActivityService = new CurrentActivityService();
        this.state = {customers: [], data: this.props.data, customer: null}
    }

    componentDidMount() {
        this.apiCustomer.getCustomerHaveOpenSR().then(customers => {
            this.setState({customers})
        })
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
            {
                hide: false,
                order: 1,
                path: null,
                label: "",
                key: "work",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                content: (problem) =>
                    addToolTip(
                        el(
                            "div",
                            {key: "img1", onClick: () => startWork(problem, this.code)},
                            el("i", {
                                className:
                                    (problem.workBtnColor === "#C6C6C6"
                                        ? "fal fa-play"
                                        : "fad fa-play ") +
                                    " fa-2x  pointer inbox-icon" +
                                    problem.workHidden || "",
                                style: {
                                    color: problem.workBtnColor,
                                    "--fa-primary-color":
                                        problem.workBtnColor == "#FFF5B3" ? "gold" : "#32a852",
                                    "--fa-secondary-color":
                                        problem.workBtnColor == "#FFF5B3" ? "gray" : "gray",
                                },
                            })
                        ),
                        problem.workBtnTitle
                    ),
            },
            {
                hide: false,
                order: 2,
                path: null,
                key: "custsomerIcon",
                label: "",
                sortable: false,
                toolTip: "Special Attention customer / contact",
                content: (problem) =>
                    problem.customerNameDisplayClass != null
                        ? el("i", {
                            className:
                                "fal fa-2x fa-star color-gray pointer float-right inbox-icon",
                            key: "starIcon",
                        })
                        : null,
            },
            {
                hide: false,
                order: 4.1,
                path: null,
                key: "Future Icon",
                label: "",
                sortable: false,
                content: (problem) =>
                    moment(problem.alarmDateTime) > moment()
                        ? addToolTip(
                        el("i", {
                            className:
                                "fal fa-2x fa-alarm-snooze color-gray pointer float-right inbox-icon",
                            key: "starIcon",
                        }),
                        `This Service Request is scheduled for the future date of ${moment(
                            problem.alarmDateTime
                        ).format("DD/MM/YYYY HH:mm")}`
                        )
                        : null,
            },
            {
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
                            `${problem.hdRemaining}`
                        )
                    ),

                ],
            },
            {
                hide: false,
                order: 3,
                path: null,
                key: "hoursRemainingIcon",
                label: "",
                sortable: false,
                toolTip: "On Hold",
                className: "text-center",
                content: (problem) =>
                    problem.hoursRemainingBgColor === "#BDF8BA"
                        ? el("i", {
                            className: "fal  fa-user-clock color-gray pointer inbox-icon",
                            //title: "On Hold",
                            key: "icon",
                            style: {float: "right"},
                        })
                        : null,
            },

            {
                hide: false,
                order: 4,
                path: null,
                key: "problemIdIcon",
                label: "",
                sortable: false,
                className: "text-center",
                toolTip: "SLA Failed for this Service Request",
                content: (problem) =>
                    problem.bgColour == "#F8A5B6"
                        ? el("i", {
                            className:
                                "fal fa-2x fa-bell-slash color-gray pointer inbox-icon",
                            title: "",
                            key: "icon",
                        })
                        : null,
            },
            {
                hide: false,
                order: 8,
                path: "hoursRemaining",
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
                content: (problem) => getMoveElement(this.code, problem, problem.queueNo),
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
            {
                hide: false,
                order: 11,
                path: "priority",
                label: "",
                hdToolTip: "Service Request Priority",
                icon: "fal fa-2x fa-signal color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                backgroundColorColumn: "priorityBgColor"

            },
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
                            style: {cursor: "pointer"},
                        })
                    ),
            });
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.state;
        return el(Table, {
            key: "helpDesk",
            data: data || [],
            columns: columns,
            pk: "problemID",
            search: false,
            searchControls: this.getOtherSearchElement()
        });
    };
    getOtherSearchElement = () => {
        const {el} = this;
        const {customers} = this.state;
        return el('div', {className: "flex-row"}, "Customer",
            el(AutoComplete, {
                errorMessage: "No Customer found",
                items: customers,
                displayLength: "40",
                displayColumn: "name",
                pk: "id",
                width: 300,
                onSelect: this.handleOnCustomerSelect,
            })
        )
    }
    handleOnCustomerSelect = (customer) => {
        console.log(customer);
        if (customer != null) {
            this.props.getCustomerOpenSR(customer.id);
        }
    }

    static getDerivedStateFromProps(props, current_state) {
        return {...current_state, ...props};
    }

    render() {
        const {getTableElement,} = this;
        return [
            getTableElement(),
        ];
    }
}

export default InboxOpenSRComponent;
