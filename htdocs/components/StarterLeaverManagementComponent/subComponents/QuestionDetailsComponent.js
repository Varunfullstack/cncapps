import MainComponent from "../../shared/MainComponent.js";
import React from "react";

import CustomerSearch from "../../shared/CustomerSearch.js";
import Toggle from "../../shared/Toggle.js";
import Modal from "../../shared/Modal/modal.js";
import APIStarterLeaverManagement from "../services/APIStarterLeaverManagement.js";
import {sort} from "../../utils/utils.js";

export default class QuestionDetailsComponent extends MainComponent {
    api = new APIStarterLeaverManagement();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            data: {
                ...this.props.data
                // customerID: "",
                // type: "y/n",
                // formType: "starter",
                // required: "",
                // multi: "",
                // options: [],
                // name: "",
                // label: "",
            },
            optionValue: ""
        };
    }

    componentDidMount() {
    }

    static getDerivedStateFromProps(props, state) {
        state.data = props.data;
        return state;
    }

    handleCancel = () => {
        if (this.props.onClose) this.props.onClose();
    };
    handleDeleteOption = (key) => {
        const {data} = this.state;
        data.options = data.options.filter(o => o != key);
        this.setState({data});
    };


    hanldeSave = () => {
        const {data} = this.state;
        if (!this.isFormValid("form")) {
            this.alert("Please add all required inputs");
            return;
        }
        data.required = data.required ? 1 : 0;
        data.multi = data.multi ? 1 : 0;

        if (data.options)
    {
            data.options = JSON.stringify(data.options);
        console.log('options error',data)
    }if (data.questionID != null)
        {
            this.api.updateQuestion(data).then(res => {
                if (this.props.onClose) this.props.onClose(true);

            }, err => {
                this.alert("Error in save data")
            })
        } else {
            this.api.addQuestion(data).then(res => {
                if (this.props.onClose) this.props.onClose(true);

            }, err => {
                this.alert("Error in save data")
            })
        }
    };
    handleAddOptions = () => {
        const {optionValue, data} = this.state;
        if (data.options == null)
            data.options = [];
        if (this.state.optionValue != "") {
            const indx = data.options.indexOf(optionValue);
            if (indx >= 0)
                this.alert("Option exist")
            else
                data.options.push(optionValue);
            sort(data.options);
            this.setState({data, optionValue: ""});
        } else
            this.alert("Please enter the option text")

    }

    getContent = () => {
        const {data} = this.state;
        if (!this.props.show) return null;

        return (
            <Modal
                show={this.props.show}
                title={data.questionID ? "Edit Question" : "Add New Question"}
                width={600}
                footer={
                    <div key="footer" onClose={this.handleCancel}>
                        <button onClick={this.hanldeSave}>Save</button>
                        <button onClick={this.handleCancel}>Cancel</button>
                    </div>
                }
                onClose={() => this.props.onClose(true)}

            >
                <div style={{display: "flex", flexDirection: "row"}} id="form">
                    <table className="table">

                        <tbody>
                        <tr>
                            <td className="question-label">Starter/Leaver</td>
                            <td>
                                <select
                                    className="form-control"
                                    value={data.formType}
                                    onChange={(event) =>
                                        this.setValue("formType", event.target.value)
                                    }
                                >
                                    <option value="starter">Starter</option>
                                    <option value="leaver">Leaver</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td className="question-label">Name(no space allowed)</td>
                            <td>
                                <input
                                    required
                                    className="form-control"
                                    value={data.name}
                                    onChange={(event) =>
                                        this.setValue("name", event.target.value.replace(" ", ""))
                                    }
                                ></input>
                            </td>
                        </tr>
                        <tr>
                            <td className="question-label">Question Label</td>
                            <td>
                                <input
                                    required
                                    className="form-control"
                                    value={data.label}
                                    onChange={(event) =>
                                        this.setValue("label", event.target.value)
                                    }
                                ></input>
                            </td>
                        </tr>

                        <tr>
                            <td className="question-label">Required?</td>
                            <td>
                                <Toggle
                                    checked={data.required}
                                    onChange={() => this.setValue("required", !data.required)}
                                ></Toggle>
                            </td>
                        </tr>

                        <tr>
                            <td className="question-label">Question Type</td>
                            <td>
                                <select
                                    className="form-control"
                                    value={data.type}
                                    onChange={(event) =>
                                        this.setValue("type", event.target.value)
                                    }
                                >
                                    <option value="y/n">Yes/No</option>
                                    <option value="multi">Multiple Choice</option>
                                    <option value="free">Free Type</option>
                                </select>
                            </td>
                        </tr>
                        <tr style={{display: data.type == "multi" ? "" : "none"}}>
                            <td className="question-label">Multiple Answers?</td>
                            <td>
                                <Toggle
                                    checked={data.multi}
                                    onChange={() => this.setValue("multi", !data.multi)}
                                ></Toggle>
                            </td>
                        </tr>
                        <tr style={{display: data.type == "multi" ? "" : "none"}}>
                            <td className="question-label" style={{verticalAlign: "baseline"}}>Question Options</td>
                            <td>
                                <div className="flex-row">
                                    <input style={{width: 330, marginRight: 5}} className="form-control"
                                           value={this.state.optionValue}
                                           onChange={(event) => this.setState({optionValue: event.target.value})}></input>
                                    <i className="fal fa-2x fa-plus pointer " style={{color: "white"}}
                                       onClick={this.handleAddOptions}></i>

                                </div>
                                <div style={{overflowY: "auto", maxHeight: 200}}>

                                    <table className="table table-striped">

                                        <tbody>
                                        {(data.options || []).map((key) => (
                                            <tr key={key}>
                                                <td>{key}</td>
                                                <td style={{width: 53}}>
                                                    {this.getDeleteElement(
                                                        key,
                                                        this.handleDeleteOption
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                        </tbody>
                                    </table>
                                </div>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </Modal>
        );
    };

    render() {
        return (
            <div>
                {this.getAlert()}
                {this.getContent()}
            </div>
        );
    }
}
