import MainComponent from "../../shared/MainComponent";
import Table from "../../shared/table/table";
import Toggle from "../../shared/Toggle";
import ToolTip from "../../shared/ToolTip";
import React from 'react';
import ReactDOM from 'react-dom';
import Spinner from "../../shared/Spinner/Spinner";
import APIRequestDashboard from "../services/APIRequestDashboard";
import Modal from "../../shared/Modal/modal";
import CNCCKEditor from "../../shared/CNCCKEditor";
 
class TimeRequestComponent extends MainComponent {
    el = React.createElement;
    api;
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            filter:props.filter,
            _mounted:false,
            showSpinner: false,
            activities:this.props.activities,
            showProcessTimeModal:false,
            currentActivity:null,
            data:{
                status:null,
                allocatedTimeAmount:'minutes',
                allocatedTimeValue:0,
                comments:null,
                callActivityID:null
            }
        };
        this.api=new APIRequestDashboard();
    }
    
    componentWillReceiveProps(nextProps) {
        ////console.log('nextProps',nextProps);        
        this.setState({activities:nextProps.activities});        
      }
    componentDidMount() { 
    }
    // loadData()
    // {
    //     const {filter}=this.state;
    //     if(filter!=null)
    //     {
    //         this.setState({showSpinner:true});
    //         this.api.getTimeRequest(filter).then(activities=>{
    //             //console.log(activities);
    //             this.setState({activities,showSpinner:false});
    //         })
    //     }
    // }
    getDataElement=()=>{
        const {el}=this;
        const {activities}=this.state;
        const columns=[
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
                width:500
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
                content:(activity)=><span>{moment(activity.requestedDateTime).format("DD/MM/YYYY HH:mm")}</span>
             }
             
             ,
             {
                path: "approvalLevel",
                label: "",
                key: "approvalLevel",
                hdToolTip: "Approval Level",
                icon: "fal fa-2x fa-file-signature color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
             }
             ,
             {
                path: "chargeableHours",
                label: "",
                key: "chargeableHours",
                hdToolTip: "Chargeable Hours",
                icon: "fal fa-2x fa-receipt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
             }
             ,
             {
                path: "timeSpentSoFar",
                label: "",
                key: "timeSpentSoFar",
                hdToolTip: "Time Spent So Far",
                icon: "fal fa-2x fa-clock color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
             }
             ,
             {
                path: "timeLeftOnBudget",
                label: "",
                key: "timeLeftOnBudget",
                hdToolTip: "Time Left On Budget",
                icon: "fal fa-2x fa-stopwatch color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
             }
             ,
             {
                path: "",
                label: "",
                key: "processTimeRequest",
                hdToolTip: "Process Time Request",
                icon: "fal fa-2x fa-alarm-plus color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center",
                content:(activity)=>el('a', {
                    className: "fal fa-2x fa-alarm-plus color-gray inbox-icon pointer",
                    onClick:()=>this.processTimeRequest(activity),
                })
             }
        ]
       
    return <Table
            key="timeRequest"
            id="timeRequestTable"
            data={activities}
            columns={columns}
            pk="callActivityID"
            search="true"
            ></Table>
    }
    processTimeRequest(activity){
        //console.log(activity);
        this.setState({showProcessTimeModal:true,currentActivity:activity});
        this.setValue("callActivityID",activity.callActivityID);
    }
    getTimeRequestModal=()=>{
        const {el} = this;
        const {types} = this.state;
        return el(Modal, {
            key: "processRequestTime",
            show: this.state.showProcessTimeModal,
            width: 720,
            title: "Time Request",
            onClose: this.handleCancel,
            content: <div    key="divBody">
                <table>
                    <tbody style={{whiteSpace:"nowrap"}}>
                        <tr>
                            <td>Granted Minutes	</td>
                            <td>
                                <input autoFocus="true" type="number" style={{marginLeft: 0}} onChange={($event)=>this.setValue('allocatedTimeValue',$event.target.value)} value={this.state.data.allocatedTimeValue}></input>
                                <select onChange={($event)=>this.setValue('allocatedTimeAmount',$event.target.value)} value={this.state.data.allocatedTimeAmount}>
                                    <option value="minutes">Minutes</option>
                                    <option  value="hours">Hours</option>
                                </select>
                            </td>
                        </tr>
                        <tr style={{verticalAlign:"top"}}>
                            <td >Comments</td>
                            <td>
                                <div id="top2"></div>
                                <CNCCKEditor                                 
                                    onChange={($event)=>this.setValue('comments',$event.editor.getData())}
                                    style={{width:600, height:200}}
                                    type="inline"
                                    sharedSpaces={true}
                                    top="top2"
                                    bottom="bottom2"
                                >
                                </CNCCKEditor>
                                <div id="bottom2"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>,
            footer: el(
                "div",
                {key: "divFooter"},
                el("button", {onClick: this.handleApprove}, "Approve"),
                el("button", {onClick: this.handleDeny}, "Deny"),
            ),
        });
    }
    handleDeny=()=>{
        const {data}=this.state;
        data.status="Deny";        
        this.api.setTimeRequest(data).then(result=>{
            if(result.status)
            {
                this.setState({showProcessTimeModal:false});
                this.onRefresh();
            }
            //console.log(result);
        });
    }
    handleCancel=()=>{
        this.setState({showProcessTimeModal:false});
    }
    handleApprove=()=>{
        if(this.state.data.allocatedTimeValue==0||this.state.data.allocatedTimeValue<=0)
        {
        this.alert("Please enter Granted Time");
        return;
        }
        const {data}=this.state;
        data.status="Approve";        
        this.api.setTimeRequest(data).then(result=>{
            if(result.status)
            {
                this.setState({showProcessTimeModal:false});
                this.onRefresh();
            }
            //console.log(result);
        });
        //console.log("approve",this.state.data);
    }
    onRefresh=()=>{
        if(this.props.onRefresh)
        this.props.onRefresh()
    }
    render() {
        const {el} = this;
        return el("div", null,                        
            el(Spinner, {key: "spinner", show: this.state.showSpinner}),
            this.getAlert(),
            this.getDataElement(),
            this.getTimeRequestModal()
        );
    }
}

export default TimeRequestComponent;

 