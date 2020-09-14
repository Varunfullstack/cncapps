import React from 'react';
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
        return (
            <div className="tab-pane fade"
                 id="nav-portal-documents-tab"
                 role="tabpanel"
                 aria-labelledby="nav-portal-documents-tab"
            >
                <div className="container-fluid mt-3 mb-3">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Portal Documents</h2>
                        </div>
                        <div className="col-md-12">
                            <button className="btn btn-primary mt-3 mb-3">Add Document
                            </button>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">

                            <table className="table table-striped table-bordered"
                                   width="50%"
                            >
                                <thead>
                                <tr>
                                    <td>Description</td>
                                    <td>Files</td>
                                    <td>Starters Form</td>
                                    <td>Leavers Form</td>
                                    <td>Main Contact Only</td>
                                    <td/>
                                    <td/>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        {/*<a href="{urlViewFile}"                                                                       title="View attached document"*/}
                                        {/*>{description}</a>*/}
                                    </td>
                                    <td>
                                        {/*<a href="{urlViewFile}"*/}
                                        {/*   title="View attached document"*/}
                                        {/*>{filename}</a>*/}
                                    </td>
                                    <td>{startersFormFlag}
                                    </td>
                                    <td>{leaversFormFlag}
                                    </td>
                                    <td>{mainContactOnlyFlag}
                                    </td>

                                    <td>
                                        {/*<a href="{urlEditDocument}">*/}
                                        <button className="btn btn-outline-secondary">
                                            <i className="fa fa-edit"/>
                                        </button>
                                        {/*</a>*/}
                                    </td>
                                    <td>
                                        {/*<a href="{urlDeleteDocument}"*/}
                                        {/*   title="Delete attached document"*/}
                                        {/*   onClick="if(!confirm('Are you sure you want to remove this document?')) return(false)"*/}
                                        {/*>*/}
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
        //                 href: `/PortalCustomerDocument.php?action=add&customerID=${this.props.customerId}`,
        //                 key: 'addDocumentLink'
        //             },
        //             'Add document'
        //         ),
        //         this.renderPortalDocuments()
        //     ]
        // )
    }
}

export default CustomerPortalDocumentsComponent;