import Modal from "../shared/Modal/modal";
import React from 'react';
import CNCCKEditor from "../shared/CNCCKEditor";
import * as PropTypes from "prop-types";

class AdditionalTimeRequestModal extends React.Component {
    el = React.createElement;
    static defaultProps = {
        show: false,
    }

    constructor(props) {
        super(props);
        this.state = this.initialState();
    }

    initialState() {
        return {
            reason: '',
            timeRequested: 0
        };
    }

    handleTemplateValueChange = (reason) => {
        this.setState({reason});
    }
    handleTemplateOk = () => {
        if (this.props.onChange)
            this.props.onChange(this.state);
        this.setState(this.initialState());
    }

    onCancel() {
        if (this.props.onCancel) {
            this.props.onCancel();
        }
        this.setState(this.initialState());
    }

    handleTimeRequestedChange = ($event) => {
        this.setState({timeRequested: $event.target.value})
    }

    renderEditableField() {
        const {reason} = this.state;
        return (
            <div key="editorField"
                 className="modal_editor"
            >
                <div id="additionalTimeRequestModalTop"
                     key="topElement"
                />
                <CNCCKEditor key="AddInternalNote"
                             name="AddInternalNote"
                             value={reason}
                             onChange={this.handleTemplateValueChange}
                             className="CNCCKEditor"
                             type="inline"
                             height="500"
                             sharedSpaces={true}
                             top="additionalTimeRequestModalTop"
                             bottom="additionalTimeRequestModalBottom"
                />
                <div id="additionalTimeRequestModalBottom"
                     key="bottomElement"
                />
            </div>
        )
    }

    getTemplateModal = () => {
        const {show} = this.props;
        const {reason, timeRequested} = this.state;
        return (
            <Modal
                width="900"
                onClose={() => this.onCancel()}
                title="Additional Time Request"
                show={show}
                className="standardTextModal"
                content={(
                    <React.Fragment key="internalModal">
                        <div key="hoursContainer"
                             style={{marginBottom: "1rem"}}
                        >
                            <label key="someLabel">
                                Hours to quote for:
                            </label>
                            <select onChange={this.handleTimeRequestedChange}
                                    key="someSelect"
                            >
                                <option>
                                    -- Select an option --
                                </option>
                                <option value={1}
                                        key={1}
                                >1
                                </option>
                                <option value={2}
                                        key={2}
                                >2
                                </option>
                                <option value={3}
                                        key={3}
                                >3
                                </option>
                                <option value={4}
                                        key={4}
                                >4
                                </option>
                            </select>
                        </div>
                        <div style={{height: 150}}
                             key="editableFieldContainer"
                        >
                            {this.renderEditableField()}
                        </div>
                    </React.Fragment>
                )}
                footer={
                    <div key="footer">
                        <button key="saveButton"
                                disabled={!reason || !timeRequested}
                                onClick={this.handleTemplateOk}
                        >
                            Save
                        </button>
                        <button key="cancelButton"
                                onClick={() => this.onCancel()}
                        >
                            Cancel
                        </button>
                    </div>
                }
            />
        )
    }

    render() {
        return this.getTemplateModal();
    }
}

AdditionalTimeRequestModal.propTypes = {
    show: PropTypes.bool,
    onChange: PropTypes.func,
    onCancel: PropTypes.func
}

export default AdditionalTimeRequestModal;