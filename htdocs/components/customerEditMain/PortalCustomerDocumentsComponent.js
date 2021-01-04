import React, {Fragment} from 'react';
import {
    addNewPortalCustomerDocument,
    deletePortalCustomerDocument,
    hideNewPortalCustomerDocumentModal,
    newPortalDocumentFieldUpdate,
    showNewPortalCustomerDocumentModal
} from "./actions";
import {connect} from "react-redux";
import {
    getMappedPortalCustomerDocuments,
    getPortalCustomerDocumentsIsFetching,
    getPortalCustomerDocumentsModalShown,
    getPortalCustomerDocumentsNewPortalDocument
} from "./selectors";
import AddPortalCustomerDocumentComponent from "./modals/AddPortalCustomerDocumentComponent";

class PortalCustomerDocumentsComponent extends React.PureComponent {
    el = React.createElement;

    constructor(props) {
        super(props);
    }

    renderPortalDocumentsRows() {
        const {
            portalCustomerDocuments,
            onDeletePortalDocument,
        } = this.props;
        return portalCustomerDocuments.map(
            portalDocument => {
                return (
                    <tr key={`portalDocumentRow-${portalDocument.id}`}>
                        <td>
                            <a href={`/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${portalDocument.id}`}
                               title="View attached document"
                               target="_blank"
                            >{portalDocument.description}</a>
                        </td>
                        <td>
                            <a href={`/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${portalDocument.id}`}
                               title="View attached document"
                               target="_blank"
                            >{portalDocument.description}</a>
                        </td>
                        <td>
                            {portalDocument.customerContract ? 'Y' : 'N'}
                        </td>
                        <td>
                            {portalDocument.mainContactOnly ? 'Y' : 'N'}
                        </td>
                        <td>
                            <a href={`/PortalCustomerDocument.php?action=edit&portalCustomerDocumentID=${portalDocument.id}`}
                               target="_blank"
                            >
                                <button className="btn btn-outline-secondary">
                                    <i className="fal fa-edit fa-lg"/>
                                </button>
                            </a>
                        </td>
                        <td>
                            <a title="Delete attached document"
                               onClick={($event) => !confirm('Are you sure you want to delete this document?') ? $event.preventDefault() : onDeletePortalDocument(portalDocument.id)}
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
        // console.warn('portal customer rendered');
        const {
            newPortalDocument,
            newPortalDocumentModalShown,
            onNewPortalDocumentFieldUpdate,
            onAddNewPortalDocument,
            onHideNewPortalDocumentModal,
            onShowNewPortalDocumentModal,
            customerId
        } = this.props;
        return (
            <Fragment>
                <AddPortalCustomerDocumentComponent
                    description={newPortalDocument.description}
                    customerContract={newPortalDocument.customerContract}
                    mainContractOnly={newPortalDocument.mainContractOnly}
                    file={newPortalDocument.file}
                    show={newPortalDocumentModalShown}
                    onFieldUpdate={onNewPortalDocumentFieldUpdate}
                    onClose={onHideNewPortalDocumentModal}
                    onAdd={() => onAddNewPortalDocument(customerId, newPortalDocument)}
                />
                <div className="mt-3">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Portal Documents</h2>
                        </div>
                        <div className="col-md-12">
                            <a onClick={() => onShowNewPortalDocumentModal()}>
                                <button className="btn btn-sm btn-new mt-3 mb-3"

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
            </Fragment>

        )
    }
}


function mapStateToProps(state) {
    return {
        portalCustomerDocuments: getMappedPortalCustomerDocuments(state),
        isFetching: getPortalCustomerDocumentsIsFetching(state),
        newPortalDocument: getPortalCustomerDocumentsNewPortalDocument(state),
        newPortalDocumentModalShown: getPortalCustomerDocumentsModalShown(state)
    }
}

function mapDispatchToProps(dispatch) {
    return {
        onDeletePortalDocument: (documentId) => {
            dispatch(deletePortalCustomerDocument(documentId))
        },
        onNewPortalDocumentFieldUpdate: (field, value) => {
            dispatch(newPortalDocumentFieldUpdate(field, value))
        },
        onAddNewPortalDocument: (customerId, portalDocument) => {
            dispatch(addNewPortalCustomerDocument(customerId, portalDocument));
        },
        onHideNewPortalDocumentModal: () => {
            dispatch(hideNewPortalCustomerDocumentModal())
        },
        onShowNewPortalDocumentModal: () => {
            dispatch(showNewPortalCustomerDocumentModal())
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(PortalCustomerDocumentsComponent)