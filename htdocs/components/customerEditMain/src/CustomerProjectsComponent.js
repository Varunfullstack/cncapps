import React from 'react';
import * as HTMLReactParser from 'html-react-parser'
import moment from "moment";
import {connect} from "react-redux";
import {entityMapToArray} from "../../utils/utils";
import {deleteProject} from "./actions";
import AddProjectModalComponent from "./modals/AddProjectModalComponent";

class CustomerProjectsComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customerProjects: [],
            customerId: props.customerId,
        };
        document.customerProjectsComponent = this;
    }

    fetchCustomerProjects() {
        return fetch('?action=getCustomerProjects&customerId=' + this.props.customerId)
            .then(response => response.json())
            .then(response => this.setState({customerProjects: response.data}));
    }

    componentDidMount() {
        this.fetchCustomerProjects()
            .then(() => {
                this.setState({
                    loaded: true,
                });
            });
    }

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
        return this.state.customerProjects.map(
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

    render() {
        if (!this.state.loaded) {
            return '';
        }

        const {customerId} = this.props;
        const {customerProjects} = this.state;

        return (
            <div className="tab-pane fade customerAddProjects"
                 id="nav-profile"
                 role="tabpanel"
                 aria-labelledby="nav-profile-tab"
            >
                <AddProjectModalComponent/>
                <div className="customerAddProjects mt-3">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Projects</h2>
                        </div>
                        <div className="col-md-12">
                            <button className="btn btn-sm btn-new mt-3 mb-3"
                                // data-toggle="modal"
                                // data-target="#exampleModalCenter"
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
                                    customerProjects.map(project => {
                                        return (
                                            <tr key={project.id}>
                                                <td>{project.name}</td>
                                                <td>{project.notes && project.notes.substr(0, 50)}</td>
                                                <td>{this.formatDate(project.startDate)}</td>
                                                <td>{this.formatDate(project.expiryDate)}</td>
                                                <td>
                                                    <a href={`/Project.php?action=edit&projectID=${project.id}`}>
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
            </div>
        )
    }
}

function mapStateToProps(state) {
    const {projects} = state;
    return {
        projects: entityMapToArray(projects.allIds, projects.byIds),
        isFetching: projects.isFetching
    }
}

function mapDispatchToProps(dispatch) {
    return {
        onDeleteProject: (projectId) => {
            dispatch(deleteProject(projectId))
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(CustomerProjectsComponent)