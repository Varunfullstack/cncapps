import CKEditor from "../shared/CKEditor";
import Modal from "../shared/Modal/modal";
import React from 'react';

/**
 * options : show,options,value,title,okTitle
 * events: onChange
 */
class StandardTextModal extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            _showModal: this.props.show || false,
            templateOptions: this.props.options || [],
            templateValue: this.props.value,
            templateDefault: this.props.value,
            templateTitle: this.props.title,
            templateOptionId: -1,
            key: this.props.key,
            okTitle: this.props.okTitle || 'send',
        }
    }

    handleTemplateValueChange = (templateValue) => {
        this.setState({templateValue});
    }
    handleTemplateOk = () => {
        this.setState({_showModal: false})
        if (this.props.onChange)
            this.props.onChange(this.state.templateValue);
    }
    handleTemplateChanged = (event) => {

        const id = event.target.value;
        const {templateOptions} = this.state;
        let templateDefault;
        let templateOptionId = null;
        let templateValue = '';
        if (id >= 0) {
            const op = templateOptions.filter(s => s.id === id)[0];
            templateDefault = op.template;
            templateValue = op.template;
            templateOptionId = op.id;
            if (this.props.onTypeChange)
                this.props.onTypeChange(id);
        } else {
            templateDefault = '';
        }
        this.setState({templateDefault, templateOptionId, templateValue});
    }
    getTemplateModal = () => {
        const {templateOptions, _showModal, templateTitle, key, okTitle, templateDefault} = this.state;
        const {el} = this;
        return el(Modal, {
                width: 900,
                key,
                onClose: () => this.props.onCancel ? this.props.onCancel() : this.setState({_showModal: false}),
                title: templateTitle,
                show: _showModal,
                content: el('div', {key: 'conatiner', style: {height: 150}},
                    templateOptions.length > 0 ? el('select', {onChange: this.handleTemplateChanged}, el('option', {
                        key: 'empty',
                        value: -1
                    }, "-- Pick an option --"), templateOptions.map(s => el('option', {
                        key: s.id,
                        value: s.id
                    }, s.name))) : null,
                    _showModal ? el(CKEditor, {
                            key: 'salesRequestEditor',
                            id: 'salesRequest',
                            value: templateDefault,
                            onChange: this.handleTemplateValueChange,
                            inline: true,
                            height: 100
                        })
                        : null),
                footer: el('div', {key: "footer"},
                    el('button', {onClick: () => this.props.onCancel ? this.props.onCancel() : this.setState({_showModal: false})}, "Cancel"),
                    el('button', {onClick: this.handleTemplateOk}, okTitle))
            }
        )
    }

    static getDerivedStateFromProps(props, current_state) {

        if (current_state && current_state._showModal !== props.show) {

            current_state._showModal = props.show;
            current_state.templateValue = props.value;
            current_state.templateDefault = props.value;
            current_state.templateOptions = props.options;
            return current_state;
        }
        return current_state;
    }

    render() {
        return (this.getTemplateModal());
    }
}

export default StandardTextModal;