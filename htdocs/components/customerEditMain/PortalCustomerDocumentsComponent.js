import React from "react";
import { params } from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import DragAndDropUploaderComponent from "../shared/DragAndDropUploaderComponent/DragAndDropUploaderComponent";
import Table from "../shared/table/table";
import APIPortalDocuments from "../services/APIPortalDocuments";
import ToolTip from "../shared/ToolTip";
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle";
// import {
//     addNewPortalCustomerDocument,
//     deletePortalCustomerDocument,
//     hideNewPortalCustomerDocumentModal,
//     newPortalDocumentFieldUpdate,
//     showNewPortalCustomerDocumentModal
// } from "./actions";
// import {connect} from "react-redux";
// import {
//     getMappedPortalCustomerDocuments,
//     getPortalCustomerDocumentsIsFetching,
//     getPortalCustomerDocumentsModalShown,
//     getPortalCustomerDocumentsNewPortalDocument
// } from "./selectors/selectors";
// import AddPortalCustomerDocumentComponent from "./modals/AddPortalCustomerDocumentComponent";

export default class PortalCustomerDocumentsComponent extends MainComponent {
  api = new APICustomers();
  apiPortalDocument = new APIPortalDocuments();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      customerId: null,
      documents: [],
      reset: false,
      showModal: false,
      isNew: true,
      data: { ...this.getInitData() },
    };
  }

  componentDidMount() {
    this.getData();
  }

  getData = () => {
    const customerId = params.get("customerID");
    this.api.getPortalCustomerDocuments(customerId).then((res) => {
      console.log("documents-----------", res);
      this.setState({ documents: res.data, customerId });
    });
  };

  getTable = () => {
    const columns = [
      {
        path: "description",
        label: "Description",
        hdToolTip: "Description",
        icon: "pointer",
        sortable: true,
        width: 300,
        content: (document) => (
          <a
            style={{ color: "black" }}
            href={`/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${document.id}`}
            target="_blank"
          >
            {document.description}
          </a>
        ),
      },
      {
        path: "filename",
        label: "File",
        hdToolTip: "Filename",
        icon: "pointer",
        sortable: true,
        width: 300,
        content: (document) => (
          <a
            style={{ color: "black" }}
            href={`/PortalCustomerDocument.php?action=viewFile&portalCustomerDocumentID=${document.id}`}
            target="_blank"
          >
            {document.filename}
          </a>
        ),
      },
      {
        path: "customerContract",
        label: "Customer Contract",
        hdToolTip: "Customer Contract",
        icon: "pointer",
        sortable: true,
        width: 150,
        content: (document) => (
          <Toggle checked={document.customerContract}  onChange={()=>null}></Toggle>
        ),
      },
      {
        path: "mainContactOnlyFlag",
        label: "Main Contact Only",
        hdToolTip: "Main Contact Only",
        icon: "pointer",
        sortable: true,
        width: 150,
        content: (document) => (
          <Toggle checked={document.mainContactOnlyFlag} onChange={()=>null}></Toggle>
        ),
      },
      {
        path: "edit",
        label: "",
        hdToolTip: "Edit Document",
        //icon: "fal fa-2x fa-signal color-gray2 pointer",
        sortable: false,
        content: (document) =>
          this.getEditElement(document, () => this.handleEdit(document)),
      },
      {
        path: "delete",
        label: "",
        hdToolTip: "Delete Document",
        //icon: "fal fa-2x fa-signal color-gray2 pointer",
        sortable: false,
        content: (document) =>
          this.getDeleteElement(
            document,
            () => this.handleDelete(document),
            document.isDeletable
          ),
      },
    ];
    return (
      <Table
        key="documents"
        pk="id"
        style={{ maxWidth: 1300 }}
        columns={columns}
        data={this.state.documents || []}
        search={true}
      ></Table>
    );
  };

  getInitData() {
    return {
      id: "",
      description: "",
      filename: "",
      customerContract: "",
      mainContactOnlyFlag: "",
      file:null
    };
  }

  handleEdit = (document) => {
    console.log("Edit Document", document);
    this.setState({ data: { ...document }, showModal: true, isNew: false });
    //window.location=`PortalCustomerDocument.php?action=edit&portalCustomerDocumentID=${document.id}`;
  };

  handleDelete = async (document) => {
    console.log("Delete document", document);
    if (await this.confirm("Are you sure you want to delete this document?")) {
      this.apiPortalDocument.deletePortalDocument(document.id).then((res) => {
        console.log(res);
        this.getData();
      });
    }
  };

  handleNewItem = () => {
    this.setState({
      showModal: true,
      isNew: true,
      data: { ...this.getInitData() },
    });
  };

  handleClose = () => {
    this.setState({ showModal: false });
  };

  handleSave = () => {
    const { data, isNew } = this.state;
    if (!data.description) {
      this.alert("Please enter description");
      return;
    }
    if (!isNew) {
      this.apiPortalDocument.updateDocument(data).then((res) => {
        if (res.state) {
          this.setState({ showModal: false, reset: true }, () =>
            this.getData()
          );
        }
      });
    } else {
      data.id = null;
      this.apiPortalDocument.addItem(data).then((res) => {
        if (res.state) {
          this.setState({ showModal: false, reset: true }, () =>
            this.getData()
          );
        }
      });
    }
  };

  getModal = () => {
    const { isNew, showModal } = this.state;
    if (!showModal) return null;
    return (
      <Modal
        width={500}
        title={isNew ? "Create Document" : "Update Document"}
        show={showModal}
        content={this.getModalContent()}
        footer={
          <div key="footer">
            <button onClick={this.handleClose} className="btn btn-secodary">
              Cancel
            </button>
            <button onClick={this.handleSave}>Save</button>
          </div>
        }
        onClose={this.handleClose}
      ></Modal>
    );
  };
  handleFileSelected = (files) => {
    console.log(files);
    this.setValue("file",files[0]);
    
  };
  getModalContent = () => {
    const { data } = this.state;    
    return (
      <div key="content">
        <table className="table">
          <tbody>
            <tr>
              <td className="text-right">Description</td>
              <td>
                <input
                  required
                  value={data.description}
                  onChange={(event) =>
                    this.setValue("description", event.target.value)
                  }
                  className="form-control"
                />
              </td>
            </tr>
            <tr>
              <td className="text-right">Customer Contract?</td>
              <td align="left">
                <Toggle
                  checked={data.customerContract}
                  onChange={() =>
                    this.setValue("customerContract", !data.customerContract)
                  }
                ></Toggle>
              </td>
            </tr>
            <tr>
              <td className="text-right">Main Contact Only?</td>
              <td align="left">
                <Toggle
                  checked={data.mainContactOnlyFlag}
                  onChange={() =>
                    this.setValue(
                      "mainContactOnlyFlag",
                      !data.mainContactOnlyFlag
                    )
                  }
                ></Toggle>
              </td>
            </tr>
            <tr>
              <td className="text-right">Document</td>
              <td>
                <div>
                  <DragAndDropUploaderComponent
                    iconStyle={{ color: "white" }}
                    onFilesChanged={(files, type) =>
                      this.handleFileSelected(files, type)
                    }
                  ></DragAndDropUploaderComponent>
                  
                </div>
              </td>
            </tr>
            <tr>
                <td></td>
                <td>{data.file ? (
                    <div className="flex-row">
                      <span>{data.file.name}</span>
                      <i className="fa fa-close pointer ml-5" onClick={()=>this.setValue("file",null)}></i>
                    </div>
                  ) : null}</td>
            </tr>
          </tbody>
        </table>
      </div>
    );
  };

  render() {
    return (
      <div>
        <Spinner show={this.state.showSpinner} />
        <ToolTip title="New Item" width={30}>
          <i
            className="fal fa-2x fa-plus color-gray1 pointer"
            onClick={this.handleNewItem}
          />
        </ToolTip>
        {this.getConfirm()}
        {this.getTable()}
        <div className="modal-style">{this.getModal()}</div>
      </div>
    );
  }
  /*
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
    }*/

  //render() {
  //return <div>New portal2</div>
  // console.warn('portal customer rendered');
  /* const {
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

        )*/
  //}
}

/*
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

export default connect(mapStateToProps, mapDispatchToProps)(PortalCustomerDocumentsComponent)*/
