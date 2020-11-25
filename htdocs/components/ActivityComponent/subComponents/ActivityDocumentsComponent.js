import MainComponent from "../../shared/MainComponent.js";
import APIActivity from "../../services/APIActivity.js";
import Table from "../../shared/table/table";
import ToolTip from "../../shared/ToolTip.js";

import React    from 'react';
class ActivityDocumentsComponent extends MainComponent {
    el = React.createElement;
    api = new APIActivity();

    constructor(props) {
        super(props);
        this.state = {...this.state, uploadFiles: []}
        this.fileUploader = new React.createRef();

    }

    getDocumentsElement = () => {
        const {uploadFiles} = this.state;
        const {documents} = this.props;
        const {el} = this;
        let columns = [
            {
                path: "Description",
                label: "Description",
                sortable: false,
                className: "text-align-center",
                content: (document) =>
                    el(
                        "a",
                        {
                            href: `Activity.php?action=viewFile&callDocumentID=${document.id}`,
                        },
                        document.description
                    ),
            },
            {
                path: "File",
                label: "File",
                sortable: false,
                className: "text-align-center",
                content: (document) =>
                    el(
                        "a",
                        {
                            href: `Activity.php?action=viewFile&callDocumentID=${document.id}`,
                        },
                        document.filename
                    ),
            },
            {
                path: "createDate",
                label: "Date",
                sortable: false,
                className: "text-align-center",
            },
            {
                path: "createUserName",
                label: "User",
                sortable: false,
                className: "text-align-center",
            },

            {
                path: "delete",
                label: "",
                sortable: false,
                content: (document) =>
                    el(ToolTip, {
                        title: "Delete the document", content:
                            el("i", {
                                className: "fal fa-trash-alt pointer icon",
                                style: {fontSize: 16},
                                onClick: () => this.deleteDocument(document.id),
                            })
                    }),
            },
        ];
        return el(
            "div",
            {className: "contianer-round ", style: {marginBottom: 30}},
            this.getConfirm(),
            el('div', {className: "flex-row"},
                el("label", {className: "label m-5", style: {display: "block"}}, "Documents"),
                el(ToolTip, {
                    title: "Select Files to uploads", content: el("i", {
                        className: "fal fa-plus pointer icon mt-5",
                        style: {fontSize: 16},
                        onClick: this.handleSelectFiles,
                    })
                }),
                el("input", {
                    ref: this.fileUploader,
                    name: "usefile",
                    type: "file",
                    style: {display: "none"},
                    multiple: "multiple",
                    onChange: this.handleFileSelected,
                }),
                this.getSelectedFilesElement(),
                uploadFiles.length > 0
                    ? el(ToolTip, {
                        title: "Upload Files", content:
                            el("i", {
                                className: "fal fa-upload pointer icon m-5",
                                style: {fontSize: 16},
                                onClick: this.handleUpload,
                            })
                    })
                    : null
            ),
            documents?.length > 0
                ? el(Table, {
                    id: "documents",
                    data: documents,
                    columns: columns,
                    pk: "id",
                    search: false,
                })
                : null,
        );
    };
    getSelectedFilesElement = () => {
        const {uploadFiles} = this.state;
        if (uploadFiles) {
            let names = "";

            for (let i = 0; i < uploadFiles.length; i++) {
                names += uploadFiles[i].name + "  ,";
            }
            names = names.substr(0, names.length - 2);
            return this.el("label", {className: "ml-5 mt-5"}, names);
        }
        return null;
    };
    handleUpload = async () => {
        const {uploadFiles} = this.state;
        const {problemID, callActivityID} = this.props;
        if (problemID) {
            const result = await this.api.uploadFiles(
                `Activity.php?action=uploadFile&problemID=${problemID}&callActivityID=${callActivityID}`,
                uploadFiles,
                "userfile[]"
            );

            this.setState({uploadFiles: []});
            if (this.props.onUpload)
                this.props.onUpload(result);
        }

    };
    handleFileSelected = (e) => {
        const uploadFiles = [...e.target.files];
        this.setState({uploadFiles});
    };
    handleSelectFiles = () => {
        this.fileUploader.current.click();
    };
    deleteDocument = async (id) => {

        if (await this.confirm("Are you sure you want to remove this document?")) {
            await this.api.deleteDocument(this.state.currentActivity, id);
            if (this.props.onDelete)
                this.props.onDelete();
        }
    };

    render() {
        return (this.getDocumentsElement());
    }
}

export default ActivityDocumentsComponent;