"use strict";
import Spinner from './../shared/Spinner/Spinner';
import Stepper from '../shared/stepper.js?v=10';
import CustomerSearchComponent from './subComponents/CustomerSearchComponent.js?v=10';
import SelectSRComponent from './subComponents/SelectSRComponent.js?v=10';
import CustomerSiteComponent from './subComponents/CustomerSiteComponent.js?v=10';
import LastStepComponent from './subComponents/LastStepComponent.js?v=10';
import APIActivity from '../services/APIActivity.js?v=10';
import MainComponent from '../shared/MainComponent.js?v=10'
import {params} from '../utils/utils'

import './LogServiceRequestComponent.css'

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
        console.log("step", step);
        this.setState({activeStep: step.id})
        let {steps} = this.state;
        steps = steps.map(s => {
            s.active = false;
            if (s.id <= step.id)
                s.active = true;
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

    componentDidMount() {

        this.api.getCurrentUser().then(user => {
            const {data} = this.state;
            data.currentUser = user;
            this.setState({data});
        });
        this.loadCustomerProblem();
    }

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
        console.log('Id', Id);
    }
    setActiveStep = (step) => {
        const {steps} = this.state;
        const index = steps.map(s => s.id).findIndex(s => s === step);
        steps[index].display = true;
        steps[index].active = true;
        steps[index].disabled = false;
        this.setState({steps, activeStep: step});
    }
    updateSRData = async (data, save = false) => {
        const newData = {...this.state.data, ...data};
        this.setActiveStep(newData.nextStep);
        console.log(newData);
        this.setState({data: newData});
        if (save) {
            const customData = {...newData};
            this.setState({_showSpinner: true});
            if (newData.internalNotes.indexOf(newData.internalNotesAppend) == -1)
                newData.internalNotes += newData.internalNotesAppend;
            newData.callActTypeID = null;
            const result = await this.api.createProblem(customData);
            console.log(result);
            if (result.status) {

                if (newData.uploadFiles.length > 0)
                    await this.api.uploadFiles(
                        `Activity.php?action=uploadFile&problemID=${result.problemID}&callActivityID=${result.callActivityID}`,
                        newData.uploadFiles,
                        "userfile[]"
                    );
                this.setState({_showSpinner: false});
                if (result.raiseTypeId === 3)
                    await this.alert(`Please advise customer their Service Request number is: ${result.problemID}`)
                if (result.nextURL)
                    window.location = result.nextURL;
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
                    className: "link-round"
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
                    activeStep === 1 ? el(CustomerSearchComponent, {data, updateSRData}) : null,
                    activeStep === 2 ? el(SelectSRComponent, {
                        data,
                        customerId: customer?.cus_custno,
                        contactId: customer?.con_contno,
                        updateSRData
                    }) : null,
                    activeStep === 3 ? el(CustomerSiteComponent, {
                        data,
                        customerId: customer?.cus_custno,
                        contactId: customer?.con_contno,
                        updateSRData
                    }) : null,
                    activeStep === 4 ? el(LastStepComponent, {
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
