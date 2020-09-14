import React from 'react';
import * as HTMLReactParser from 'html-react-parser'
import moment from "moment";

class CustomerProjectsComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customerProjects: [],
            customerId: props.customerID,
        };
        document.customerProjectsComponent = this;
    }

    fetchCustomerProjects() {
        return fetch('?action=getCustomerProjects&customerId=' + this.props.customerID)
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

    renderDeleteLink(project) {
        if (!project.isDeletable) {
            return null;
        }
        return this.el(
            'a',
            {
                href: `Project.php?action=delete&projectID=${project.id}`,
                key: `delete-${project.id}`
            },
            ' delete'
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
        return (
            <div className="tab-pane fade customerEditProjects"
                 id="nav-profile"
                 role="tabpanel"
                 aria-labelledby="nav-profile-tab"
            >
                <div className="customerEditProjects container-fluid mt-3 mb-3">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Projects</h2>
                        </div>
                        <div className="col-md-12">
                            {/*<a href="{addProjectURL}">*/}
                            {/*    <button className="btn btn-primary mt-3 mb-3">Add Project</button>*/}
                            {/*</a>*/}
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
                                <tr>
                                    <td>{projectName}</td>
                                    <td>{notes}</td>
                                    <td>{startDate}</td>
                                    <td>{expiryDate}</td>
                                    <td>
                                        {/*<a href="{editProjectLink}">*/}
                                        <button className="btn btn-outline-secondary">
                                            <i className="fa fa-edit"/>
                                        </button>
                                        {/*</a>*/}
                                    </td>
                                    <td>
                                        {/*<a href="{deleteProjectLink}">*/}
                                        <button className="btn btn-outline-danger">
                                            <i className="fa fa-trash"/>
                                        </button>
                                        {/*</a>*/}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <nav aria-label="Page navigation example">
                                <ul className="pagination justify-content-end">
                                    <li className="page-item">
                                        <a className="page-link"
                                           href="#"
                                        >Previous</a>
                                    </li>
                                    <li className="page-item">
                                        <a className="page-link"
                                           href="#"
                                        >1</a>
                                    </li>
                                    <li className="page-item">
                                        <a className="page-link"
                                           href="#"
                                        >2</a>
                                    </li>
                                    <li className="page-item">
                                        <a className="page-link"
                                           href="#"
                                        >3</a>
                                    </li>
                                    <li className="page-item">
                                        <a className="page-link"
                                           href="#"
                                        >Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        )

        //
        // if (!this.state.loaded) {
        //     return this.el(
        //         Skeleton,
        //         null,
        //         'Loading Data'
        //     );
        // }
        //
        //
        // return this.el(
        //     'div',
        //     {},
        //     [
        //         this.el(
        //             'a',
        //             {
        //                 href: `/Project.php?action=add&customerID=${this.props.customerId}`,
        //                 key: 'addProjectLink'
        //             },
        //             'Add Project'
        //         ),
        //         this.renderProjects()
        //     ]
        // )
    }
}

export default CustomerProjectsComponent;