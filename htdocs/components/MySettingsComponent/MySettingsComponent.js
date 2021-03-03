"use strict";
import CheckBox from '../shared/checkBox';
import React from 'react';
import ReactDOM from 'react-dom';

import '../style.css'
import MainComponent from '../shared/MainComponent';
import APIMySettings from './services/APIMySettings';
import APIUser from '../services/APIUser';
import Table from '../shared/table/table';
import '../shared/table/table.css';
class MySettingsComponent extends MainComponent {
    el = React.createElement;
    api=new APIMySettings();
    apiUser=new APIUser();
    TAB_MY_ACCOUNT=1;
    TAB_MY_SETTINGS=2;
    TAB_MY_FEEDBACK=3;
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            activeTab:this.TAB_MY_ACCOUNT,
            feedbacks:[],
            filter:{
                from:moment().subtract(3,'M').format("YYYY-MM-01"),
                to:''
            },
            callBackEmail:false
        };
        this.tabs = [
            {id: this.TAB_MY_ACCOUNT, title: "My Account", icon: null},
            {id: this.TAB_MY_SETTINGS, title: "My Settings", icon: null},
            {id: this.TAB_MY_FEEDBACK, title: "My Feedback", icon: null},

        ];
    }

    componentDidMount() {
        fetch('?action=getMySettings')
            .then(res => res.json())
            .then(data => {
                data.lengthOfServices = 0;
                if (data.startDate) {
                    data.lengthOfServices = (moment().diff(moment(data.startDate), 'months') / 12).toFixed(1);
                    data.startDate = moment(data.startDate).format('DD-MM-YYYY');
                }
                if (data.userLog)
                    data.userLog = data.userLog.map(log => {
                        return {...log, loggedDate: moment(log.loggedDate).format('DD-MM-YYYY')};
                    });
                console.log(data);
                this.setState({...data});
            })
    }
    getTabsElement = () => {
        const {el, tabs} = this;
        return el(
            "div",
            {
                key: "tab",
                className: "tab-container",
                style: {flexWrap: "wrap", justifyContent: "flex-start", maxWidth: 1300}
            },
            tabs.map((t) => {
                return el(
                    "i",
                    {
                        key: t.id,
                        className: this.isActive(t.id) + " nowrap",
                        onClick: () => this.setActiveTab(t.id),
                        style: {width: 200}
                    },
                    t.title,
                    t.icon
                        ? el("span", {
                            className: t.icon,
                            style: {
                                fontSize: "12px",
                                marginTop: "-12px",
                                marginLeft: "-5px",
                                position: "absolute",
                                color: "#000",
                            },
                        })
                        : null
                );
            })
        );
    };

    isActive = (code) => {
        const {activeTab} = this.state;
        if (activeTab == code) return "active";
        else return "";
    };
    setActiveTab = (activeTab) => {         
        this.setState({activeTab},()=>this.loadActiveTabData());        
    };
    getElement(key, label, value) {
        return <tr key>
            <td>{label}</td>
            <td>{value}</td>
        </tr>        
    }

    getUserLog() {
        if (this.state.userLog)
            return this.el("dl", {key: "user_log"}, [
                this.state.userLog.map((log,indx) => {
                    return this.el('dd', {key: indx}, log.loggedDate + ' ' + log.startedTime)
                })]);

        else return null;
    }

    handleOnChange = () => {
        const sendEmailAssignedService = !this.state.sendEmailAssignedService;
        this.setState({sendEmailAssignedService});
    }

    handleOnClick = () => {
        const body={
            sendEmailAssignedService:(this.state.sendEmailAssignedService ? 1 : 0),
            bccOnCustomerEmails:(this.state.bccOnCustomerEmails ? 1 : 0),
            callBackEmail:(this.state.callBackEmail ? 1 : 0),
        }
        console.log(body);
        this.api.saveMySettings(body).then(result=>{
            // if(result.status)
            //     this.alert('Setting saved successfully');
        })
        // fetch('?action=sendEmailAssignedService&&sendEmailAssignedService=' + (this.state.sendEmailAssignedService ? 1 : 0), {method: 'POST'}).then(response => {
        //     this.alert('Setting saved successfully');
        // })
    }
    getMyAccountTab=()=>{
       return (
         <table style={{width:400}} key="table-active">
           <tbody>
               <tr>
                   <td>Name</td>
                   <td>{ this.state.name}</td>
               </tr>
               <tr>
                   <td>Job Title</td>
                   <td>{ this.state.jobTitle}</td>
               </tr>
               <tr>
                   <td>Start Date</td>
                   <td>{ this.state.startDate}</td>
               </tr>
               <tr>
                   <td>Length Of Service</td>
                   <td>{  this.state.lengthOfServices + " years"}</td>
               </tr>
               <tr>
                   <td>Manager</td>
                   <td>{ this.state.manager}</td>
               </tr>
               <tr>
                   <td>Team</td>
                   <td>{ this.state.team}</td>
               </tr>
          
             <tr>
                 <td>Last login times</td>
                 <td>{this.getUserLog()}</td>
             </tr>
              
           </tbody>
         </table>
       );
       
    }
    getMySettingTab=()=>{
        return this.el('div',{key:"container"},         
        this.el(CheckBox,
            {
                key: 'sendMeEmail',
                name: 'sendMeEmail',
                label: "Send me an email when I'm assigned a Service Request.",
                checked: this.state.sendEmailAssignedService,
                onChange: ()=>this.setState({'sendEmailAssignedService':!this.state.sendEmailAssignedService})
            }, null),
        this.el(CheckBox,
            {
                key: 'bccEmail',
                name: 'bccEmail',
                label: "BCC on customer emails.",
                checked: this.state.bccOnCustomerEmails,
                onChange: ()=>this.setState({'bccOnCustomerEmails':!this.state.bccOnCustomerEmails})
            }, null),
        this.el(CheckBox,
            {
                key: 'callBackEmail',
                name: 'callBackEmail',
                label: "Send me an email when I receive a call back request.",
                checked: this.state.callBackEmail,
                onChange: ()=>this.setState({'callBackEmail':!this.state.callBackEmail})
            }, null),
        this.el('button', {key: 'btnSave', style: {width: 50}, onClick: this.handleOnClick}, 'Save')
        );
    }
    getMyFeedbackTab= ()=>{
        const {feedbacks,filter}=this.state;;
        const columns=[
            {
                path: "value",
                label: "",
                hdToolTip: "Comments",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-heart color-gray2 pointer",
                sortable: true,
                content:(feed)=>{
                    switch (feed.value) {
                        case 1:
                            return <i className="fal fa-smile fa-2x"></i>
                        case 2:
                            return <i className="fal fa-meh fa-2x "></i>
                        case 3:
                            return <i className="fal fa-frown fa-2x "></i>                    
                        default:
                        return  '';
                    }
                }
                //className: "text-center",
             },
             {
                path: "problemID",
                label: "",
                hdToolTip: "Service Request",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: true,
                content:(feed)=><a href={`SRActivity.php?action=displayActivity&serviceRequestId=${feed.problemID}` } target="_blank">{feed.problemID}</a>,
                className: "text-center",
             },
            {
               path: "cus_name",
               label: "",
               hdToolTip: "Customer",
               //hdClassName: "text-center",
               icon: "fal fa-2x fa-building color-gray2 pointer",
               sortable: true,
               //className: "text-center",
            },
            {
                path: "comments",
                label: "",
                hdToolTip: "Comments",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: true,
                //className: "text-center",
             },
             {
                path: "createdAt",
                label: "",
                hdToolTip: "Date of feedback",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-calendar color-gray2 pointer",
                sortable: true,                
                className: "text-center",
             },
        ]
        return <div>
            <div className="flex-row" style={{alignItems:"center",marginBottom:10}}>
                <div>From</div>
                <div><input style={{marginLeft:12}} type="date" value={filter.from} onChange={(event)=>this.setFilter('from',event.target.value)}></input></div>
                <div>To</div>
                <div><input type="date" value={filter.to} onChange={(event)=>this.setFilter('to',event.target.value)}></input></div>
            </div>
        <Table id="myfeedback"
        data={feedbacks || []}
        columns={columns}
        pk="id"
        search={true}        
        >
        </Table>
        </div>;
    }
    setFilter=(field,value)=>{
        const {filter}=this.state;
        filter[field]=value;
        this.setState({filter});
        this.loadActiveTabData();
    }
    loadActiveTabData=()=>{
        const {activeTab,filter}=this.state;
        switch(activeTab)
        {
            case this.TAB_MY_FEEDBACK:
                return  this.apiUser.getMyFeedback(filter.from,filter.to).then(feedbacks=>this.setState({feedbacks}));                            
        }
    }
    getActiveTab=()=>{
        const {activeTab}=this.state;
        switch(activeTab)
        {
            case this.TAB_MY_ACCOUNT:
                return this.getMyAccountTab();                
            case this.TAB_MY_SETTINGS:
                return this.getMySettingTab();
            case this.TAB_MY_FEEDBACK:
                return this.getMyFeedbackTab();
        }
    }
    render() {        
        return this.el(
            "div",            
            {className: 'my-account'},
            [
                this.getAlert(),
                this.getTabsElement(),
                this.getActiveTab()
            ]
        );
    }
}

export default MySettingsComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById('react_main_mysettings');
    ReactDOM.render(React.createElement(MySettingsComponent), domContainer);
})
