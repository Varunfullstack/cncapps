import CurrentActivityService from "../services/CurrentActivityService";
import React from "react";
import ToolTip from "../../shared/ToolTip";
import MainComponent from "../../shared/MainComponent";
import Table from "../../shared/table/table";

class CallBackComponent extends MainComponent {
    apiCurrentActivityService = new CurrentActivityService();
    dataInterval;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            callbacks: []
        }
    }

    componentDidMount() {
        this.getData()
        this.dataInterval = setInterval(() => this.getData(), 1000 * 30)
    }

    componentWillUnmount() {
        if (this.dataInterval)
            clearTimeout(this.dataInterval);
    }

    componentDidUpdate(prevProps, prevState) {
        if (prevProps.team != this.props.team || prevProps.customerID != this.props.customerID)
            this.getData();
    }

    getData = () => {
        this.apiCurrentActivityService.getMyCallback(this.props.team, this.props.customerID)
            .then(callbacks => {
                this.setState({callbacks});
            });
    }
    getCallBackContact = (callback) => {
        if (callback.useID == null)
            return <ToolTip title="This SR has not been assigned to an engineer"
                            width={30}
            >
                <i className="fal fa-2x fa-user-slash color-gray icon pointer"
                   onClick={() => this.createCustomerContact(callback)}
                />
            </ToolTip>;
        else
            return <ToolTip title="Create customer contact"
                            width={30}
            >
                <i className="fal fa-2x fa-phone color-gray icon pointer"
                   onClick={() => this.createCustomerContact(callback)}
                />
            </ToolTip>
    }
    getContent = () => {
        const {callbacks} = this.state;
        if (!callbacks || callbacks.length == 0)
            return null;
        const columns = [
            {
                path: "customerContact",
                label: "",
                hdToolTip: " ",
                hdClassName: "text-center",

                sortable: false,
                content: (callback) => <div className="flex-row"
                                            style={{justifyContent: "center"}}
                >
                    {this.getCallBackContact(callback)}
                </div>,
                className: "text-center",
            },
            {
                path: "problemID",
                label: "",
                hdToolTip: "Service Request",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: true,
                className: "text-center"
            },

            {
                path: "customerName",
                label: "",
                hdToolTip: "Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: true,
                //className: "text-center",
            },
            {
                path: "contactName",
                label: "",
                hdToolTip: "Contact",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-id-card-alt color-gray2 pointer",
                sortable: true,
                //className: "text-center",
            },
            {
                path: "DESCRIPTION",
                label: "",
                hdToolTip: "Reason for call back",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: true,
                className: "text-center",
            },
            {
                path: "callback_datetime",
                label: "",
                hdToolTip: "Call back date time",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass color-gray2 pointer",
                sortable: true,
                content: (problem) => <div>{this.getCorrectDate(problem.callback_datetime, true)}</div>,
                className: "text-center",
            },
            // {
            //   path: "timeRemainIcon",
            //   label: "",
            //   hdToolTip: "Time Remain",
            //   hdClassName: "text-center",
            //   icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
            //   sortable: false,
            //   width:30,
            //   content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>
            //   <ToolTip title="Time Remain" width={30}>
            //       <i className="fal fa-2x fa-hourglass color-gray2 pointer"></i>
            //   </ToolTip>
            //   </div>,
            //   className: "text-center",
            // },
            // {
            //   path: "timeRemain",
            //   label: "",
            //   hdToolTip: "Time Remain",
            //   hdClassName: "text-center",
            //   icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
            //   sortable: false,
            //   //className: "text-center",
            // },
            {
                path: "",
                label: "",
                hdToolTip: " ",
                hdClassName: "text-center",

                sortable: false,
                content: (problem) => <div className="flex-row"
                                           style={{justifyContent: "center"}}
                >
                    {problem.timeRemain < 0 ?
                        <ToolTip title="Call back time expired"
                                 width={30}
                        >
                            <i className="fal fa-2x fa-alarm-exclamation color-gray icon pointer"/>
                        </ToolTip> : null}
                </div>,
                className: "text-center",
            },
            {
                path: "CancelCallback",
                label: "",
                hdToolTip: " ",
                hdClassName: "text-center",

                sortable: false,
                content: (problem) => <div className="flex-row"
                                           style={{justifyContent: "center"}}
                >
                    <ToolTip title="Cancel call back"
                             width={30}
                    >
                        <i className="fal fa-2x fa-phone-slash color-gray icon pointer"
                           onClick={() => this.cancelCallBack(problem)}
                        />
                    </ToolTip>
                </div>,
                className: "text-center",
            },
        ];

        return <div style={{width: 800}}>
            <h3>Call back</h3>
            <Table
                key="callback"
                data={callbacks || []}
                pk="id"
                columns={columns}
                search={false}
            >
            </Table>
        </div>
    }
    createCustomerContact = (callback) => {
        this.apiCurrentActivityService.updateCallBackStatus(callback.id, 'contacted').then(result => {
            if (result.status) {
                this.getData();
                if (result.callActivityID != null)
                    window.open(`SRActivity.php?action=editActivity&callActivityID=${result.callActivityID}&isFollow=1`, "_blank")
            }

        })
    }
    cancelCallBack = async (callback) => {
        const reason = await this.prompt("Reason for not calling back");
        if (reason !== false && reason != '' && reason != null) {
            this.apiCurrentActivityService.cancelCallBack(callback.id, reason)
                .then(result => {
                    if (result.status) {
                        this.getData();
                    }
                })
        }

    }

    render() {
        return <div>
            {this.getPrompt()}
            {this.getContent()}
        </div>

    }
}

export default CallBackComponent;
