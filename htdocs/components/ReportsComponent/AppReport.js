import React from 'react';
import APIProjectOptions from '../ProjectOptionsComponent/services/APIProjectOptions';
import APIUser from '../services/APIUser';
import CustomerSearch from '../shared/CustomerSearch';
import MainComponent from "../shared/MainComponent";
import {sort} from '../utils/utils';
import './../style.css';
import './ReportsComponent.css';
import APIReports from './services/APIReports';
import RepCallbackSearch from './subComponents/RepCallbackSearch';
import RepProjects from './subComponents/RepProjects';
import RepProjectsByConsultant from './subComponents/RepProjectsByConsultant';
import RepProjectsByConsultantInProgress from './subComponents/RepProjectsByConsultantInProgress';
import RepProjectsByCustomerStageFallsStartEnd from './subComponents/RepProjectsByCustomerStageFallsStartEnd';
import RepProjectsWithoutClousureMeeting from './subComponents/RepProjectsWithoutClousureMeeting';
import RepProjectsBudget from "./subComponents/RepProjectsBudget";

class AppReport extends MainComponent {
    api = new APIReports();
    apiUsers = new APIUser();
    apiProjectOptions = new APIProjectOptions();
    components = {};

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            categories: [],
            reports: [],
            currentCategoryID: '',
            currentReportID: '',
            parameters: [],
            compParameters: [],
            data: {},
            consultants: [],
            hideCategories: false,
            projectStages: [],
            projectTypes: []
        };
        this.components = {
            RepProjectsByConsultant: RepProjectsByConsultant,
            RepProjectsByConsultantInProgress: RepProjectsByConsultantInProgress,
            RepProjectsByCustomerStageFallsStartEnd: RepProjectsByCustomerStageFallsStartEnd,
            RepProjects: RepProjects,
            RepProjectsWithoutClousureMeeting: RepProjectsWithoutClousureMeeting,
            RepCallbackSearch: RepCallbackSearch,
            RepProjectsBudget: RepProjectsBudget,
        };
    }

    componentDidMount() {
        const currentCategoryID = this.props.categoryID;
        const hideCategories = this.props.hideCategories;
        if (currentCategoryID) {
            this.handleCategoryChange(currentCategoryID);

        }
        this.setState({hideCategories});
        if (!hideCategories)
            this.getReportCategories();

    }

    getReportCategories = () => {
        this.api.getReportCategoriesActive().then(categories => {
            this.setState({categories});
        });
    }
    loadConsultants = () => {
        this.apiUsers.getActiveUsers().then(consultants => {
            this.setState({consultants})
        });
    }
    getCategoriesElement = () => {
        const {categories, currentCategoryID, hideCategories} = this.state;
        if (hideCategories)
            return null;
        return <tr>
            <td>Report Categories</td>
            <td>
                <select style={{width: 300}} value={currentCategoryID}
                        onChange={(event) => this.handleCategoryChange(event.target.value)}

                >
                    <option value="">Select Category</option>
                    {categories.map(c => <option key={c.id} value={c.id}>{c.title}</option>)}
                </select>
            </td>
        </tr>
    }
    handleCategoryChange = (categoryID) => {
        this.api.getCategoryReports(categoryID).then(reports => {
            this.setState({currentCategoryID: categoryID, reports});

        })
    }
    getReportsElement = () => {
        const {reports, currentReportID} = this.state;
        return <tr>
            <td>Reports</td>
            <td>
                <select style={{width: 300}} value={currentReportID}
                        onChange={(event) => this.handleReportChange(event.target.value)}

                >
                    <option value="">Select Report</option>
                    {reports.map(c => <option key={c.id} value={c.id}>{c.title}</option>)}
                </select>
            </td>
        </tr>
    }
    loadParametersData = (parameters) => {
        for (let i = 0; i < parameters.length; i++) {
            switch (parameters[i].name) {
                case "consID":
                    this.loadConsultants();
                    break;
                case "projectStageID":
                    this.loadProjectStages();
                    break;
                case "projectTypeID":
                    this.loadProjectTypes();
                    break;
                default:
                    break;
            }
        }
    }
    loadProjectStages = () => {
        this.apiProjectOptions.getProjectStages().then(projectStages => {
            this.setState({projectStages});
        })
    }
    loadProjectTypes = () => {
        this.apiProjectOptions.getProjectTypes().then(projectTypes => {
            this.setState({projectTypes});
        })
    }
    handleReportChange = (reportID) => {
        if (reportID)
            this.api.getReportParameters(reportID)
                .then(parameters => {
                    if (parameters.length > 0) {
                        parameters = parameters.map(p => {
                            p.value = '';
                            return p;
                        });
                        this.loadParametersData(parameters);
                        parameters = sort(parameters, 'parameterOrder');
                        this.setState({parameters});
                    }

                });
        this.setState({currentReportID: reportID, parameters: []});
    }
    getParametersElement = () => {
        const {parameters} = this.state;
        if (parameters.length == 0)
            return null;
        return parameters.map(p => <tr key={p.id}>
            <td>{p.title || p.defaultTitle}</td>
            <td>{this.getParameter(p)}</td>
        </tr>)
    }
    setParameterValue = (parameter, value) => {
        const {parameters} = this.state;
        const indx = parameters.map(p => p.id).indexOf(parameter.id);
        if (indx >= 0) {
            parameters[indx].value = value;
        }
        this.setState({parameters});
    }
    getParameterValue = (parameter) => {
        const {parameters} = this.state;
        const indx = parameters.map(p => p.id).indexOf(parameter.id);
        if (indx >= 0) {
            return parameters[indx].value;
        }
        return '';
    }
    getParameter = (parameter) => {
        const {consultants, projectStages, projectTypes} = this.state;
        switch (parameter.name) {
            case 'consID':
                return <select required={parameter.required} value={parameter.value}
                               onChange={(event) => this.setParameterValue(parameter, event.target.value)}>
                    <option value=""/>
                    {consultants.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>;
            case 'projectStageID':
                return <select required={parameter.required} value={parameter.value}
                               onChange={(event) => this.setParameterValue(parameter, event.target.value)}>
                    <option value=""/>
                    {projectStages.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>;
            case 'projectTypeID':
                return <select required={parameter.required} value={parameter.value}
                               onChange={(event) => this.setParameterValue(parameter, event.target.value)}>
                    <option value=""/>
                    {projectTypes.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>;
            case 'dateFrom':
                return <input type="date" required={parameter.required} value={parameter.value}
                              onChange={(event) => this.setParameterValue(parameter, event.target.value)}/>;
            case 'dateTo':
                return <input type="date" required={parameter.required} value={parameter.value}
                              onChange={(event) => this.setParameterValue(parameter, event.target.value)}/>;
            case 'customerID':
                return <CustomerSearch
                    onChange={(customer) => this.setParameterValue(parameter, customer.id)}/>
            case 'callbackStatus':
                return <select required={parameter.required} value={parameter.value}
                               onChange={(event) => this.setParameterValue(parameter, event.target.value)}>
                    <option value="">All</option>
                    <option value="awaiting">Awaiting</option>
                    <option value="contacted">Contacted</option>
                    <option value="canceled">Canceled</option>
                </select>
            default:
                break;
        }
    }
    isParametersValid = () => {
        const {parameters} = this.state;
        for (let i = 0; i < parameters.length; i++) {
            if (parameters[i].required && parameters[i].value == '') {
                return false;
            }
        }
        return true;
    }
    handleGo = () => {
        const {parameters, currentReportID} = this.state;
        if (!currentReportID) {
            this.alert("Please select the report");
            return;
        }
        if (!this.isParametersValid()) {
            this.alert("Please enter required fields");
            return;
        }
        const compParameters = {};
        for (let i = 0; i < parameters.length; i++) {
            compParameters[parameters[i].name] = parameters[i].value;
        }
        this.setState({compParameters});

    }
    getCurrentReportComponent = () => {
        const {currentReportID, reports, compParameters} = this.state;
        if (currentReportID == '')
            return null;
        const report = reports.find(r => r.id == currentReportID);
        const RepComponent = this.components[report.component];
        return <RepComponent {...compParameters}/>;
    }
    handleClear = () => {
        const {parameters} = this.state;
        parameters.map(p => {
            p.value = '';
            return p;
        });
        this.setState({parameters});
    }

    render() {
        return <div>
            {this.getAlert()}
            <table>
                <tbody>
                {this.getCategoriesElement()}
                {this.getReportsElement()}
                {this.getParametersElement()}
                <tr>
                    <td>
                        <button onClick={this.handleGo}>Go</button>
                        <button onClick={this.handleClear}>Clear</button>
                    </td>
                    <td/>
                </tr>
                </tbody>
            </table>
            {this.getCurrentReportComponent()}
        </div>;
    }
}

export default AppReport;

 