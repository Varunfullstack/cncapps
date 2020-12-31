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
import APIUser from "../../services/APIUser";
//import './../../style.css';
class SalesRequestComponent extends MainComponent {
    el = React.createElement;
    api;
    apiUsers;
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
            },
            users:[]
        };
        this.api=new APIRequestDashboard();
        this.apiUsers=new  APIUser();
    }
    
    // componentWillReceiveProps(nextProps) {
    //     this.setState({activities:nextProps.activities} );
    //   }
    static getDerivedStateFromProps(props, current_state) {
        return {...current_state, ...props};
    }
    componentDidMount() {          
        this.getAllocateUsers();
    }
    onRefresh=()=>{
        if(this.props.onRefresh)
        this.props.onRefresh()
    }
    getAllocateUsers=()=>{
        this.apiUsers.getActiveUsers().then(users=>{
            this.setState({users});
            //console.log(users);
        })
    }
    // loadData()
    // {
    //     const {filter}=this.state;
    //     if(filter!=null)
    //     {
    //         this.setState({showSpinner:true});
    //         this.api.getSalesRequest(filter).then(activities=>{
    //             //console.log(activities);
    //             this.setState({activities,showSpinner:false});
    //         })
    //     }
    // }
    getUsersElement=(activity)=>{
        const {users}=this.state;
        return <select value={activity.salesRequestAssignedUserId||""} onChange={($event)=>this.handleuserAllocate($event.target.value,activity)}>
            <option key="empty" ></option>
            {
                users.map(user=><option key={user.id} value={user.id}>{user.name}</option>)
            }
        </select>
    }
    
    handleuserAllocate=(userId,activity)=>{
        //console.log(userId,activity);        
        this.api.setAllocateUser(userId,activity.problemID).then(result=>{
            //console.log(result);
            if(result.status)
                this.onRefresh();
        })
    }
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
                hdToolTip: "Sales Request",
                icon: "fal fa-2x fa-file-alt  color-gray2 ",               
                hdClassName: "text-center",
                sortable: false,    
                content:(activity)=><div dangerouslySetInnerHTML={{__html: activity?.requestBody}}></div>
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
                className:"text-center text-top nowrap",
                content:(activity)=><span>{moment(activity.requestedDateTime).format("DD/MM/YYYY HH:mm")}</span>

             },             
             {
                path: "attachments",
                label: "",
                key: "attachments",
                hdToolTip: "Attachments",
                icon: "fal fa-2x fa-paperclip color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className:" text-center text-top",
                content:(problem)=>{
                    return <div style={{display:"flex",flexDirection:"column"}}>
                     {problem.attachments.map(file=><a key={file.documentId} href={"Activity.php?action=viewFile&callDocumentID="+file.documentId}>{file.filename}</a>)}
                     </div>
                }
             }, 
             {
                path: "type",
                label: "",
                key: "type",
                hdToolTip: "Type",
                icon: "fal fa-2x fa-typewriter color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className:"text-center text-top"
             },
             {
                path: "salesRequestAssignedUserId",
                label: "",
                key: "assignedTo",
                hdToolTip: "Being Reviewed By'",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                className:"text-center text-top",
                content:(activity)=>this.getUsersElement(activity)
             },
             {
                path: "",
                label: "",
                key: "processSalesRequest",
                hdToolTip: "Process Sales Request",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: false,
                hdClassName: "text-center",
                className: "text-center text-top",
                content:(activity)=>el('a', {
                    className: "fal fa-2x fa-edit color-gray inbox-icon pointer",
                    onClick:()=>this.processSalesRequest(activity),
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
    processSalesRequest(activity){
        //console.log(activity);
        this.setState({showProcessTimeModal:true,currentActivity:activity});
        this.setValue("callActivityID",activity.callActivityID);
    }
    getTimeRequestModal=()=>{
        const {el} = this;        
        const {currentActivity}=this.state;
        const notNotifySales=currentActivity?.salesRequestDoNotNotifySalesOption        
        return el(Modal, {
            key: "processRequestTime",
            show: this.state.showProcessTimeModal,
            width: 640,
            title: "Sales Request",
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
                                    autoFocus={true}
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
                notNotifySales=="1"?el("button", {onClick: ()=>this.handleRequest("Approve Without Notifying Sales")}, "Approve Without Notifying Sales"):null,
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
        this.api.processSalesRequest(data).then(result=>{
            if(result.status)
            {
                this.setState({showProcessTimeModal:false});
                this.onRefresh();
            }
            else
            {
                this.alert(result.error);
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

export default SalesRequestComponent;

 