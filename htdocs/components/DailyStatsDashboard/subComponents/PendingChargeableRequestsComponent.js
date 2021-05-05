import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import Table from "../../shared/table/table";
import {equal} from "../../utils/utils";
import APISDManagerDashboard from "../services/APISDManagerDashboard";
import moment from "moment";
import ToolTip from "../../shared/ToolTip";

export default class PendingChargeableRequestsComponent extends MainComponent {
    api = new APISDManagerDashboard();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            items: [],
            params: {},
            filter: {}
        };
    }

    componentDidMount() {
        this.getData();
    }

    componentDidUpdate(prevProps, prevState) {
        if (!equal(prevProps.filter, this.props.filter))
            this.getData();
    }

    getData() {
        const {hd, es, sp, p, limit} = this.props.filter;
        this.api.getPendingChargeableRequests(hd, es, sp, p, limit)
            .then(items => {
                this.setState({items, showModal: false, loadData: false});
            });
    }


    getDataTable = () => {
        const {items} = this.state;
        const columns = [
            {
                path: "serviceRequestId",
                label: "",
                hdToolTip: "Service Request",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: false,
                className: "text-center",
                content: (item) =>
                    <a href={`SRActivity.php?action=displayActivity&serviceRequestId=${item.serviceRequestId}`}
                       target="_blank"
                    >{item.serviceRequestId}</a>
            },
            {
                path: "customerName",
                label: "",
                hdToolTip: "Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: false,
            },
            {
                path: "requesteeName",
                label: "",
                hdToolTip: "Contact",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-id-card-alt color-gray2 pointer",
                sortable: false,
            },
            {
                path: "emailSubjectSummary",
                label: "",
                hdToolTip: "SR Description",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: false,
                className: "text-center",
            },
            {
                path: "reason",
                label: "",
                hdToolTip: "Request Reason",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: false,
                content: (item) => <div dangerouslySetInnerHTML={{__html: item.reason}}/>,
                className: "text-center",
            },
            {
                path: "createdAt",
                label: "",
                hdToolTip: "Requested At",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-monitor-heart-rate color-gray2 pointer",
                sortable: false,
                content: (item) =>
                    <div>
                        {moment(item.createdAt).format('DD/MM/YYYY HH:mm:ss')}
                    </div>,
                className: "text-center",
            },

            {
                path: "additionalHoursRequested",
                label: "",
                hdToolTip: "Hours Requested",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass  color-gray2 pointer",
                sortable: false,
                className: "text-center",
            },
            {
                path: "requesterName",
                label: "",
                hdToolTip: "Requested By",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
                sortable: false,

            },
            {
                path: "",
                label: "",
                hdToolTip: "",
                hdClassName: "text-center",
                sortable: false,
                content: (item) => {
                    return (
                        <div>
                            <ToolTip title="Cancel Request"
                                     style={{display: 'inline-block'}}
                            >
                                <a onClick={this.createCancelRequestHandle(item.id)}
                                   style={{marginRight: "1rem"}}
                                >
                                    <i className="fal fa-2x fa-trash-alt color-gray2 pointer"/>
                                </a>
                            </ToolTip>
                            <ToolTip title="Resend Request Email"
                                     style={{display: 'inline-block'}}
                            >
                                <a onClick={this.createResendRequestEmailHandle(item.id)}>
                                    <i className="fal fa-2x fa-redo color-gray2 pointer"/>
                                </a>
                            </ToolTip>
                        </div>
                    )
                }
            },
        ];

        return <div>

            <Table
                key="chargeableRequests"
                data={items || []}
                pk="id"
                columns={columns}
                search={false}
            >
            </Table>
        </div>;
    }

    createCancelRequestHandle = (id) => {
        return async () => {
            const response = await this.confirm('Are you sure you want to cancel this request?');
            if (!response) {
                return;
            }
            await this.api.cancelChargeableRequest(id);
            await this.alert('The request has been cancelled');
            await this.getData();
        }
    }

    createResendRequestEmailHandle = (id) => {
        return async () => {
            const response = await this.confirm('Are you sure you want to resend the email for this request?');
            if (!response) {
                return;
            }
            await this.api.resendChargeableRequestEmail(id);
            await this.alert('The email has been sent');
        }
    }

    render() {

        return <div>
            <Spinner key="spinner"
                     show={this.state.showSpinner}
            />
            {this.getDataTable()}
            {this.getConfirm()}
            {this.getAlert()}
        </div>
    }

}

  