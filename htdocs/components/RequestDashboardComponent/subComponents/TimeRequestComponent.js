import MainComponent from "../../shared/MainComponent";
import Table from "../../shared/table/table";
import React from 'react';
import APIRequestDashboard from "../services/APIRequestDashboard";
import Modal from "../../shared/Modal/modal";
import CNCCKEditor from "../../shared/CNCCKEditor";
import Spinner from "../../shared/Spinner/Spinner";

class TimeRequestComponent extends MainComponent {
    el = React.createElement;
    api;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            filter: props.filter,
            _mounted: false,
            showSpinner: false,
            activities: this.props.activities,
            showProcessTimeModal: false,
            currentTimeRequest: null,
            data: {
                status: null,
                allocatedTimeAmount: 'minutes',
                allocatedTimeValue: 0,
                comments: null,
                callActivityID: null
            }
        };
        this.api = new APIRequestDashboard();
    }

    static getDerivedStateFromProps(props, current_state) {
        return {...current_state, ...props};
    }

    getDataElement = () => {
        const {el} = this;
        const {activities} = this.state;
        const columns = [
            {
                path: "customerName",
                key: "customer",
                label: "",
                hdToolTip: "Customer Name",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: false,
            },
            {
                path: "problemID",
                label: "",
                hdToolTip: "Service Request Number",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                className: "text-center",
                classNameColumn: "",
                sortable: false,
                content: (problem) => el('a', {
                    href: `SRActivity.php?action=displayActivity&serviceRequestId=${problem.problemID}`,
                    target: '_blank'
                }, problem.problemID)
            },
            {
                path: "notes",
                label: "",
                key: "notes",
                hdToolTip: "Notes",
                icon: "fal fa-2x fa-file-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                width: 500
            },
            {
                path: "requesterTeam",
                label: "",
                key: "requesterTeam",
                hdToolTip: "Team of Requester",
                icon: "fal fa-2x fa-users color-gray2 ",
                hdClassName: "text-center",
                className: "text-center",
                sortable: false,
            },
            {
                path: "requestedBy",
                label: "",
                key: "requestedBy",
                hdToolTip: "Requester Name",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
            },
            {
                path: "requestedDateTime",
                label: "",
                key: "requestedDateTime",
                hdToolTip: "Requested Date & Time",
                icon: "fal fa-2x fa-calendar color-gray2 ",
                sortable: false,
                hdClassName: "text-center ",
                className: "text-center nowrap",
                content: (activity) => <span>{moment(activity.requestedDateTime).format("DD/MM/YYYY HH:mm")}</span>
            },
            {
                path: "teamManagementApprovalMinutes",
                label: "",
                key: "teamManagementApprovalMinutes",
                hdToolTip: "Approval Level",
                icon: "fal fa-2x fa-file-signature color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content: (request) => {
                    let text = "Team Lead"
                    if (request.timeSpentSoFar >= request.teamManagementApprovalMinutes) {
                        text = 'Mgmt';
                    }
                    return <span>{text}</span>;
                }
            },
            {
                path: "chargeableHours",
                label: "",
                key: "chargeableHours",
                hdToolTip: "Chargeable Hours",
                icon: "fal fa-2x fa-receipt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
            },
            {
                path: "timeSpentSoFar",
                label: "",
                key: "timeSpentSoFar",
                hdToolTip: "Time Spent So Far",
                icon: "fal fa-2x fa-clock color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
            },
            {
                path: "timeLeftOnBudget",
                label: "",
                key: "timeLeftOnBudget",
                hdToolTip: "Time Left On Budget",
                icon: "fal fa-2x fa-stopwatch color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
            },
            {
                path: "",
                label: "",
                key: "processTimeRequest",
                hdToolTip: "Process Time Request",
                icon: "fal fa-2x fa-alarm-plus color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content: (activity) => el('a', {
                    className: "fal fa-2x fa-alarm-plus color-gray inbox-icon pointer",
                    onClick: () => this.processTimeRequest(activity),
                })
            },
        ]

        return <Table
            key="timeRequest"
            id="timeRequestTable"
            data={activities}
            columns={columns}
            pk="callActivityID"
            search="true"
        />
    }

    processTimeRequest(timeRequest) {
        const {data} = this.state;
        data.comments = "";
        data.allocatedTimeValue = '';
        data.allocatedTimeAmount = "minutes";
        this.setState({showProcessTimeModal: true, currentTimeRequest: timeRequest, data});
        this.setValue("callActivityID", timeRequest.callActivityID);
    }

    renderNotAllowedError = (isAllowed) => {
        if (isAllowed) {
            return <tr/>;
        }

        return <tr>
            <td colSpan="2"
                style={{color: 'red'}}
            >You are not allowed to add additional minutes to this SR for the selected
                team, please discuss this with management to proceed.
            </td>
        </tr>
    }

    getTimeRequestModal = () => {
        const {showProcessTimeModal, data, currentTimeRequest} = this.state;
        const {allocatedTimeValue, allocatedTimeAmount} = data;
        if (!currentTimeRequest) {
            return '';
        }
        const isAllowed = isAdditionalTimeLimitApprover || (allocatedTimeValue * (allocatedTimeAmount === 'minutes' ? 1 : 60) + currentTimeRequest.timeSpentSoFar) < currentTimeRequest.teamManagementApprovalMinutes;
        return <Modal
            key="processRequestTime"
            show={showProcessTimeModal}
            width="700px"
            title="Time Request"
            onClose={this.handleCancel}
            footer={
                <div key="divFooter">
                    <button onClick={this.handleApprove} disabled={!isAllowed} >Approve</button>
                    <button onClick={this.handleDeny}>Deny</button>
                </div>
            }
        >
            <div key="divBody">
                <table>
                    <tbody>
                    <tr>
                        <td>Granted Minutes</td>
                        <td>
                            <input autoFocus={true}
                                   style={{marginLeft: 0}}
                                   type="number"
                                   onChange={($event) => this.setValue('allocatedTimeValue', parseInt($event.target.value))}
                                   value={allocatedTimeValue}
                            />
                            <select onChange={($event) => this.setValue('allocatedTimeAmount', $event.target.value)}
                                    value={allocatedTimeAmount}
                            >
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                            </select>
                        </td>
                    </tr>
                    {this.renderNotAllowedError(isAllowed)}
                    <tr style={{verticalAlign: "top"}}>
                        <td>Comments</td>
                        <td>
                            <div id="top2"/>
                            <CNCCKEditor
                                onChange={(receivedData) => this.setValue('comments', receivedData)}
                                style={{width: 600, height: 200}}
                                type="inline"
                                sharedSpaces={true}
                                top="top2"
                                bottom="bottom2"
                            >
                            </CNCCKEditor>
                            <div id="bottom2"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </Modal>
    }
    handleDeny = () => {
        const
            {
                data
            }
                = this.state;
        data.status = "Deny";
        if (!data.comments) {
            this.alert("Please enter a comment");
            return;
        }

        this.api.setTimeRequest(data).then(result => {
            if (result.status) {
                this.handleCancel()
                this.onRefresh();
            }
        });
    }
    handleCancel = () => {
        this.setState({
            showProcessTimeModal: false,
            data: {...this.state.data, allocatedTimeValue: '', allocatedTimeAmount: '', comments: ''}
        });
    }
    handleApprove = () => {
        const {data} = this.state;
        if (!data.allocatedTimeValue) {
            this.alert("Please enter Granted Time");
            return;
        }
        if (!parseInt(data.allocatedTimeValue)) {
            this.alert("Please enter a valid time value");
            return;
        }
        data.status = "Approve";
        this.api.setTimeRequest(data).then(result => {
            if (result.status) {
                this.handleCancel();
                this.onRefresh();
            }
        });
    }
    onRefresh = () => {
        if (this.props.onRefresh)
            this.props.onRefresh()
    }

    render() {
        return (
            <div>
                <Spinner key="spinner"
                         show={this.state.showSpinner}
                />
                {this.getAlert()}
                {this.getDataElement()}
                {this.getTimeRequestModal()}
            </div>
        )
    }
}

export default TimeRequestComponent;

 