import React from 'react';
import Skeleton from "react-loading-skeleton";
import ReactDOM from 'react-dom';
import * as HTMLReactParser from 'html-react-parser'

class CustomerPortalDocumentsComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customerPortalDocuments: [],
            customerId: props.customerID,
        };
        document.customerPortalDocumentsComponent = this;
    }

    fetchCustomerPortalDocuments() {
        return fetch('?action=getCustomerPortalDocuments&customerId=' + this.props.customerID)
            .then(response => response.json())
            .then(response => this.setState({customerPortalDocuments: response.data}));
    }

    componentDidMount() {
        this.fetchCustomerPortalDocuments()
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
                this.renderTd('listHeadText', 'Description', 'headerName'),
                this.renderTd('listHeadText', 'File', 'headerNotes'),
                this.renderTd('listHeadText', 'Customer Contract', 'headerStarts'),
                this.renderTd('listHeadText', 'Main Contact Only', 'headerExpires'),
                this.renderTd('listHeadText', ' ', 'headerActions', false, {colSpan: 2}),
            ],
        )
    }

    renderTd(className, value, key, isComplex = false, attributes = {}) {
        return this.el(
            'td',
            {className, key, ...attributes},
            (value && !isComplex && HTMLReactParser.default(value)) || value
        )
    }


    renderPortalDocumentsRows() {
        return this.state.customerPortalDocuments.map(
            portalDocument => {
                return this.el(
                    'tr',
                    {
                        key: `portalDocumentRow-${portalDocument.id}`
                    },
                    [
                        this.renderTd('content',
                            this.el(
                                'a',
                                {
                                    href: `/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${portalDocument.id}`,
                                },
                                portalDocument.description
                            ),
                            `description-${portalDocument.id}`,
                            true
                        ),
                        this.renderTd('content',
                            this.el(
                                'a',
                                {
                                    href: `/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${portalDocument.id}`,
                                },
                                portalDocument.description
                            ),
                            `file-${portalDocument.id}`,
                            true
                        ),
                        this.renderTd('content', portalDocument.customerContract ? 'Y' : 'N', `customerContract-${portalDocument.id}`),
                        this.renderTd('content', portalDocument.mainContactOnly ? 'Y' : 'N', `notes-${portalDocument.id}`),
                        this.renderTd('content',
                            this.el(
                                'a',
                                {
                                    href: `/PortalCustomerDocument.php?action=edit&portalCustomerDocumentID=${portalDocument.id}`,
                                },
                                portalDocument.description
                            ),
                            `edit-${portalDocument.id}`,
                            true
                        ),
                        this.renderTd('content',
                            this.el(
                                'a',
                                {
                                    href: `/PortalCustomerDocument.php?action=delete&portalCustomerDocumentID=${portalDocument.id}`,
                                },
                                portalDocument.description
                            ),
                            `delete-${portalDocument.id}`,
                            true
                        ),
                    ]
                )
            }
        )
    }

    renderPortalDocuments() {
        return this.el(
            'table',
            {
                className: 'singleBorder',
                border: 0,
                cellPadding: 2,
                cellSpacing: 1,
                key: 'portalDocumentsTable'
            },
            this.el(
                'tbody',
                {},
                [
                    this.renderHeadingRow(),
                    ...this.renderPortalDocumentsRows()
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
                        href: `/PortalCustomerDocument.php?action=add&customerID=${this.props.customerId}`,
                        key: 'addDocumentLink'
                    },
                    'Add document'
                ),
                this.renderPortalDocuments()
            ]
        )
    }
}

export default CustomerPortalDocumentsComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactCustomerPortalDocuments');
    ReactDOM.render(React.createElement(CustomerPortalDocumentsComponent, {customerID: domContainer.dataset.customerId}), domContainer);
});