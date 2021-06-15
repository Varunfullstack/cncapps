import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import ToolTip from "../shared/ToolTip";
import React from "react";
import ReactDOM from "react-dom";

import "./../style.css";
import "./KeywordMatchingIgnoresComponent.css";
import APIKeywordMatchingIgnores from "./services/APIKeywordMatchingIgnores";
import Modal from "../shared/Modal/modal";


class KeywordMatchingIgnoresComponent extends MainComponent {
    api = new APIKeywordMatchingIgnores();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            words: [],
            showModal: false,
            insert: true,
            data: {
                id: null,
                word: "",
            },

        };
    }

    componentDidMount() {
        this.getData();
    }

    getData = () => {
        this.setState({showSpinner: true});
        this.api.getWords().then((words) => {
            this.setState({showSpinner: false, words});
        });
    };

    getNewWordModal = () => {
        const {data, showModal, insert} = this.state;
        return (
            <Modal
                title={insert ? "Add Keyword" : "Edit Keyword"}
                width={300}
                show={showModal}
                footer={
                    <div key="footer" style={{display: "flex", justifyContent: "space-between"}}>
                        <button onClick={this.handleSave}>Save</button>
                        <button onClick={() => this.setState({showModal: false})}>Cancel</button>
                    </div>
                }
                onClose={() => this.setState({showModal: false})}>
                <div>
                    <div className="form-group">
                        <label>Word</label>
                        <input
                            value={data.word}
                            onChange={(event) => this.setValue("word", event.target.value)}
                            className="formControl"
                        ></input>
                    </div>
                </div>
            </Modal>
        );
    };

    handleSave = () => {
        const {data, insert} = this.state;
        let callApi;
        if (insert)
            callApi = this.api.AddWord(data);
        else
            callApi = this.api.UpdateWord(data);
        callApi.then(res => {
            if (res.state) {
                this.setState({showModal: false})
                this.getData();
            } else {
                this.alert(res.error);

            }

        })
    }
    getDataTable = () => {
        const columns = [
            {
                path: "word",
                label: "",
                hdToolTip: "Word",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                width: 290
            },
            {
                path: "",
                label: "",
                //hdToolTip: "Edit",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-edit color-gray2 pointer",
                key: "edit",
                content: (word) => this.getEditElement(word, this.handleEdit)
            },
            {
                path: "delete",
                label: "",
                hdToolTip: "Delete",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-trash color-gray2 pointer",
                content: (word) => this.getDeleteElement(word, this.handleDelete)
            },
        ];
        return <Table
            columns={columns}
            data={this.state.words || []}
            pk="id"
            search={true}
        >

        </Table>
    }
    handleEdit = (obj) => {
        this.setState({showModal: true, insert: false, data: {...obj}})
    }
    handleDelete = (obj) => {
        this.api.deleteWord(obj.id).then(res => {
            if (res) {
                this.setState({showModal: false})
                this.getData();
            }
        })
    }

    render() {
        return (
            <div>
                {this.getAlert()}
                <ToolTip title="Add Word" width={30}>
                    <i className="fas fa-plus fa-2x"
                       onClick={() => this.setState({showModal: true, data: {id: null, word: ''}})}></i>
                </ToolTip>
                {this.getNewWordModal()}
                <div style={{width: 500, marginTop: 10}}>
                    {this.getDataTable()}
                </div>

            </div>
        );
    }
}

export default KeywordMatchingIgnoresComponent;

document.addEventListener("DOMContentLoaded", () => {
    const domContainer = document.querySelector(
        "#reactMainKeywordMatchingIgnores"
    );
    ReactDOM.render(
        React.createElement(KeywordMatchingIgnoresComponent),
        domContainer
    );
});
