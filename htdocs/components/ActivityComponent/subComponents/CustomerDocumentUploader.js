import React from 'react';
import ToolTip from "../../shared/ToolTip";
import DragAndDropUploaderComponent from "../../shared/DragAndDropUploaderComponent/DragAndDropUploaderComponent";
import Table from "../../shared/table/table";
import APIActivity from "../../services/APIActivity";

import './CustomerDocumentUploader.css';

export default class CustomerDocumentUploader extends React.PureComponent {
    api = new APIActivity();

    constructor(props, context) {
        super(props, context);
        this.state = {
            uploadFiles: []
        }
    }

    async deleteDocument(id) {
        const {onDeleteDocument} = this.props;
        onDeleteDocument(id);
    }

    async handleUpload() {
        const {uploadFiles} = this.state;
        const {onFilesUploaded, serviceRequestId, activityId} = this.props;
        await this.api.uploadFiles(
            `Activity.php?action=uploadFile&problemID=${serviceRequestId}&callActivityID=${activityId}`,
            uploadFiles,
            "userfile[]"
        );
        this.setState({uploadFiles: []});
        if (onFilesUploaded) {
            onFilesUploaded();
        }
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
        const {uploadFiles} = this.state;
        const {documents} = this.props;

        let columns = [
            {
                path: "Description",
                label: "Description",
                sortable: false,
                content: (document) => (
                    <a href={`Activity.php?action=viewFile&callDocumentID=${document.id}`}
                       target="_blank"
                    >{document.description}</a>)
            },
            {
                path: "File",
                label: "File",
                sortable: false,
                content: (document) => (
                    <a href={`Activity.php?action=viewFile&callDocumentID=${document.id}`}
                       target="_blank"
                    >{document.filename}</a>)
            },
            {
                path: "createDate",
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
            <div className="round-container customer-documents-uploader"
                 style={{position: "relative"}}
            >
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
}