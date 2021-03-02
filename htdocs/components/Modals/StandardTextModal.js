import Modal from "../shared/Modal/modal";
import React from 'react';
import CNCCKEditor from "../shared/CNCCKEditor";

/**
 * options : show,options,value,title,okTitle
 * events: onChange
 */
class StandardTextModal extends React.Component {
    el = React.createElement;
    static defaultProps = {
        show: false,
        options: [],
        value: "",
        title: "",
        okTitle: "send",
    }

    constructor(props) {
        super(props);
        this.state = {
            selectedOptionId: null,
            value: this.props.value,
        }
    }

    initialState() {
        return {selectedOptionId: null, value: this.props.value};
    }

    handleTemplateValueChange = (value) => {
        this.setState({value});
    }
    handleTemplateOk = () => {
        if (this.props.onChange)
            this.props.onChange(this.state.value);
        this.setState(this.initialState());
    }
    handleTemplateChanged = (event) => {

        const id = +event.target.value;
        const {options} = this.props;
        let selectedOptionId = null;
        let value = '';
        if (id) {
            const op = options.find(s => s.id == id);
            value = op.template;
            selectedOptionId = op.id;
            if (this.props.onTypeChange)
                this.props.onTypeChange(id);
        }
        this.setState({selectedOptionId, value});
    }

    onCancel() {
        this.props.onCancel();
        this.setState(this.initialState());
    }

    renderOptions() {
        const {options} = this.props;
        if (!options.length) {
            return "";
        }
        return (

            <select onChange={this.handleTemplateChanged}
                    autoFocus={true}
                    key="standardTextSelect"
                    style={{display: "block"}}
            >
                <option key="empty"
                        value={null}
                >-- Pick an option --
                </option>
                {options.map(s => (
                    <option key={s.id}
                            value={s.id}
                    >{s.name}</option>)
                )}
            </select>
        )
    }

    renderEditableField() {
        const {noEditor} = this.props;
        const {value} = this.state;
        if (noEditor) {
            return (
                <textarea
                    autoFocus={true}
                    value={value}
                    key="editableField"
                    onChange={($event) => {
                        this.handleTemplateValueChange($event.target.value)
                    }}
                    style={{height: "100px", width: "700px"}}
                />
            )
        }

        return (
            <React.Fragment key="editableField">
                <div id="top"
                     key="top"
                />
                <CNCCKEditor key={'salesRequest'}
                             name="salesRequest"
                             value={value}
                             onChange={(data) => this.handleTemplateValueChange(data)}
                             height="100"
                             type="inline"
                             className="CNCCKEditor"
                             sharedSpaces={true}
                             top="top"
                             bottom="bottom"
                />
                <div id="bottom"
                     key="bottom"
                />
            </React.Fragment>

        )
    }

    getTemplateModal = () => {
        const {title, okTitle, show} = this.props;
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

    render() {
        return (this.getTemplateModal());
    }
}

export default StandardTextModal;