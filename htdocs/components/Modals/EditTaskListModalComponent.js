import React from 'react';
import * as PropTypes from "prop-types";
import StandardTextModal from "./StandardTextModal";
import Modal from "../shared/Modal/modal";

class EditTaskListModalComponent extends StandardTextModal {


    // constructor(props) {
    //     super(props);
    //     this.state = {
    //         ...this.state,
    //     }
    // }

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

    // handleTemplateValueChange = (value) => {
    //     console.log('handleTemplateValueChange:', value);
    //     this.setState({value});
    // }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (JSON.stringify(prevProps) !== JSON.stringify(this.props)) {
            this.setState({value: this.props.value});
        }
    }

    appendTaskList = () => {
        const {options} = this.props;
        const {selectedOptionId} = this.state;
        const foundStandardText = options.find(x => x.id === selectedOptionId);
        this.setState({value: `${this.state.value}${foundStandardText.template}`});
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

    // renderEditableField() {
    //     const {noEditor} = this.props;
    //     const {value} = this.state;
    //     console.log('renderFieldValue', value);
    //     if (noEditor) {
    //         return (
    //             <textarea
    //                 autoFocus={true}
    //                 value={value}
    //                 key="editableField"
    //                 onChange={($event) => {
    //                     this.handleTemplateValueChange($event.target.value)
    //                 }}
    //                 style={{height: "100px", width: "700px"}}
    //             />
    //         )
    //     }
    //
    //     return (
    //         <React.Fragment key="editableField">
    //             <div id="top"
    //                  key="top"
    //             />
    //             <CNCCKEditor key={'salesRequest'}
    //                          name="salesRequest"
    //                          value={value}
    //                          onChange={(data) => this.handleTemplateValueChange(data)}
    //                          height="100"
    //                          type="inline"
    //                          className="CNCCKEditor"
    //                          sharedSpaces={true}
    //                          top="top"
    //                          bottom="bottom"
    //             />
    //             <div id="bottom"
    //                  key="bottom"
    //             />
    //         </React.Fragment>
    //
    //     )
    // }
}

EditTaskListModalComponent.propTypes = {
    show: PropTypes.bool,
    value: PropTypes.string,
}

export default EditTaskListModalComponent;