import Table from "./../../shared/table/table";
import CurrentActivityService from "../services/CurrentActivityService";
import MainComponent from '../../shared/MainComponent.js';

import React from 'react';

class InboxPendingReopenedComponent extends MainComponent {
    code = "PR";
    el = React.createElement;
    apiCurrentActivityService;

    constructor(props) {
        super(props);
        this.apiCurrentActivityService = new CurrentActivityService();
        this.state = {
            ...this.state,
            showEmailSubjectSummaryModal: false,
        }
    }

    addToolTip = (element, title) => {
        return this.el(
            "div",
            {className: "tooltip"},
            element,
            this.el("div", {className: "tooltiptext tooltip-bottom"}, title)
        );
    };
    processPendingReopened = async (problem, code) => {
        console.log(problem);
        if (code == 'R' && !(await this.confirm("Are you sure you want to reopen?"))) return;
        if (code == 'D' && !(await this.confirm("Are you sure you want to delete?"))) return;

        if (code == 'R' || code == 'D')
            this.apiCurrentActivityService.processPendingReopened(problem.pendingReopenedID, code).then(res => {
                this.props.loadQueue(this.code);
            })
        else if (code == 'N') {

            this.prompt('Please provide an email subject summary for this request', 500, null, false).then(value => {
                if (!value) {
                    return;
                }
                const data = {
                    action: 'editServiceRequestHeader',
                    contactID: problem.pendingReopenedContactID,
                    customerID: problem.pendingReopenedCustomerID,
                    reason: atob(problem.base64Reason),
                    pendingReopenedID: problem.pendingReopenedID,
                    deletePending: true,
                    raiseTypeId: 1,
                    emailSubjectSummary: value
                };
                window.location=`LogServiceRequest.php?pendingReopenedID=${problem.pendingReopenedID}&&emailSubjectSummary=${value}`;
                //this.redirectPost("Activity.php", data);
            })
        }
    }


    getTableElement = () => {
        const {el, addToolTip, processPendingReopened} = this;
        const {
            deleteSR,
            createNewSR,
            srCustomerDescription,
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
                content: (problem) =>
                    addToolTip(
                        el(
                            "div",
                            {key: "deleteIcon", onClick: () => processPendingReopened(problem, "D")},
                            el("i", {
                                className: "fal fa-trash-alt color-gray pointer inbox-icon",
                            })
                        ),
                        "Delete"
                    ),
            },
            {
                hide: false,
                order: 2,
                path: null,
                label: "",
                key: "reopen",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                content: (problem) =>
                    addToolTip(
                        el(
                            "div",
                            {key: "reopenIcon", onClick: () => processPendingReopened(problem, "R")},
                            el("i", {
                                className: "fal fa-redo color-gray pointer inbox-icon",
                            })
                        ),
                        "Reopen Service Request"
                    ),
            },
            {
                hide: false,
                order: 3,
                path: null,
                label: "",
                key: "new",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                content: (problem) =>
                    addToolTip(
                        el(
                            "div",
                            {key: "deleteIcon", onClick: () => processPendingReopened(problem, "N")},
                            el("i", {
                                className: "fal fa-plus color-gray pointer inbox-icon",
                            })
                        ),
                        "Create New Service Request"
                    ),
            },
            {
                hide: false,
                order: 4,
                path: "receivedDate",
                label: "",
                key: "receivedDate",
                sortable: false,
                className: "text-center nowrap",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-calendar-alt color-gray2 ",
                hdToolTip: "Date and time email was received",
                content: (problem) =>
                    el('span', null, moment(problem.receivedDate).format("DD/MM/YYYY HH:mm"))
            },
            {
                hide: false,
                order: 5,
                path: "pendingReopenSR",
                label: "",
                key: "pendingReopenSR",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 ",
                hdToolTip: "Service Request number",
                content: (problem) =>
                    el('a', {href: problem.pendingReopenSRURL, target: "_blank"}, problem.pendingReopenSR)
            },
            {
                hide: false,
                order: 6,
                path: "pendingReopenCustomerName",
                label: "",
                key: "pendingReopenCustomerName",
                sortable: false,
                className: "",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 ",
                hdToolTip: "Customer",

            },
            {
                hide: false,
                order: 7,
                path: "pendingReopenDescriptionSummary",
                label: "",
                key: "pendingReopenDescriptionSummary",
                sortable: false,
                className: "",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 ",
                hdToolTip: "Customer",
                content: (problem) =>
                    el('a', {
                        className: "pointer",
                        onClick: () => this.openPopup(`CurrentActivityReport.php?action=pendingReopenedPopup&pendingReopenedID=${problem.pendingReopenedID}&htmlFmt=popup`)
                    }, problem.pendingReopenDescriptionSummary)
            },
            {
                hide: false,
                order: 8,
                path: "pendingReopenPriority",
                label: "",
                key: "pendingReopenPriority",
                sortable: false,
                className: "text-center",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-signal color-gray2 ",
                hdToolTip: "Priority",

            },
            // {
            //   hide: false,
            //   order: 2,
            //   path: null,
            //   label: "",
            //   key: "New",
            //   sortable: false,
            //   className: "text-center",
            //   hdClassName: "text-center",
            //   backgroundColorColumn:"cpBgColor",
            //   content: (problem) =>
            //     addToolTip(
            //       el(
            //         "div",
            //         { key: "newIcon", onClick: () => createNewSR(problem, this.code) },
            //         el("i", {
            //           className:"fal fa-plus color-gray pointer inbox-icon",
            //         })
            //       ),
            //       "Create New Service Request"
            //     ),
            // },
            // {
            //   hide: false,
            //   order:3,
            //   path: "cpCustomerName",
            //   key: "cpCustomerName",
            //   label: "",
            //   sortable: false,
            //   backgroundColorColumn:"cpBgColor",
            //   content: (problem) =>
            //       el("a", {
            //           key: "customer",
            //           className:"pointer",
            //           href:problem.cpUrlCustomer
            //         },problem.cpCustomerName)

            // },
            // {
            //   hide: false,
            //   order:4,
            //   path: "cpTruncatedReason",
            //   key: "description",
            //   label: "",
            //   sortable: false,
            //   icon:"fal fa-file-alt fa-2x  color-gray2",
            //   hdToolTip: "Description of the Service Request",
            //   hdClassName: "text-center",
            //   backgroundColorColumn:"cpBgColor",
            //   content: (problem) =>
            //       el("a", {
            //           key: "desription",
            //           className:"pointer",
            //           onClick:()=>srCustomerDescription(problem)
            //         },problem.cpTruncatedReason)

            // },
            // {
            //   hide: false,
            //   order: 5,
            //   path: "cpDate",
            //   key: "cpDate",
            //   label: "",
            //   sortable: false,
            //   className:"nowrap",
            //   icon:"fal fa-calendar-alt fa-2x  color-gray2",
            //   hdToolTip: "Date and time email was received",
            //   hdClassName: "text-center",
            //   backgroundColorColumn:"cpBgColor",
            //   content:(problem)=>el('span',null,moment(problem.cpDate).format("DD/MM/YYYY HH:mm"))
            // },
            // {
            //   hide: false,
            //   order: 6,
            //   path: "cpServiceRequestID",
            //   key: "srId",
            //   label: "",
            //   sortable: false,
            //   icon:"fal fa-hashtag fa-2x  color-gray2",
            //   hdToolTip: "Service Request number",
            //   hdClassName: "text-center",
            //   backgroundColorColumn:"cpBgColor",
            //   content: (problem) =>
            //   el("a", {
            //       key: "cpUrlServiceRequest",
            //       target:"_blank",
            //       href:problem.cpUrlServiceRequest,
            //       className:"pointer",
            //      },problem.cpServiceRequestID)
            // },
            // {
            //   hide: false,
            //   order: 7,
            //   path: "cpPriority",
            //   key: "priority",
            //   label: "",
            //   sortable: false,
            //   icon:"fal fa-signal fa-2x  color-gray2",
            //   hdToolTip: "Service Request priority",
            //   hdClassName: "text-center",
            //   backgroundColorColumn:"cpBgColor",
            // },
        ];

        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.props;

        return el(Table, {
            id: "pendingReopend",
            data: data || [],
            columns: columns,
            pk: "pendingReopenSR",
            search: true,
        });
    };

    render() {
        const {getTableElement} = this;
        return (
            <div>
                {this.getConfirm()}
                {this.getPrompt()}
                {getTableElement()}
            </div>
        );

    }
}

export default InboxPendingReopenedComponent;
