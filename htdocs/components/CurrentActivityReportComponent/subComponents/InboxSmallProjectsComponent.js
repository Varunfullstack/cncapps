import Table from "./../../shared/table/table";
import CurrentActivityService from "../services/CurrentActivityService";

import React, {Fragment} from 'react';
import {ColumnRenderer} from "./ColumnRenderer";
import {ServiceRequestSummary} from "./ServiceRequestSummary";
import ToolTip from "../../shared/ToolTip";

class InboxSmallProjectsComponent extends React.Component {
    code = "SP";
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
        const {el} = this;
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
                order: 0.9,
                path: null,
                label: "",
                key: "CallBack",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content: (problem) =>
                <ToolTip title="Call back">
                    <i className="fal fa-2x fa-phone icon pointer color-gray" onClick={()=>this.props.onCallBack(problem)}></i>
                </ToolTip>
                   
            },
            ColumnRenderer.getWorkIconColumn(startWork, this.code),
            ColumnRenderer.getSpecialAttentionColumn(),
            ColumnRenderer.getFixSLAWarningColumn(),
            ColumnRenderer.getFutureWorkColumn(),
            ColumnRenderer.getRequestTimeColumn(requestAdditionalTime),
            ColumnRenderer.getOnHoldColumn(),
            ColumnRenderer.getSLABreachedColumn(),
            {
                hide: false,
                order: 8,
                path: "hoursRemainingForSLA",
                key: "hoursRemainingForSLA",
                label: "",
                hdToolTip: "Hours the Service Request has been open",
                icon: "fal fa-2x  fa-clock color-gray2 ",
                sortable: false,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (problem) => (
                    <label style={{verticalAlign: "middle"}}>
                        {problem.hoursRemainingForSLA}
                    </label>
                )
            },
            {
                hide: false,
                order: 11.5,
                path: "contactName",
                key: 'contactName',
                label: "",
                hdToolTip: "Contact",
                icon: "fal fa-2x fa-id-card-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
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
                content: (problem) => (
                    <a
                        href={`SRActivity.php?action=displayActivity&serviceRequestId=${problem.problemID}`}
                        target="_blank"
                        key="link"
                    >
                        {problem.problemID}
                    </a>
                )
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
                path: "emailSubjectSummary",
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
                            dangerouslySetInnerHTML: {__html: problem.emailSubjectSummary}
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
        if (this.props?.currentUser?.isSDManager)
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
                content: (problem) => {
                    return (
                        <div onClick={() => allocateAdditionalTime(problem)}>
                            <i className="fal fa-2x fa-hourglass-start color-gray inbox-icon"
                               style={{cursor: "pointer"}}
                            />
                        </div>
                    )
                }
            });
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.props;

        return <Table id="helpDesk"
                      data={data || []}
                      columns={columns}
                      pk="problemID"
                      search={true}
        />
    };

    render() {
        const {data} = this.props;
        return (
            <Fragment>
                <ServiceRequestSummary data={data}/>
                {this.getTableElement()}
            </Fragment>
        )
    }
}

export default InboxSmallProjectsComponent;
