"use strict";
import Spinner from './../shared/Spinner/Spinner';
import Stepper from '../shared/stepper';
import CustomerSearchComponent from './subComponents/CustomerSearchComponent';
import SelectSRComponent from './subComponents/SelectSRComponent';
import CustomerSiteComponent from './subComponents/CustomerSiteComponent';
import LastStepComponent from './subComponents/LastStepComponent';
import APIActivity from '../services/APIActivity';
import MainComponent from '../shared/MainComponent'
import {params} from '../utils/utils'
import React from 'react';
import ReactDOM from 'react-dom';
import './LogServiceRequestComponent.css'
import './../style.css';
import '../shared/ToolTip.css'

export default class LogServiceRequestComponent extends MainComponent {
    el = React.createElement;
    steps = [{id: 0, title: "", display: false, active: false}];
    api = new APIActivity();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            projects: [],
            _showSpinner: false,
            steps: this.initSteps(),
            activeStep: 1,
            customer: null,
            data: {
                nextStep: 1,
                customer: null
            }
        }
    }

    componentDidMount() {
        this.api.getCurrentUser().then(user => {
            const {data} = this.state;
            data.currentUser = user;
            this.setState({data});
        });
        this.loadCustomerProblem();
        // check pending Reopen
        this.checkPendingReopen();
    }
    checkPendingReopen=()=>{
        const pendingReopenedID=params.get("pendingReopenedID");
        const emailSubjectSummary=params.get("emailSubjectSummary");
        if(pendingReopenedID)
        {
            //loading reopen data
            this.api.getPendingReopen(pendingReopenedID).then(res=>{
                // init data;
                //customer, customerID: customer.cus_custno, nextStep: 2
                const {data}=this.state;
                data.customerID=res.customerID;
                data.customer={cus_custno:res.customerID,cus_name:res.cus_name,con_contno:res.contactID};
                data.emailSubjectSummary=emailSubjectSummary;
                data.reason=res.reason;
                data.pendingReopenedID=pendingReopenedID;
                data.contactID=res.contactID;
                data.deletePending='true';
                this.setState({data});
                this.setActiveStep(2);
                this.setActiveStep(3);
            })
        }
    }

    initSteps = () => {
        this.steps = [
            {id: 1, title: "Select Customer", display: true, active: true, disabled: false},
            {id: 2, title: "Select Service Request", display: true, active: false, disabled: true},
            {id: 3, title: "What is the problem?", display: true, active: false, disabled: true},
            {id: 4, title: "Finish", display: true, active: false, disabled: true},

        ];
        return this.steps;
    };
    handleStepChange = (step) => {

        this.setState({activeStep: step.id})
        let {steps} = this.state;
        steps = steps.map(s => {

            s.active = s.id <= step.id;
            return s
        });
        this.setState({steps});
    }
    getStepper = () => {
        const {el, handleStepChange} = this;
        return el(
            Stepper, {steps: this.steps, onChange: handleStepChange}
        );
    };

   

    loadCustomerProblem = () => {
        const Id = params.get("customerproblemno");
        if (Id) {
            this.api.getCustomerRaisedRequest(Id).then(result => {
                const {data} = this.state;
                data.customerproblemno = result.cpr_customerproblemno;
                data.reason = result.cpr_reason;
                data.priority = result.cpr_priority;
                data.siteNo = result.con_siteno;
                data.customer = {cus_custno: result.con_custno, con_contno: result.cpr_contno};
                this.setState({data});
            })
        }

    }
    setActiveStep = (step) => {
        const {steps} = this.state;
        const index = steps.map(s => s.id).findIndex(s => s == step);
        steps[index].display = true;
        steps[index].active = true;
        steps[index].disabled = false;
        this.setState({steps, activeStep: step});
    }
    updateSRData = async (data, save = false) => {
        const newData = {...this.state.data, ...data};
        this.setActiveStep(newData.nextStep);
        this.setState({data: newData});
        if (save) {
            const customData = {...newData};
            this.setState({_showSpinner: true});
            newData.callActTypeID = null;           
            const result = await this.api.createProblem(customData);
            if (result.status) {
                if (newData.uploadFiles.length > 0) {
                    await this.api.uploadFiles(
                        `Activity.php?action=uploadFile&problemID=${result.problemID}&callActivityID=${result.callActivityID}`,
                        newData.uploadFiles,
                        "userfile[]"
                    );
                }

                if (newData.internalDocuments.length > 0) {
                    await this.api.addServiceRequestFiles(result.problemID, newData.internalDocuments);
                }

                this.setState({_showSpinner: false});
                if (result.raiseTypeId == 3) {
                    await this.alert(
                        `<p>Please advise customer their Service Request number is: ${result.problemID}.</p><p>The SLA for priority ${data.priority} request is ${result.SLAResponseHours} hour${result.SLAResponseHours > 1 ? 's' : ''}</p>`,
                        500,
                        'Alert',
                        true,
                        false
                    );
                }
                if (result.nextURL) {
                    window.location = result.nextURL;
                }
            }
            this.setState({_showSpinner: false});

        }
    }
    getProjectsElement = () => {
        const {data} = this.state;
        const {el} = this;
        if (data && data.projects && data.projects.length > 0) {
            return el('div', {style: {display: "flex", flexDirection: "row", alignItems: "center", marginTop: -20}},
                el('h3', {className: "mr-5"}, "Projects "),
                data.projects.map(p => el("a", {
                    key: p.projectID,
                    href: p.editUrl,
                    className: "link-round",
                    target: '_blank'
                }, p.description))
            )
        } else return null;
    }

    render() {
        const {el, getStepper, updateSRData} = this;
        let {activeStep, data, _showSpinner} = this.state;
        const customer = data.customer;
        return el("div", null,
            el(Spinner, {show: _showSpinner}),
            this.getAlert(),
            el("div", {style: {minHeight: "90vh"}},
                this.getProjectsElement(),
                getStepper(),
                el('div', {style: {marginTop: 30}},
                    activeStep == 1 ? el(CustomerSearchComponent, {data, updateSRData}) : null,
                    activeStep == 2 ? el(SelectSRComponent, {
                        data,
                        customerId: customer?.cus_custno,
                        contactId: customer?.con_contno,
                        updateSRData
                    }) : null,
                    activeStep == 3 ? el(CustomerSiteComponent, {
                        data,
                        customerId: customer?.cus_custno,
                        contactId: customer?.con_contno,
                        updateSRData
                    }) : null,
                    activeStep == 4 ? el(LastStepComponent, {
                        data,
                        customerId: customer?.cus_custno,
                        contactId: customer?.con_contno,
                        updateSRData
                    }) : null,
                )
            )
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainLogServiceRequest");
    ReactDOM.render(React.createElement(LogServiceRequestComponent), domContainer);
});
