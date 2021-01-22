import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import {dateFormatExcludeNull, exportCSV, SRQueues} from "../../utils/utils";

class DetailsComponent extends MainComponent {

    constructor(props) {
        super(props);

    }

    componentWillUnmount() {

    }

    componentDidMount() {

    }

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
                //className: "text-center",

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