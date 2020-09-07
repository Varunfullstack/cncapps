import React from 'react';
import Skeleton from "react-loading-skeleton";
import ReactDOM from 'react-dom';
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
        if (!this.state.loaded) {
            return this.el(
                Skeleton,
                null,
                'Loading Data'
            );
        }


        return this.el(
            'div',
            {},
            [
                this.el(
                    'a',
                    {
                        href: `/Project.php?action=add&customerID=${this.props.customerId}`,
                        key: 'addProjectLink'
                    },
                    'Add Project'
                ),
                this.renderProjects()
            ]
        )
    }
}

export default CustomerProjectsComponent;