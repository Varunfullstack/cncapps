import Table from "./../../shared/table/table";
import CurrentActivityService from "../services/CurrentActivityService";

import React from 'react';

class InboxToBeLoggedComponent extends React.Component {
    code = "TBL";
    el = React.createElement;
    apiCurrentActivityService;

    constructor(props) {
        super(props);
        this.apiCurrentActivityService = new CurrentActivityService();
    }

    addToolTip = (element, title) => {
        return this.el(
            "div",
            {className: "tooltip", key:title},
            element,
            this.el("div", {className: "tooltiptext tooltip-bottom"}, title)
        );
    };
    getTableElement = () => {
        const {el, addToolTip} = this;
        const {
            deleteSR,
            createNewSR,
            srCustomerDescription,
            assignToRequest
        } = this.props;
        let columns = [
            {
                hide: false,
                order: 1,
                path: null,
                label: "",
                key: "delete",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                backgroundColorColumn: "cpBgColor",
                content: (problem) =>
                    addToolTip(
                        el(
                            "div",
                            {key: "deleteIcon", onClick: () => deleteSR(problem, this.code)},
                            el("i", {className: "fal fa-trash-alt color-gray pointer inbox-icon",})
                        ),
                        "Delete"
                    ),
            },
            {
                hide: false,
                order: 1.1,
                path: null,
                label: "",
                key: "assignToRequest",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                backgroundColorColumn: "cpBgColor",
                content: (problem) =>
                    addToolTip(
                        <div onClick={() => assignToRequest(problem)}>
                            <i className="fal fa-file-plus color-gray pointer inbox-icon"/></div>,
                        "Add to existing Service Request"
                    ),
            },
            {
                hide: false,
                order: 2,
                path: null,
                label: "",
                key: "createNewRequest",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                backgroundColorColumn: "cpBgColor",
                content: (problem) =>
                    addToolTip(
                        <div onClick={() => createNewSR(problem, this.code)}>
                            <i className="fal fa-plus color-gray pointer inbox-icon"/></div>,
                        "Create New Service Request"
                    ),
            },
            {
                hide: false,
                order: 3,
                path: "cpCustomerName",
                key: "cpCustomerName",
                label: "",
                sortable: false,
                backgroundColorColumn: "cpBgColor",
                content: (problem) =>
                    el("a", {
                        key: "customer",
                        className: "pointer",
                        href: problem.cpUrlCustomer
                    }, problem.cpCustomerName)

            },
            {
                hide: false,
                order: 7,
                path: "cpTruncatedReason",
                key: "description",
                label: "",
                sortable: false,
                icon: "fal fa-file-alt fa-2x  color-gray2",
                hdToolTip: "Description of the Service Request",
                hdClassName: "text-center",
                backgroundColorColumn: "cpBgColor",
                content: (problem) =>
                    el("a", {
                        key: "desription",
                        className: "pointer",
                        onClick: () => srCustomerDescription(problem)
                    }, problem.cpTruncatedReason)

            },
            {
                hide: false,
                order:6,
                path: "emailSubject",
                key: "emailSubject",
                label: "",
                sortable: false,
                icon: "fal fa-envelope fa-2x  color-gray2",
                hdToolTip: "Email subject",
                hdClassName: "text-center",
            },
            {
                hide: false,
                order: 4,
                path: "cpDate",
                key: "cpDate",
                label: "",
                sortable: false,
                className: "nowrap",
                icon: "fal fa-calendar-alt fa-2x  color-gray2",
                hdToolTip: "Date and time email was received",
                hdClassName: "text-center",
                backgroundColorColumn: "cpBgColor",
                content: (problem) => el('span', null, moment(problem.cpDate).format("DD/MM/YYYY HH:mm"))
            },
            {
                hide: false,
                order: 5,
                path: "cpServiceRequestID",
                key: "srId",
                label: "",
                sortable: false,
                icon: "fal fa-hashtag fa-2x  color-gray2",
                hdToolTip: "Service Request number",
                hdClassName: "text-center",
                backgroundColorColumn: "cpBgColor",
                content: (problem) =>
                    el("a", {
                        key: "cpUrlServiceRequest",
                        target: "_blank",
                        href: problem.cpUrlServiceRequest,
                        className: "pointer",
                    }, problem.cpServiceRequestID)
            },
            {
                hide: false,
                order: 8,
                path: "cpPriority",
                key: "priority",
                label: "",
                sortable: false,
                icon: "fal fa-signal fa-2x  color-gray2",
                hdToolTip: "Service Request priority",
                hdClassName: "text-center",
                backgroundColorColumn: "cpBgColor",
            },
        ];

        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.props;

        return el(Table, {
            id: "tobeLogged",
            data: data || [],
            columns: columns,
            key:"toBeLogged",
            pk: "cpCustomerProblemID",
            search: true,
        });
    };

    render() {
        const {getTableElement} = this;
        return [
            getTableElement(),
        ];
    }
}

export default InboxToBeLoggedComponent;
