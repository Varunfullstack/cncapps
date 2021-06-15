import * as React from "react";
import Modal from "../../../shared/Modal/modal";
import CNCCKEditor from "../../../shared/CNCCKEditor";
import * as PropTypes from "prop-types";
import APIActivity from "../../../services/APIActivity";
import MainComponent from "../../../shared/MainComponent";

export const TEMPlATE_TYPES = {
    changeRequest: "changeRequest",
    salesRequest: "salesRequest",
    partsUsed: "partsUsed",
}


export class TemplateModal extends MainComponent {
    api = new APIActivity();

    constructor(props, context) {
        super(props, context);
        this.state = {
            ...this.state,
            templateValue: '',
            templateOptionId: '',
            templateOptions: [],
            templateTitle: '',
            templateDefault: '',
            isOptionRequired: false
        }

    }

    async componentDidMount() {
        let templateOptions = [];
        let templateTitle = "";
        let isOptionRequired = false;
        let templateOptionId = '';
        let templateValue = '';
        switch (this.props.templateType) {
            case "salesRequest":
                templateOptions = await this.api.getSalesRequestOptions();
                isOptionRequired = true;
                templateTitle = "Sales Request";
                templateOptionId = "68";
                const template = templateOptions.find(x => x.id === 68);
                templateValue = template.template;
                break;
            case "changeRequest":
                templateOptions = await this.api.getChangeRequestOptions();
                templateTitle = "Change Request";
                break;
            case "partsUsed":
                templateTitle = "Parts Used";
                break;
        }
        this.setState({
            ...this.state,
            templateOptions,
            templateTitle,
            isOptionRequired,
            templateOptionId,
            templateValue
        })
    }

    handleTemplateChanged = (event) => {
        const value = event.target.value;
        const {templateOptions} = this.state;
        let templateDefault = '';
        let templateOptionId = value;
        let templateValue = '';
        if (value) {
            const id = parseInt(value);
            const op = templateOptions.filter(s => s.id == id)[0];
            templateDefault = op.template;
            templateValue = op.template;
        }
        this.setState({...this.state, templateDefault, templateOptionId, templateValue});
    }

    handleTemplateValueChange = (data) => {
        this.setState({templateValue: data});
    };

    handleTemplateSend = async () => {
        const {
            activityId,
            serviceRequestId,
            customerId,
            templateType
        } = this.props;

        const {
            templateValue,
            templateOptionId,
        } = this.state;
        if (templateValue == "") {
            await this.alert('Please enter details');
            return;
        }
        const payload = new FormData();
        payload.append("message", templateValue);
        payload.append("type", templateOptionId);
        try {
            switch (templateType) {
                case "changeRequest":
                    await this.api.sendChangeRequest(serviceRequestId, payload);
                    await this.alert('Change Request Sent');
                    break;
                case "partsUsed":
                    const object = {
                        message: templateValue,
                        callActivityID: activityId,
                    };
                    await this.api.sendPartsUsed(object);
                    await this.alert('Parts Used Sent');
                    break;
                case "salesRequest":
                    await this.api.sendSalesRequest(
                        customerId,
                        serviceRequestId,
                        payload
                    );
                    await this.alert('Sales Request Sent');
                    break;
            }
        } catch (error) {
            await this.alert(JSON.stringify(error));
        }
        this.props.onClose(true);
    }


    render() {
        const {onClose} = this.props;
        const {templateOptions, templateTitle, templateValue, templateOptionId, isOptionRequired} = this.state;
        return (
            <React.Fragment>
                {this.getAlert()}
                <Modal
                    width="900"
                    onClose={onClose}
                    title={templateTitle}
                    show={true}
                    footer={
                        (
                            <div key="footer">
                                <button disabled={(isOptionRequired && !templateOptionId) || !templateValue}
                                        onClick={this.handleTemplateSend}>Send
                                </button>
                                <button onClick={onClose}>Cancel</button>
                            </div>
                        )
                    }

                >
                    <div key="container">
                        {
                            templateOptions.length > 0 ?
                                <select onChange={this.handleTemplateChanged}
                                        autoFocus={true}
                                        value={templateOptionId}
                                >
                                    <option key="empty" value=''>-- Pick an option --</option>
                                    {templateOptions.map(s => <option key={s.id}
                                                                      value={s.id}>{s.name}</option>)}
                                </select>
                                : null
                        }
                        <div className="modal_editor">
                            <div id="top2"/>
                            <CNCCKEditor
                                key="salesRequestEditor"
                                name="salesRequest"
                                value={templateValue}
                                type="inline"
                                onChange={this.handleTemplateValueChange}
                                sharedSpaces={true}
                                top="top2"
                                bottom="bottom2"
                            />
                            <div id="bottom2"/>
                        </div>
                    </div>
                </Modal>
            </React.Fragment>
        );
    }
}

TemplateModal.propTypes = {
    templateType: PropTypes.string,
    onClose: PropTypes.func,
    activityId: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    serviceRequestId: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    customerId: PropTypes.oneOfType([PropTypes.number, PropTypes.string])
};