import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import {dateFormatExcludeNull, exportCSV, SRQueues} from "../../utils/utils";
import CurrentActivityService from "../../CurrentActivityReportComponent/services/CurrentActivityService";

class DetailsComponent extends MainComponent {

    apiCurrentActivityService = new CurrentActivityService();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            allocatedUsers: []
        }
    }

    componentWillUnmount() {

    }

    componentDidMount() {
        this.apiCurrentActivityService
            .getAllocatedUsers()
            .then((res) => this.setState({allocatedUsers: res}));
    }

    handleUserOnSelect = (engineerName, serviceRequestID) => {
        const {allocatedUsers} = this.state;
        const foundEngineer = allocatedUsers.find(x => x.userName === engineerName);
        const engineerId = foundEngineer.userID;
        this.apiCurrentActivityService
            .allocateUser(serviceRequestID, engineerId)
            .then(() => {
                if (this.props.onChange) {
                    this.props.onChange();
                }
            })

    };

    getAllocatedElement = (serviceRequest) => {
        const {handleUserOnSelect} = this;
        const {allocatedUsers} = this.state;
        const teamData = {
            'Helpdesk': {id: 1, title: 'Helpdesk'},
            'Escalations': {id: 2, title: 'Escalations',},
            'Small Projects': {id: 4, title: 'Small Projects'},
            'Projects': {id: 5, title: 'Projects'},
            'Sales': {id: 7, title: 'Sales',},
        }

        const teamId = teamData[serviceRequest.teamName]?.id;
        const currentTeam = allocatedUsers.filter((u) => u.teamID == teamId);
        const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamId);
        return (
            <select

                key={"allocatedUser"}
                value={serviceRequest.assignedTo || ""}
                style={{width: '120px'}}
                onChange={(event) => handleUserOnSelect(event.target.value, serviceRequest.serviceRequestID)}
            >
                <option key="unassigned"/>
                {
                    [...currentTeam, ...otherTeams].map(p => {
                        return <option value={p.userName}
                                       className={teamId === p.teamID ? "in-team" : ''}
                                       key={`option-${p.userID}`}
                        >
                            {p.userName}
                        </option>
                    })
                }
            </select>
        );
    };

    getDetailsElement = () => {
        const {data} = this.props;
        const columns = [
            {
                path: "customer",
                label: "",
                hdToolTip: "Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: true,
                //className: "text-center",
                classNameColumn: "rowClass",
                //classNameColumn:"",
                //toolTip:"",
                //textColorColumn:"",
                //allowRowOrder:false,
                //onOrderChange:(row)=>null,
            },
            {
                path: "serviceRequestID",
                label: "",
                hdToolTip: "SR No",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: true,
                className: "text-center",
                classNameColumn: "rowClass",
                content: (sr) => <a target="_blank"
                                    href={`SRActivity.php?action=displayActivity&serviceRequestId=${sr.serviceRequestID}`}
                >{sr.serviceRequestID}</a>
            },
            {
                path: "description",
                label: "",
                hdToolTip: "Details",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                className: '',
                content: (sr) => sr.description ? sr.description.substr(0, 300) : ""

            },
            {
                path: "assignedTo",
                label: "",
                hdToolTip: "Assigned to",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                content: (sr) => {
                    return this.getAllocatedElement(sr)
                }

            },
            {
                path: "teamName",
                label: "",
                hdToolTip: "Team",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-users color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                //className: "text-center",
                content: (sr) => <label>{this.getQueueName(sr.queueNo)}</label>

            },
            {
                path: "durationHours",
                label: "",
                hdToolTip: "Open For(days)",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-clock color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                //className: "text-center",
            },
            {
                path: "timeSpentHours",
                label: "",
                hdToolTip: "Time Spent(hours)",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-stopwatch color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                //className: "text-center",
            },
            {
                path: "priority",
                label: "",
                hdToolTip: "Priority",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                //className: "text-center",
            },
            {
                path: "lastUpdatedDate",
                label: "",
                hdToolTip: "Last Updated",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-calendar  color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                content: order => dateFormatExcludeNull(order.lastUpdatedDate)
            },
            {
                path: "awaiting",
                label: "",
                hdToolTip: "Status",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-monitor-heart-rate color-gray2 pointer",
                sortable: true,
                classNameColumn: "rowClass",
                //className: "text-center",
            },
        ]
        return <Table id="details"
                      data={data || []}
                      columns={columns}
                      pk="serviceRequestID"
                      search={true}
                      striped={false}
                      defaultSortPath="durationHours"
                      defaultSortOrder="desc"
        >
        </Table>;
    }

    getQueueName(id) {
        const indx = SRQueues.map(s => s.id).indexOf(parseInt(id));
        if (indx >= 0)
            return SRQueues[indx].name;
        else return "";
    }

    handleCSV = () => {
        const {data} = this.props;
        const dataMap = data.map(s => {
            return {
                'Customer': s.customer,
                'SR No': s.serviceRequestID.toString(),
                'Details': s.description,
                'Assigned To': s.assignedTo,
                'Team': s.teamName,
                'Open For(days)': s.durationHours.toString(),
                'Time Spent(hours)': s.timeSpentHours.toString(),
                'Priority': s.priority.toString(),
                'Last Updated': s.lastUpdatedDate,
                'Awaiting': s.awaiting
            }
        })
        exportCSV(dataMap, 'Aged Service Requests.csv');
    }
    getAverageAgeDays = () => {

        const {data} = this.props;
        const sum = data.reduce((prev, curr) => {
            prev += parseFloat(curr.durationHours);
            return prev;
        }, 0);
        const avg = sum / data.length;
        return avg.toFixed(1);
    }

    render() {
        return <div>
            <div style={{display: "flex", flexDirection: "row", justifyContent: "space-between", width: 400}}>
                <button className="btn-sm"
                        onClick={this.handleCSV}
                >CSV
                </button>
                <h3>Total Requests :{this.props.data.length}</h3>
                <h3>Average Age in Days :{this.getAverageAgeDays()}</h3>
            </div>

            {
                this.getDetailsElement()
            }
        </div>;
    }
}

export default DetailsComponent;