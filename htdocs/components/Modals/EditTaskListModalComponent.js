import React from 'react';
import * as PropTypes from "prop-types";
import StandardTextModal from "./StandardTextModal";
import Modal from "../shared/Modal/modal";

class EditTaskListModalComponent extends StandardTextModal {

    handleTemplateChanged = (event) => {
        const id = +event.target.value;
        const {options} = this.props;
        let selectedOptionId = null;
        if (id) {
            const op = options.find(s => s.id == id);
            selectedOptionId = op.id;
        }
        this.setState({selectedOptionId});
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (JSON.stringify(prevProps) !== JSON.stringify(this.props)) {
            this.setState({value: this.props.value});
        }
    }

    appendTaskList = () => {
        const {options} = this.props;
        const {selectedOptionId} = this.state;
        const foundStandardText = options.find(x => x.id === selectedOptionId);
        const cleanValue = this.state.value ?? '';
        this.setState({value: `${cleanValue}${foundStandardText.template}`});
    }

    getTemplateModal = () => {
        const {title, okTitle, show} = this.props;
        const {selectedOptionId} = this.state;
        const {el} = this;
        return el(Modal, {
                width: 900,
                onClose: () => this.onCancel(),
                title,
                show,
                className: "standardTextModal",
                content: (
                    <div style={{height: 150}}
                         key="container"
                    >
                        {this.renderOptions()}
                        <button disabled={!selectedOptionId}
                                onClick={this.appendTaskList}
                        >Add
                        </button>
                        {this.renderEditableField()}
                    </div>
                ),
                footer: el('div', {key: "footer"},
                    el('button', {onClick: this.handleTemplateOk}, okTitle),
                    el('button', {onClick: () => this.onCancel()}, "Cancel"),
                )
            }
        )
    }
}

EditTaskListModalComponent.propTypes = {
    show: PropTypes.bool,
    value: PropTypes.string,
}

export default EditTaskListModalComponent;