import React from 'react';
import ToolTip from "../../shared/ToolTip";
import DragAndDropUploaderComponent from "../../shared/DragAndDropUploaderComponent/DragAndDropUploaderComponent";
import Table from "../../shared/table/table";
import APIActivity from "../../services/APIActivity";
import MainComponent from "../../shared/MainComponent";
import './InternalDocumentsComponent.css';
import CNCFileViewer from './../../shared/CNCFileViewer';
import { getFileExt } from './../../utils/utils';

export class InternalDocumentsComponent extends MainComponent {
    api = new APIActivity();

    constructor(props, context) {
        super(props, context);
        this.state = {
            ...this.state,
            documents: [],
            uploadFiles: []
        }
    }

    componentDidMount() {
        this.loadDocuments(this.props.serviceRequestId);
    }

    async loadDocuments(serviceRequestId) {
        const documents = await this.api.getDocumentsForServiceRequest(serviceRequestId);
        this.setState({documents});
    }

    async deleteDocument(id) {
        if (await this.confirm('Are you sure you want to remove this document?')) {
            await this.api.deleteInternalDocument(id);
            this.setState({documents: [...this.state.documents.filter(d => d.id !== id)]});
        }
    }

    async handleUpload() {
        const {uploadFiles} = this.state;
        const {serviceRequestId} = this.props;
        await this.api.addServiceRequestFiles(serviceRequestId, uploadFiles)
        this.setState({uploadFiles: []});
        this.loadDocuments(serviceRequestId);
    }

    getSelectedFilesElement() {
        const {uploadFiles} = this.state;
        if (uploadFiles) {
            const names = uploadFiles.map(x => x.name).join(", ");
            return <label className="ml-5">{names}</label>
        }
        return null;
    }

    handleFileSelected(files) {
        const uploadFiles = [...files];
        this.setState({uploadFiles})
    }

    render() {

        const {uploadFiles, documents} = this.state;
        let columns = [
            {
                path: "originalFileName",
                label: "File",
                sortable: false,
                class: 'align-left',
                hdClassName: 'align-left',
                content: (document) =>(
                        <CNCFileViewer     
                        key={document.id}
                        style={{maxWidth:700,maxHeight: 500}}          
                        type={getFileExt(document.storedFileName)}
                        filePath={`SRActivity.php?action=viewInternalDocument&documentId=${document.id}&viewer`}
                         >
                        <a target="_blank"
                       href={`SRActivity.php?action=viewInternalDocument&documentId=${document.id}`}
                    >{document.originalFileName}</a>
                         </CNCFileViewer>
                        )
            },
            {
                path: "createdAt",
                label: "Date",
                sortable: false,
            },
            {
                path: "delete",
                label: "",
                sortable: false,
                content: (document) => (
                    <i className="fal fa-trash-alt pointer icon font-size-4"
                       onClick={() => this.deleteDocument(document.id)}
                    />
                )
            },
        ]

        return (

            <div className="round-container internal-documents-component"
                 style={{position: "relative"}}
            >
                {this.getConfirm()}
                <div className="flex-row">
                    <label className="label  mt-5 mr-3 ml-1 mb-5"
                           style={{display: "block"}}
                    >
                        Internal Documents
                    </label>
                    <ToolTip width="15"
                             title="Documents here are not visible to the customer in their portal."
                    >
                        <i className="fal fa-info-circle mt-5 pointer icon"/>
                    </ToolTip>
                </div>
                <DragAndDropUploaderComponent onFilesChanged={(files, type) => this.handleFileSelected(files, type)}
                >
                    {this.getSelectedFilesElement()}
                    {
                        uploadFiles.length > 0 && (
                            <ToolTip width="30"
                                     title="Upload documents"
                            >
                                <i className="fal fa-upload pointer icon font-size-4"
                                   onClick={() => this.handleUpload()}
                                />
                            </ToolTip>
                        )
                    }
                    {
                        documents?.length > 0 && (
                            <Table id="documents"
                                   data={documents || []}
                                   columns={columns}
                                   pk="id"
                                   search={false}
                            />
                        )
                    }

                </DragAndDropUploaderComponent>
            </div>
        )
    }
}