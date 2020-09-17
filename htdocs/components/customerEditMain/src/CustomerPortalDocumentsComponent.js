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
                return (
                    <tr key={`portalDocumentRow-${portalDocument.id}`}>
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
                                    <i className="fal fa-edit fa-lg"/>
                                </button>
                            </a>
                        </td>
                        <td>
                            <a href={`/PortalCustomerDocument.php?action=delete&portalCustomerDocumentID=${portalDocument.id}`}
                               title="Delete attached document"
                               onClick={($event) => !confirm('Are you sure you want to delete this document?') ? $event.preventDefault() : null}
                            >
                                <button className="btn btn-outline-danger">
                                    <i className="fal fa-trash-alt fa-lg"/>
                                </button>
                            </a>
                        </td>
                    </tr>
                );
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
                <div className="mt-3">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Portal Documents</h2>
                        </div>
                        <div className="col-md-12">
                            <a>
                                <button className="btn btn-sm btn-new mt-3 mb-3"
                                        data-toggle="modal"
                                        data-target="#portalDocumentsModal"
                                >Add Document
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