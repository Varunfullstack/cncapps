import React from 'react';

class CustomerPortalDocumentsComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customerPortalDocuments: [],
            customerId: props.customerId,
        };
    }

    fetchCustomerPortalDocuments() {
        return fetch('?action=getCustomerPortalDocuments&customerId=' + this.props.customerId)
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

    renderPortalDocumentsRows() {
        return this.state.customerPortalDocuments.map(
            portalDocument => {
                return (<tr key={`portalDocumentRow-${portalDocument.id}`}>
                    <td>
                        <a href={`/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${portalDocument.id}`}
                           title="View attached document"
                        >{portalDocument.description}</a>
                    </td>
                    <td>
                        <a href={`/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${portalDocument.id}`}
                           title="View attached document"
                        >{portalDocument.description}</a>
                    </td>
                    <td>
                        {portalDocument.customerContract ? 'Y' : 'N'}
                    </td>
                    <td>
                        {portalDocument.mainContactOnly ? 'Y' : 'N'}
                    </td>
                    <td>
                        <a href={`/PortalCustomerDocument.php?action=edit&portalCustomerDocumentID=${portalDocument.id}`}>
                            <button className="btn btn-outline-secondary">
                                <i className="fa fa-edit"/>
                            </button>
                        </a>
                    </td>
                    <td>
                        <a href={`/PortalCustomerDocument.php?action=delete&portalCustomerDocumentID=${portalDocument.id}`}
                           title="Delete attached document"
                           onClick={() => !confirm('Are you sure you want to remove this document?')}
                        >
                            <button className="btn btn-outline-danger">
                                <i className="fa fa-trash"/>
                            </button>
                        </a>
                    </td>
                </tr>);
            }
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
                            <a href={`/PortalCustomerDocument.php?action=add&customerID=${this.props.customerId}`}>
                                <button className="btn btn-primary mt-3 mb-3">
                                    Add Document
                                </button>
                            </a>
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
                                    <td>Customer Contract</td>
                                    <td>Main Contact Only</td>
                                    <td/>
                                    <td/>
                                </tr>
                                </thead>
                                <tbody>
                                {this.renderPortalDocumentsRows()}
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>

            </div>
        )
    }
}

export default CustomerPortalDocumentsComponent;