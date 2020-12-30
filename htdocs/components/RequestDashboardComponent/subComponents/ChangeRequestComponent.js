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
//import './../../style.css';
class ChangeRequestComponent extends MainComponent {
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
                comments:null,
                callActivityID:null
            }
        };
        this.api=new APIRequestDashboard();
    }
    
    componentWillReceiveProps(nextProps) {        
        this.setState({activities:nextProps.activities});       
      }
    componentDidMount() {  
    }
    onRefresh=()=>{
        if(this.props.onRefresh)
        this.props.onRefresh()
    }
    // loadData()
    // {
    //     const {filter}=this.state;
    //     if(filter!=null)
    //     {
    //         this.setState({showSpinner:true});
    //         this.api.getChangeRequest(filter).then(activities=>{
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
               className:"text-top"                                       
            },
            {
                path: "problemID",
                label: "",
                hdToolTip: "Service Request Number",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",                 
                className: "text-center text-top",
                classNameColumn: "",
                sortable: false,    
                content: (problem) => el('a', {
                    href: `SRActivity.php?action=displayActivity&serviceRequestId=${problem.problemID}`,
                    target: '_blank'
                }, problem.problemID)
             },
             {
                path: "requestBody",
                label: "",
                key: "requestBody",
                hdToolTip: "Change Requested",
                icon: "fal fa-2x fa-file-alt  color-gray2 ",               
                hdClassName: "text-center",
                sortable: false,    
                content:(activity)=><div className="notes" dangerouslySetInnerHTML={{__html: activity?.requestBody}}></div>
             },
             {
                path: "requestedBy",
                label: "",
                key: "requestedBy",
                hdToolTip: "Requester Name",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center text-top",
             },
             {
                path: "requestedDateTime",
                label: "",
                key: "requestedDateTime",
                hdToolTip: "Requested Date & Time",
                icon: "fal fa-2x fa-calendar color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className:"text-top nowrap text-top",
                content:(activity)=><span>{moment(activity.requestedDateTime).format("DD/MM/YYYY HH:mm")}</span>

             },             
             {
                path: "",
                label: "",
                key: "processTimeRequest",
                hdToolTip: "Process Change Request",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center text-top",
                content:(activity)=>el('a', {
                    className: "fal fa-2x fa-edit color-gray inbox-icon pointer",
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
        return el(Modal, {
            key: "processRequestTime",
            show: this.state.showProcessTimeModal,
            width: 640,
            title: "Change Request",
            onClose: this.handleCancel,
            content: <div    key="divBody">
                <table>
                    <tbody style={{whiteSpace:"nowrap"}}>
                         <tr><td>Comments</td></tr>
                        <tr style={{verticalAlign:"top"}}>                          
                            <td>
                                <div id="top2"></div>                         
                                <CNCCKEditor                                 
                                    onChange={($event)=>this.setValue('comments',$event.editor.getData())}
                                    style={{width:600, height:200}}
                                    type="inline"
                                    sharedSpaces={true}
                                    top="top2"
                                    bottom="bottom2"
                                    autoFocus="true"
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
                el("button", {onClick: ()=>this.handleRequest("Approve")}, "Approve"),
                el("button", {onClick: ()=>this.handleRequest("Deny")}, "Deny"),
            ),
        });
    }
    
    handleCancel=()=>{
        this.setState({showProcessTimeModal:false});
    }
    
    handleRequest=(status)=>{         
        const {data}=this.state;
        if(data.comments==null||data.comments=='')
        {
            this.alert("Please enter comments");
            return;
        }
        data.status=status;         
        this.api.processChangeRequest(data).then(result=>{
            if(result.status)
            {
                this.setState({showProcessTimeModal:false});
                this.onRefresh();
            }
            //console.log(result);
        });        
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

export default ChangeRequestComponent;

 