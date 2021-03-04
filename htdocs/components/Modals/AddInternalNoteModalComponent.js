import Modal from "../shared/Modal/modal";
import React from 'react';
import CNCCKEditor from "../shared/CNCCKEditor";
import * as PropTypes from "prop-types";

class AddInternalNoteModalComponent extends React.Component {
    el = React.createElement;
    static defaultProps = {
        show: false,
        value: "",
    }

    constructor(props) {
        super(props);
        this.state = {
            value: this.props.value,
        }
    }

    initialState() {
        return {value: this.props.value};
    }

    handleTemplateValueChange = (value) => {
        this.setState({value});
    }
    handleTemplateOk = () => {
        if (this.props.onChange)
            this.props.onChange(this.state.value);
        this.setState(this.initialState());
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (JSON.stringify(prevProps) !== JSON.stringify(this.props)) {
            this.setState({value: this.props.value});
        }
    }

    onCancel() {
        if (this.props.onCancel) {
            this.props.onCancel();
        }
        this.setState(this.initialState());
    }

    renderEditableField() {
        const {value} = this.state;
        return (
            <div key="editorField"
                 className="modal_editor"
            >
                <div id="internalNoteTop"
                     key="topElement"
                />
                <CNCCKEditor key="AddInternalNote"
                             name="AddInternalNote"
                             value={value}
                             onChange={(data) => this.handleTemplateValueChange(data)}
                             className="CNCCKEditor"
                             type="inline"
                             height="500"
                             sharedSpaces={true}
                             top="internalNoteTop"
                             bottom="internalNoteBottom"
                />
                <div id="internalNoteBottom"
                     key="bottomElement"
                />
            </div>

        )
    }

    getTemplateModal = () => {
        const {show} = this.props;
        return (
            <Modal
                width="900"
                onClose={() => this.onCancel()}
                title="Internal Note"
                show={show}
                className="standardTextModal"
                content={(
                    <div style={{height: 150}}
                         key="editableFieldContainer"
                    >
                        {this.renderEditableField()}
                    </div>
                )}
                footer={
                    <div key="footer">
                        <button key="saveButton"
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
        return (this.getTemplateModal());
    }
}

AddInternalNoteModalComponent.propTypes = {
    show: PropTypes.bool,
    value: PropTypes.string,
}

export default AddInternalNoteModalComponent;