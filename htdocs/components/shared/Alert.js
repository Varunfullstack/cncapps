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
        const {title, width, message} = this.props;
        return (el(
            Modal, {
                title: title || "Alert",
                show: this.props.show,
                width: width || 300,
                onClose: () => this.close(),
                footer: el('button', {key: "btnOk", onClick: () => this.close()}, "OK"),
                content: el('label', {key: "label"}, message)
            }
        ));
    }
}

export default Alert;