import React from 'react';
import {params} from "../utils/utils";
import APICustomers from '../services/APICustomers';
import MainComponent from '../shared/MainComponent';
import Table from '../shared/table/table';
import APIProjects from '../ProjectsComponent/services/APIProjects';
import ToolTip from '../shared/ToolTip';

export default class CustomerProjectsComponent extends MainComponent {
    api = new APICustomers();
    apiProjects = new APIProjects();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            customerId: null,
            projects: []
        }
    }

    componentDidMount() {
        this.getData();
    }

    getData = () => {
        const customerId = params.get("customerID");
        this.api.getCustomerProjects(customerId).then(projects => {
            projects.map(p => {
                if (p.notes != null)
                    p.notes = p.notes.substr(0, 150);
                return p;
            })
            this.setState({projects, customerId});
        })
    }
    getTable = () => {
        const columns = [
            {
                path: "name",
                label: "",
                hdToolTip: "Project Name",
                icon: "fal fa-2x fa-text color-gray2 pointer",
                sortable: true,
                width: 300
            },
            {
                path: "notes",
                label: "",
                hdToolTip: "Notes",
                icon: "fal fa-2x fa-file-contract color-gray2 pointer",
                sortable: true,
                width: 600
            },
            {
                path: "startDate",
                label: "",
                hdToolTip: "Start Date",
                icon: "fal fa-2x fa-hourglass-start color-gray2 pointer",
                sortable: true,
                content: (project) => this.getCorrectDate(project.startDate)
            },
            {
                path: "expiryDate",
                label: "",
                hdToolTip: "Expiry Date",
                icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
                sortable: true,
                content: (project) => this.getCorrectDate(project.expiryDate)
            },
            {
                path: "edit",
                label: "",
                hdToolTip: "Edit Project",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,
                content: (project) => this.getEditElement(project, () => this.handleEdit(project))
            },
            {
                path: "delete",
                label: "",
                hdToolTip: "Delete Project",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,
                content: (project) => this.getDeleteElement(project, () => this.handleDelete(project), project.isDeletable)
            },
        ]
        return <Table

            key="projects"
            pk="id"
            style={{maxWidth: 1300}}
            columns={columns}
            data={this.state.projects || []}
            search={true}
        >
        </Table>
    }

    handleEdit = (project) => {
        window.location = `Projects.php?action=edit&projectID=${project.id}`;
    }

    handleDelete = async (project) => {
        if (await this.confirm("Are you sure you want to delete this project?")) {
            this.apiProjects.deleteProject(project.id).then(res => {
                this.getData();
            })
        }
    }

    render() {
        return <div>
            <div className="m-5">
                <ToolTip title="Add New Project" width={30}>
                    <i className="fal fa-plus fa-2x pointer"
                       onClick={() => window.location = `Projects.php?action=add&customerID=${this.state.customerId}`}
                    ></i>
                </ToolTip>
            </div>
            {this.getConfirm()}
            {this.getTable()}
        </div>
    }
}
