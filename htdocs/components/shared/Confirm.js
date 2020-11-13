import Modal from "../shared/Modal/modal";

import React from 'react';

class Confirm extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            show: false,
            title: "",
            width: 300,
            message: ""
        }
    }

    close = (value) => {
        this.setState({show: false})
        if (this.props.onClose)
            this.props.onClose(value);
    }

    static getDerivedStateFromProps(props, current_state) {
        return {...current_state, ...props};
    }

    render() {
        const {el} = this;
        const {title, width, message} = this.state;
        return (
            el(
                Modal, {
                    title: title || "Alert",
                    show: this.state.show,
                    width: width || 300,
                    onClose: () => this.close(),
                    footer: [
                        el('button', {key: "btnOk", onClick: () => this.close(true)}, "Yes"),
                        el('button', {key: "btncancel", onClick: () => this.close(false)}, "No"),
                    ],
                    content: el('label', {key: "label"}, message)
                }
            ));
    }
}

export default Confirm;