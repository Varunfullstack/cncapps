"use strict";
import CheckBox from '../shared/checkBox';
import React from 'react';
import ReactDOM from 'react-dom';

import '../style.css'
import MainComponent from '../shared/MainComponent';
import APIMySettings from './services/APIMySettings';

class MySettingsComponent extends MainComponent {
    el = React.createElement;
    api=new APIMySettings();
    TAB_MY_ACCOUNT=1;
    TAB_MY_SETTINGS=2;
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            activeTab:this.TAB_MY_ACCOUNT
        };
        this.tabs = [
            {id: this.TAB_MY_ACCOUNT, title: "My Account", icon: null},
            {id: this.TAB_MY_SETTINGS, title: "My Settings", icon: null},
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
        this.setState({activeTab});        
    };
    getElement(key, label, value) {
        return [
            this.el('dt', {key: key + "_label", className: 'col-3'}, label),
            this.el('dd', {key: key + '_value', className: 'col-9'}, value == null ? '' : value),
        ];
    }

    getUserLog() {
        if (this.state.userLog)
            return this.el("dl", {key: "user_log"}, [
                this.state.userLog.map((log) => {
                    return this.el('dd', {key: log.userTimeLogID}, log.loggedDate + ' ' + log.startedTime)
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
       return  this.el('dl', {className: 'row', key: 'about_me'}, [
            this.getElement('name', 'Name', this.state.name),

            this.getElement('jobTitle', 'Job Title', this.state.jobTitle),

            this.getElement('startDate', 'Start Date', this.state.startDate),

            this.getElement('lengthOfServices', 'Length Of Service', this.state.lengthOfServices + " years"),

            this.getElement('manager', 'Manager', this.state.manager),

            this.getElement('team', 'Team', this.state.team),
            this.el('dt', {key: 'userLog', className: 'col-3'}, 'Last login times'),
            this.getUserLog(),
        ]);
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
        this.el('button', {key: 'btnSave', style: {width: 50}, onClick: this.handleOnClick}, 'Save')
        );
    }
    getActiveTab=()=>{
        const {activeTab}=this.state;
        switch(activeTab)
        {
            case this.TAB_MY_ACCOUNT:
                return this.getMyAccountTab();                
            case this.TAB_MY_SETTINGS:
                return this.getMySettingTab();
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
