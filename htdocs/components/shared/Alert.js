import Modal from "../shared/Modal/modal";
import React from 'react';

class Alert extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
    }

    close = () => {
        this.props.onClose();
    }

    render() {
        const {el} = this;
        const {title, width, message, isHTML} = this.props;

        let content = el('label', {key: "label"}, message);

        if (isHTML) {
            content = (<label dangerouslySetInnerHTML={{__html: message}}/>)
        }

        return (el(
            Modal, {
                title: title || "Alert",
                show: this.props.show,
                width: width || 300,
                onClose: () => this.close(),
                footer: el('button', {key: "btnOk", onClick: () => this.close(),autoFocus:true}, "OK"),
                content
            }
        ));
    }
}

export default Alert;