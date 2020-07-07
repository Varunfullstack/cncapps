import React from 'react';
import Skeleton from "react-loading-skeleton";
import ReactDOM from 'react-dom';

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
        this.handleDeleteProject = this.handleDeleteProject.bind(this);
    }

    fetchCustomerProjects() {
        return fetch('/CustomerNote.php?action=getCustomerProjects&customerId=' + this.props.customerID)
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
            {},
            [
                this.renderTd('headerLightgrey', 'Name'),
                this.renderTd('headerLightgrey', 'Notes'),
                this.renderTd('headerLightgrey', 'Starts'),
                this.renderTd('headerLightgrey', 'Expires'),
                this.renderTd('headerLightgrey', ' '),
            ],
        )
    }

    renderTd(className, value) {
        return this.el(
            'td',
            {className},
            value
        )
    }

    renderDeleteLink(project) {
        if (!project.isDeletable) {
            return null;
        }
        return this.el(
            'a',
            {href: `Project.php?action=delete&projectID=${project.id}`},
            'delete'
        )
    }

    renderProjectsRows() {
        return this.state.customerProjects.map(
            project => {
                return this.el(
                    'tr',
                    {},
                    [
                        this.renderTd('content', project.name),
                        this.renderTd('content', project.notes),
                        this.renderTd('content', project.startDate),
                        this.renderTd('content', project.expiryDate),
                        this.renderTd('content',
                            [
                                this.el(
                                    'a',
                                    {
                                        href: `/Project.php?action=edit&projectID=${project.id}`
                                    },
                                    'edit'
                                ),
                                this.renderDeleteLink(project)
                            ]
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
                cellSpacing: 1
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

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerProjects');
    ReactDOM.render(React.createElement(CustomerProjectsComponent, {customerID: domContainer.dataset.customerId}), domContainer);
});