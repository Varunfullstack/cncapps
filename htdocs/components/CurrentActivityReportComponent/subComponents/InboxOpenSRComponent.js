import APICustomers from "../../services/APICustomers";
import AutoComplete from "../../shared/AutoComplete/autoComplete.js";
import Table from "./../../shared/table/table";
import CurrentActivityService from "../services/CurrentActivityService";
import React from 'react';
import {ColumnRenderer} from "./ColumnRenderer";
import ToolTip from "../../shared/ToolTip";

class InboxOpenSRComponent extends React.Component {
    code = "OSR";
    el = React.createElement;
    apiCurrentActivityService;
    apiCustomer = new APICustomers();
    timeOut;

    constructor(props) {
        super(props);
        this.apiCurrentActivityService = new CurrentActivityService();
        this.state = {
            customers: [],
            data: this.props.data,
            customer: null,
            filter: {
                srNumber: '',
                customer: null
            }
        }
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
                        <i className={`fal fa-2x icon pointer color-gray fa-phone${problem.hasCallback ? '-plus' : ''}`}
                           onClick={() => this.props.onCallBack(problem)}
                        />
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
                content: (problem) => [
                    el(
                        "label",
                        {key: "label", style: {verticalAlign: "middle"}},
                        problem.hoursRemainingForSLA
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
                order: 6,
                path: "problemID",
                label: "",
                hdToolTip: "Service Request number",
                icon: "fal fa-2x fa-hashtag color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-right",
                content: (problem) =>
                    el(
                        "a",
                        {
                            href: `SRActivity.php?action=displayActivity&serviceRequestId=${problem.problemID}`,
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
            id: "helpDesk",
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
        return <div className="flex-row" style={{alignItems:"center"}}>
            <label>Customer</label>
            <AutoComplete
            errorMessage= "No Customer found"
            items={customers}
            displayLength={40}
            displayColumn= "name"
            pk= "id"
            width= {300}
            onSelect={ (customer) => this.setFilter('customer', customer)}
            ></AutoComplete>
            <label style={{marginLeft: 30, whiteSpace: "nowrap"}}>SR Number</label>
            <input
             className="form-control"
             onChange={(event) => this.setFilter('srNumber', event.target.value)}
            ></input>
        </div>        
    }
    setFilter = (field, value) => {
        const {filter} = this.state;
        filter[field] = value;
        this.setState({filter}, () => {
            if (field == 'srNumber' && value.length >= 4) {
                clearTimeout(this.timeOut);
                this.timeOut = setTimeout(() => this.handleOnCustomerSelect(), 1000);
            } else if (field == 'customer')
                this.handleOnCustomerSelect()
        });
    }
    handleOnCustomerSelect = () => {
        const {filter} = this.state;
        this.props.getCustomerOpenSR((filter.customer?.id || ''), filter.srNumber);

    }

    static getDerivedStateFromProps(props, current_state) {
        return {...current_state, ...props};
    }

    render() {
        const {getTableElement,} = this;
        return getTableElement();

    }
}

export default InboxOpenSRComponent;
