import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../services/APIProjects';
import CNCCKEditor from "../../shared/CNCCKEditor";

export default class ProjectSummaryComponent extends MainComponent {
    api = new APIProjects();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            data: {
                engineersSummary: '',
                projectManagersSummary: '',
                projectClosureNotes: '',
                projectClosureDate: '',
            },

        };
    }

    componentDidMount() {
        this.getData();
    }

    getData() {
        this.api.getProjectSummary(this.props.projectID).then(data => {
            this.setState({data, showModal: false});
        })
    }

    getSummaryElement = () => {
        const {data} = this.state;
        return <div>
            <div className="form-group">
                <label>Engineers Summary</label>
                <CNCCKEditor type="inline"
                             style={{width: 800, minHeight: 60}}
                             value={data.engineersSummary}
                             onChange={(event) => this.setTemplateValue('engineersSummary', event)}
                />
            </div>
            <div className="form-group">
                <label>Project Managers Summary</label>
                <CNCCKEditor type="inline"
                             style={{width: 800, minHeight: 60}}
                             value={data.projectManagersSummary}
                             onChange={(event) => this.setTemplateValue('projectManagersSummary', event)}
                />
            </div>
            <div className="form-group">
                <label>Closure Meeting Date</label>
                <input type="date"
                       value={data.projectClosureDate}
                       style={{width: 150, margin: 0}}
                       onChange={(event) => this.setValue('projectClosureDate', event.target.value)}
                />
            </div>
            <div className="form-group">
                <label>Project Closure Notes</label>
                <CNCCKEditor type="inline"
                             style={{width: 800, minHeight: 60}}
                             value={data.projectClosureNotes}
                             onChange={(event) => this.setTemplateValue('projectClosureNotes', event)}
                />
            </div>
            <button onClick={this.handleSave}>Save</button>
        </div>
    }
    setTemplateValue = (template, value) => {

        this.setValue(template, value);
    }
    handleSave = () => {
        const {data} = this.state;
        this.setState({showSpinner: true});
        this.api.updateProjectSummary(this.props.projectID, data).then(result => {
            if (result.status)
                setTimeout(() => this.setState({showSpinner: false}), 1000);
        })
    }

    render() {
        return <div>
            <Spinner key="spinner"
                     show={this.state.showSpinner}
            />
            {this.getAlert()}
            {this.getSummaryElement()}

        </div>
    }

}

  