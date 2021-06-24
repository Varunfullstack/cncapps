import React, {Fragment} from 'react';
import * as HTMLReactParser from 'html-react-parser'
import moment from "moment";
import {connect} from "react-redux";
import {entityMapToArray, params} from "../utils/utils";
import {addNewProject, deleteProject, hideNewProjectModal, newProjectFieldUpdate, showNewProjectModal} from "./actions";
import AddProjectModalComponent from "./modals/AddProjectModalComponent";
import {getProjects} from "./selectors/selectors";
import APICustomers from '../services/APICustomers';
import MainComponent from '../shared/MainComponent';
import Table from '../shared/table/table';
export default class CustomerProjectsComponent extends MainComponent {
    api =new APICustomers();

    constructor(props) {
        super(props);
        this.state={
            ...this.state,
            customerId:null,
            projects:[]
        }
    }
    componentDidMount() {
        const customerId=params.get("customerID");
        this.api.getCustomerProjects(customerId).then(projects=>{
            console.log(projects);
            projects.map(p=>{
                if( p.notes!=null)
                p.notes=p.notes.substr(0,150);
               return p;
            })
            this.setState({projects,customerId});
        })
    }
    getTable=()=>{
        const columns=[
            {
               path: "name",
               label: "name",
               hdToolTip: "Project Name",               
               //icon: "fal fa-2x fa-signal color-gray2 pointer",
               sortable: true,                              
            },
            {
                path: "notes",
                label: "notes",
                hdToolTip: "Notes",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,      
                width:500                        
             },
             {
                path: "startDate",
                label: "Start Date",
                hdToolTip: "Start Date",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,                              
                content:(project)=>this.getCorrectDate(project.startDate)
             },
             {
                path: "expiryDate",
                label: "Expiry Date",
                hdToolTip: "Expiry Date",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,          
                content:(project)=>this.getCorrectDate(project.expiryDate)                    
             },
             {
                path: "edit",
                label: "",
                hdToolTip: "Edit Project",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,                              
                content:(project)=>this.getEditElement(project,()=>this.handleEdit(project))
             },
             {
                path: "delete",
                label: "",
                hdToolTip: "Delete Project",               
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,                              
                content:(project)=>this.getEditElement(project,()=>this.handleDelete(project))
             },
        ]
        return <Table           
                     
        key="projects"
        pk="id"
        columns={columns}
        data={this.state.projects||[]}
        search={true}
        >
        </Table>
    }

    handleEdit=(project)=>{
        console.log("Edit project",project);
    }

    handleDelete=(project)=>{
        console.log("Delete project",project);
    }
/*
    renderHeadingRow() {
        return this.el(
            'tr',
            {
                key: 'headerRow'
            },
            [
                this.renderTd('headerLightgrey', 'Name', 'headerName'),
                this.renderTd('headerLightgrey', 'Notes', 'headerNotes'),
                this.renderTd('headerLightgrey', 'Starts', 'headerStarts'),
                this.renderTd('headerLightgrey', 'Expires', 'headerExpires'),
                this.renderTd('headerLightgrey', ' ', 'headerActions'),
            ],
        )
    }

    renderTd(className, value, key, isComplex = false) {
        return this.el(
            'td',
            {className, key},
            (value && !isComplex && HTMLReactParser.default(value)) || value
        )
    }

    handleProjectDelete(project) {

        if (!confirm('Are you sure you want to delete this project?')) {
            return;
        }
        this.props.onDeleteProject(project.id);
    }

    renderDeleteLink(project) {
        if (!project.isDeletable) {
            return null;
        }
        return (
            <button className="btn btn-outline-danger"
                    onClick={() => this.handleProjectDelete(project)}
            >
                <i className="fal fa-trash-alt fa-lg"/>
            </button>
        )
    }

    formatDate(dateString) {
        if (!dateString) {
            return ''
        }
        return moment(dateString, 'YYYY-MM-DD').format('DD/MM/YYYY');
    }

    renderProjectsRows() {
        const {projects} = this.props
        return projects.map(
            project => {
                return this.el(
                    'tr',
                    {
                        key: `projectRow-${project.id}`
                    },
                    [
                        this.renderTd('content', project.name, `name-${project.id}`),
                        this.renderTd('content', project.notes && project.notes.substr(0, 50), `notes-${project.id}`),
                        this.renderTd('content', this.formatDate(project.startDate), `startDate-${project.id}`),
                        this.renderTd('content', this.formatDate(project.expiryDate), `expiryDate-${project.id}`),
                        this.renderTd('content',
                            [
                                this.el(
                                    'a',
                                    {
                                        href: `/Project.php?action=edit&projectID=${project.id}`,
                                        key: `edit-${project.id}`
                                    },
                                    'edit'
                                ),
                                this.renderDeleteLink(project),
                            ],
                            `actions-${project.id}`,
                            true
                        )
                    ]
                )
            }
        )
    }

    renderProjects() {
        return this.el(
            'table',
            {
                className: 'content',
                border: 0,
                cellPadding: 2,
                cellSpacing: 1,
                key: 'projectsTable'
            },
            this.el(
                'tbody',
                {},
                [
                    this.renderHeadingRow(),
                    ...this.renderProjectsRows()
                ]
            )
        )
    }
*/
    render() {
        return <div>
            {this.getTable()}
        </div>
      /*  const {customerId, newProject, newProjectModalShown, onNewProjectFieldUpdate, onHideNewProjectModal, onAddNewProject, isFetching, projects, onShowNewProjectModal} = this.props;

        if (isFetching) {
            return '';
        }
        return (
            <Fragment>
                <AddProjectModalComponent description={newProject.description}
                                          summary={newProject.summary}
                                          show={newProjectModalShown}
                                          openedDate={newProject.openedDate}
                                          onNewProjectFieldUpdate={(field, value) => {
                                              onNewProjectFieldUpdate(field, value)
                                          }}
                                          onClose={() => onHideNewProjectModal()}
                                          onAddProject={() => onAddNewProject(customerId, newProject.description, newProject.summary, newProject.openedDate)}
                />
                <div className="customerAddProjects mt-3">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Projects</h2>
                        </div>
                        <div className="col-md-12">
                            <button className="btn btn-sm btn-new mt-3 mb-3"
                                    onClick={() => onShowNewProjectModal()}
                            >Add Project
                            </button>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                            <table className="table table-striped table-bordered"
                                   border="0"
                                   cellPadding="2"
                                   cellSpacing="1"
                            >
                                <thead>
                                <tr>
                                    <td>Items</td>
                                    <td>Notes</td>
                                    <td>Starts</td>
                                    <td>Expires</td>
                                    <td/>
                                    <td/>
                                </tr>
                                </thead>
                                <tbody>
                                {
                                    projects.map(project => {
                                        return (
                                            <tr key={project.id}>
                                                <td>{project.name}</td>
                                                <td>
                                                    <div dangerouslySetInnerHTML={{__html: project.notes && project.notes.substr(0, 50)}}/>
                                                </td>
                                                <td>{this.formatDate(project.startDate)}</td>
                                                <td>{this.formatDate(project.expiryDate)}</td>
                                                <td>
                                                    <a href={`/Project.php?action=edit&projectID=${project.id}`}
                                                       target="_blank"
                                                    >
                                                        <button type="button"
                                                                className="btn btn-outline-secondary"
                                                        >
                                                            <i className="fal fa-edit fa-lg"/>
                                                        </button>
                                                    </a>
                                                </td>
                                                <td>
                                                    {
                                                        this.renderDeleteLink(project)
                                                    }
                                                </td>
                                            </tr>
                                        )
                                    })
                                }

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </Fragment>
        )
        */
    }
}
/*
function mapStateToProps(state) {
    const {projects} = state;
    return {
        projects: getProjects(state),
        isFetching: projects.isFetching,
        newProject: projects.newProject,
        newProjectModalShown: projects.newProjectModalShown
    }
}

function mapDispatchToProps(dispatch) {
    return {
        onDeleteProject: (projectId) => {
            dispatch(deleteProject(projectId))
        },
        onNewProjectFieldUpdate: (field, value) => {
            dispatch(newProjectFieldUpdate(field, value))
        },
        onAddNewProject: (customerId, description, summary, opened) => {
            dispatch(addNewProject(customerId, description, summary, opened));
        },
        onHideNewProjectModal: () => {
            dispatch(hideNewProjectModal())
        },
        onShowNewProjectModal: () => {
            dispatch(showNewProjectModal())
        }
    }
}
*/
 