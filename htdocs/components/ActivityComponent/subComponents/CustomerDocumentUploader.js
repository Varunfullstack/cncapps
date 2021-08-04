import React from 'react';
import ToolTip from "../../shared/ToolTip";
import DragAndDropUploaderComponent from "../../shared/DragAndDropUploaderComponent/DragAndDropUploaderComponent";
import Table from "../../shared/table/table";
import APIActivity from "../../services/APIActivity";

import './CustomerDocumentUploader.css';
import moment from "moment";
import {dateFormatExcludeNull, getFileSize} from "../../utils/utils";
 import { getFileExt } from './../../utils/utils';
import CNCFileViewer from "../../shared/CNCFileViewer"
import MainComponent from '../../shared/MainComponent';
export default class CustomerDocumentUploader extends MainComponent {
    api = new APIActivity();

    constructor(props, context) {
        super(props, context);
        this.state = {
            ...this.state,
            uploadFiles: [],
            documents: []
        }
    }

    componentDidMount() {
        this.fetchDocuments();
    }


    async deleteDocument(id) {
        if (await this.confirm('Are you sure you want to remove this document?')) {
            await this.api.deleteDocument(id);
            this.fetchDocuments();
        }
    }

    async handleUpload() {
        const {uploadFiles} = this.state;
        const {serviceRequestId} = this.props;
        await this.api.uploadFiles(
            `SRActivity.php?action=uploadCustomerDocuments&serviceRequestId=${serviceRequestId}`,
            uploadFiles,
            "userfile[]"
        );
        this.setState({uploadFiles: []});
        this.fetchDocuments();
    }

    getSelectedFilesElement() {
        const {uploadFiles} = this.state;
        if (uploadFiles) {
            const names = uploadFiles.map(x => x.name).join(", ");
            return <label className="ml-5">{names}</label>
        }
        return null;
    }

    handleFileSelected(files, type) {
        const uploadFiles = [...files];
        this.setState({uploadFiles})
    }

    render() {
        const {uploadFiles, documents} = this.state;

        let columns = [
            {
                path: "Description",
                label: "Description",
                sortable: false,
                content: (document) => (
                    <CNCFileViewer      
                    key={document.id}
                    style={{maxWidth:700,maxHeight: 500}}          
                    type={getFileExt(document.filename)}
                    filePath={`Activity.php?action=getFileBinary&callDocumentID=${document.id}`}
                     >
                    <a href={`Activity.php?action=viewFile&callDocumentID=${document.id}`}
                       target="_blank"
                    >{document.description}</a>
                     </CNCFileViewer>
                    )
            },
            {
                path: "File",
                label: "File",
                sortable: false,
                content: (document) =>(
                    <CNCFileViewer      
                    key={document.id}
                    style={{maxWidth:700,maxHeight: 500}}          
                    type={getFileExt(document.filename)}
                    filePath={`Activity.php?action=getFileBinary&callDocumentID=${document.id}`}
                     >
                    <a href={`Activity.php?action=viewFile&callDocumentID=${document.id}`}
                       target="_blank"
                    >{document.filename}</a>
                     </CNCFileViewer>
                    )
                     
            },
            {
                path: "createDate",
                label: "Date",
                sortable: false,
                content: document => {
                    return dateFormatExcludeNull(document.createDate, 'YYYY-MM-DD HH:mm:ss')
                }
            },
            {
                path: "fileLength",
                label: "Size",
                sortable: false,
                content: document => getFileSize(document.fileLength)
                
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
            <div className="round-container customer-documents-uploader"
                 style={{position: "relative"}}
            >
                {this.getConfirm()}
                <div className="flex-row">
                    <label className="label  mt-5 mr-3 ml-1 mb-5"
                           style={{display: "block"}}
                    >
                        Customer Documents
                    </label>
                    <ToolTip width="15"
                             title="Documents here are visible to the customer in their portal."
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

    async fetchDocuments() {
        const {serviceRequestId} = this.props;
        const documents = await this.api.getServiceRequestCustomerDocuments(serviceRequestId);
        this.setState({documents});
    }
}