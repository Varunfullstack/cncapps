import Modal from "./Modal/modal";
import React from 'react';
import CNCCKEditor from "./CNCCKEditor";

class Prompt extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            show: false,
            title: "",
            width: 300,
            reason: this.props.defaultValue
        }
    }

    close = (cancel = false) => {
        this.setState({show: false});
        if (this.props.onClose) {
            if (cancel)
                this.props.onClose(false);
            else
                this.props.onClose(this.state.reason || this.props.defaultValue);
        }
    }

    static getDerivedStateFromProps(props, current_state) {
        return {...current_state, ...props};
    }

    getContent(defaultValue) {
        const {isEditor} = this.props;

        if (isEditor) {
            return <CNCCKEditor onchange={(value) => this.setState({reason: value})} value={defaultValue}/>
        }

        return <textarea key="input"
                         onChange={(event) => this.setState({reason: event.target.value})}
                         style={{width: "97%", minHeight: 30}}
                         defaultValue={defaultValue}
        />
    }

    render() {
        const {el} = this;
        const {title, width} = this.state;
        const {defaultValue} = this.props;
        return (el(
            Modal, {
                title: title || "Alert",
                show: this.state.show,
                width: width || 500,
                onClose: () => this.close(),
                footer: [
                    el('button', {key: "btnOk", onClick: () => this.close()}, "OK"),
                    el('button', {key: "btncancel", onClick: () => this.close(true)}, "Cancel"),
                ],
                content: this.getContent(defaultValue)
            }
        ));
    }
}

export default Prompt;