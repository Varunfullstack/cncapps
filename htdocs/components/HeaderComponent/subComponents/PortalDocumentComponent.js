"use strict";
import React from 'react';
import APIPortalDocuments from '../../services/APIPortalDocuments';
import MainComponent from '../../shared/MainComponent';
import Modal from '../../shared/Modal/modal';
import Toggle from '../../shared/Toggle';
import ToolTip from '../../shared/ToolTip';

export default class PortalDocumentComponent extends MainComponent {
    api = new APIPortalDocuments();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            data: {
                ...this.getInitData()
            },
            dosuments: [],
            showModal: false,
            files: []
        };
    }

    getInitData = () => {
        return {
            portalDocumentID: null,
            description: "",
            mainContactOnlyFlag: "",
            requiresAcceptanceFlag: "",
        };
    }

    componentDidMount() {
        this.getData();
    }

    getData = () => {
        this.api.getPortalDocuments().then(res => {
            if (res.state)
                this.setState({dosuments: res.data});
        })
    }
    getDocuments = () => {
        const {dosuments} = this.state;
        return <table className="table table-striped" style={{maxWidth: 900}}>
            <thead>
            <tr>
                <th>Description</th>
                <th>File</th>
                <th>Main Contact Only</th>
                <th>Requires Acceptance?</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {
                dosuments.map(file => {
                    return <tr key={file.portalDocumentID}>
                        <td>{file.description}</td>
                        <td>
                            <a href={file.urlViewFile}>
                                {file.filename}
                            </a>

                        </td>
                        <td className="text-center">
                            <Toggle disabled={true} checked={file.mainContactOnlyFlag == "Y"}>
                            </Toggle>
                        </td>
                        <td className="text-center">
                            <Toggle disabled={true} checked={file.requiresAcceptanceFlag == "Y"}>
                            </Toggle>
                        </td>
                        <td>
                            {this.getEditElement(file, this.handleEdit)}
                        </td>
                        <td>
                            {this.getDeleteElement(file, this.handleDelete)}
                        </td>
                    </tr>
                })
            }
            </tbody>
        </table>
    }
    handleEdit = (file) => {
        this.setState({data: {...file}, showModal: true});
    }
    handleDelete = async (file) => {
        if (await this.confirm("Are you sure to delete this document?")) {
            this.api.deletePortalDocuments(file.portalDocumentID).then(res => {
                if (res.state) {
                    this.setState({showModal: false, data: null});
                    this.getData();
                }
            })
        }
    }
    getFileDetailsModal = () => {
        const {data, showModal} = this.state;
        if (!showModal)
            return null;
        return <Modal
            width={400}
            show={this.state.showModal}
            title={data ? "Edit Document" : "New Document"}
            onClose={this.handleCloseModal}
            footer={<div key="footer">
                <button onClick={this.handleSave}>Save</button>
            </div>}
        >
            <table className="table" key="tableContent">
                <tbody>
                <tr>
                    <td>Description</td>
                    <td>
                        <input className="form-control" value={data?.description}
                               onChange={(event) => this.setValue("description", event.target.value)}></input>
                    </td>
                </tr>
                <tr>
                    <td>Main Contact Only?</td>
                    <td>
                        <Toggle checked={data?.mainContactOnlyFlag == "Y"}
                                onChange={() => this.setValue("mainContactOnlyFlag", data.mainContactOnlyFlag == "Y" ? "N" : "Y")}>
                        </Toggle>
                    </td>
                </tr>
                <tr>
                    <td>Requires Acceptance?</td>
                    <td>
                        <Toggle checked={data?.requiresAcceptanceFlag == "Y"}
                                onChange={() => this.setValue("requiresAcceptanceFlag", data.requiresAcceptanceFlag == "Y" ? "N" : "Y")}>
                        </Toggle>
                    </td>
                </tr>
                <tr>
                    <td>File</td>
                    <td>
                        <input name="userfile" type="file" onChange={this.handleFileSelect}></input>
                    </td>
                </tr>
                </tbody>
            </table>

        </Modal>
    }
    handleFileSelect = (event) => {
        this.setState({"files": event.target.files});
    }
    handleSave = () => {
        const {data, files} = this.state;
        if (!data.portalDocumentID && files.length == 0)
            this.alert("Please enter a file path")
        else
            this.api
                .updateDocument(
                    {
                        portalDocumentID: data.portalDocumentID,
                        description: data.description,
                        mainContactOnlyFlag: data.mainContactOnlyFlag,
                        requiresAcceptanceFlag: data.requiresAcceptanceFlag,
                    },
                    files
                )
                .then(
                    (res) => {
                        if (res.state) {
                            this.setState({showModal: false, data: null});
                            this.getData();
                        }
                    },
                    (err) => {
                        console.log("error", err)
                        this.alert(err.error)
                    }
                );

    }
    handleCloseModal = () => {
        this.setState({showModal: false, data: null})
    }
    handleNewDocument = () => {
        this.setState({showModal: true, data: {...this.getInitData()}})
    }

    render() {
        return (
            <div>
                {this.getConfirm()}
                {this.getAlert()}
                {this.getFileDetailsModal()}
                <ToolTip title="Add new portal document" width={30}>
                    <i className="fal fa-2x fa-plus pointer" onClick={this.handleNewDocument}></i>
                </ToolTip>
                {this.getDocuments()}
            </div>
        );
    }
}
 