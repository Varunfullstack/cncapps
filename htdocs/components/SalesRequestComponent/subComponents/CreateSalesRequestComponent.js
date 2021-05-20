import MainComponent from "../../shared/MainComponent";
import React from "react";
import CustomerSearch from "../../shared/CustomerSearch";
import APIStandardText from "../../services/APIStandardText";
import CNCCKEditor from "../../shared/CNCCKEditor";
import DragAndDropUploaderComponent from "../../shared/DragAndDropUploaderComponent/DragAndDropUploaderComponent";
import APISalesRequest from "../services/APISalesRequest";
import ToolTip from "../../shared/ToolTip";

const initialData = {
    customerId: '',
    message: '',
    files: [],
    type: 68,
    customerName: ''
}

export class CreateSalesRequestComponent extends MainComponent {
    api = new APISalesRequest();
    apiStandardText = new APIStandardText();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            options: [],
            data: {...initialData}
        };
    }

    componentDidMount() {
        this.apiStandardText
            .getOptionsByType("Sales Request")
            .then((options) => this.setState({options}));
    }

    handleFileSelected = (files, type) => {
        this.setValue("files", [...files])
    }
    handleTemplateSelect = (templateId) => {

        const template = this.state.options.find(o => o.id == templateId);
        this.setValue("message", template.template);
        this.setValue("type", templateId);
    }
    handleSave = () => {
        const {data} = this.state;
        if (data.customerId == '') {
            this.alert("Please select customer");
            return;
        }
        if (data.message == '') {
            this.alert("Please enter the message");
            return;
        }

        this.api.CreateSalesRequest(data.files, {
            type: data.type,
            message: data.message,
            customerId: data.customerId
        }).then(res => {
            if (res.state) {
                this.setState({data: {...initialData}});
            }

        })
    }
    handleCancel = () => {

    }
    handleDeleteFile = (file) => {
        const {data} = this.state;
        data.files = data.files.filter(f => f.name != file.name)
        this.setState({data});
    }
    handleCustomerSelect = (customer) => {
        const {data} = this.state;
        data.customerId = customer.id;
        data.customerName = customer.name;
        this.setState({data});
    }

    render() {
        const {options, data} = this.state;
        return (
            <div>
                {this.getAlert()}
                <div className="form-group">
                    <label>Customer</label>
                    <CustomerSearch customerName={data.customerName}
                                    onChange={(c) => this.handleCustomerSelect(c)}/>
                </div>
                <div className="form-group">
                    <label>Template</label>
                    <select value={data.type} style={{width: 305}}
                            onChange={(event) => this.handleTemplateSelect(event.target.value)}>
                        {options.map((o) => (
                            <option key={o.id} value={o.id}>
                                {o.name}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="form-group">
                    <label>Message</label>
                    <CNCCKEditor
                        value={data.message}
                        type="inline"
                        style={{width: 800, height: 100, border: '1px gray solid'}}
                        onChange={(c) => this.setValue("message", c)}
                    />
                </div>
                <div className="form-group">
                    <label>Files</label>
                    <DragAndDropUploaderComponent
                        onFilesChanged={(files, type) => this.handleFileSelected(files, type)}/>
                </div>
                <div className="flex-row">
                    {data.files.map(f => <div className="flex-row" key={f.name}
                                              style={{background: "#d1cece", margin: 5, padding: 5, borderRadius: 10}}>
                        {f.name}
                        <ToolTip title="Delete files" width={30}>
                            <i className="fal fa-trash-alt color-red pointer ml-3"
                               onClick={() => this.handleDeleteFile(f)}/>
                        </ToolTip>
                    </div>)}
                </div>
                <div className="flex-row">
                    <button onClick={this.handleSave}>Submit</button>
                    <button onClick={this.handleCancel} className="btn btn-secondary">Cancel</button>
                </div>
            </div>
        );
    }
}
