import React from "react";
import {params} from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import DragAndDropUploaderComponent from "../shared/DragAndDropUploaderComponent/DragAndDropUploaderComponent";
import Table from "../shared/table/table";
import APIPortalDocuments from "../services/APIPortalDocuments";
import ToolTip from "../shared/ToolTip";
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle";

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
            data: {...this.getInitData()},
            showSpinner: true
        };
    }

    componentDidMount() {
        this.getData();
    }

    getData = () => {
        const customerId = params.get("customerID");
        this.setState({showSpinner: true})
        this.apiPortalDocument.getPortalDocuments(customerId).then((res) => {
            this.setState({documents: res.data, customerId, showSpinner: false});
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
                        style={{color: "black"}}
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
                        style={{color: "black"}}
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
                    <Toggle checked={document.customerContract} onChange={() => null}></Toggle>
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
                    <Toggle checked={document.mainContactOnlyFlag} onChange={() => null}></Toggle>
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
                style={{maxWidth: 1300}}
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
            file: null
        };
    }

    handleEdit = (document) => {
        this.setState({data: {...document}, showModal: true, isNew: false});
    };

    handleDelete = async (document) => {
        if (await this.confirm("Are you sure you want to delete this document?")) {
            this.apiPortalDocument.deletePortalDocument(document.id).then((res) => {
                this.getData();
            }, error => {
                this.alert(error)
            });
        }
    };

    handleNewItem = () => {
        this.setState({
            showModal: true,
            isNew: true,
            data: {...this.getInitData()},
        });
    };

    handleClose = () => {
        this.setState({showModal: false});
    };

    handleSave = async () => {
        const {data, isNew, customerId} = this.state;
        if (!data.description) {
            this.alert("Please enter description");
            return;
        }
        if (!isNew) {
            const res = await this.apiPortalDocument.updateDocument(data);
            if (res.state) {
                if (data.file)
                    await this.apiPortalDocument.uploadDocument(data.id, data.file);
                this.setState({showModal: false, reset: true}, () =>
                    this.getData()
                );
            }
        } else {
            data.id = null;
            data.customerID = customerId;
            const res = await this.apiPortalDocument.addDocument(data);
            if (res.state) {
                if (data.file)
                    await this.apiPortalDocument.uploadDocument(res.data.documentID, data.file);
                this.setState({showModal: false, reset: true}, () => this.getData());
            }
        }
    };

    getModal = () => {
        const {isNew, showModal} = this.state;
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
        this.setValue("file", files[0]);

    };
    getModalContent = () => {
        const {data} = this.state;
        return (
            <div key="content" style={{height: 150}}>
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
                                    iconStyle={{color: "white"}}
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
                                <i className="fa fa-close pointer ml-5" onClick={() => this.setValue("file", null)}></i>
                            </div>
                        ) : null}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        );
    };

    render() {
        if (this.state.showSpinner)
            return <Spinner show={this.state.showSpinner}/>
        return (
            <div>
                {this.getConfirm()}
                {this.getAlert()}
                <div className="m-5">
                    <ToolTip title="New Item" width={30}>
                        <i
                            className="fal fa-2x fa-plus color-gray1 pointer"
                            onClick={this.handleNewItem}
                        />
                    </ToolTip>
                </div>
                {this.getTable()}

                <div className="modal-style">{this.getModal()}</div>
            </div>
        );
    }
}
 