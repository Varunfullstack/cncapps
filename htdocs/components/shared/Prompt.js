import Modal from "./Modal/modal";
import React from 'react';
import EditorFieldComponent from "./EditorField/EditorFieldComponent";

class Prompt extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            show: false,
            title: "",
            width: 300,
            height: this.props.height,
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
            return <EditorFieldComponent name="prompt"
                                         onChange={(value) => this.setState({reason: value})}
                                         value={defaultValue}
                                         hasToolbar={true}
                                         autoFocus={true}
                                         style={{width: this.props.width - 40, height: this.props.height}}
                                         key="content"
            />
        }

        return <textarea key="input"
                         className="spellcheck"
                         onChange={(event) => this.setState({reason: event.target.value})}
                         style={{width: "97%", minHeight: 30}}
                         defaultValue={defaultValue}
        />
    }

    render() {
        const {title, width} = this.state;
        const {defaultValue} = this.props;
        return (
            <Modal
                title={title || "Alert"}
                show={this.state.show}
                width={width || 500}
                onClose={() => this.close()}
                footer={[
                    <button
                        key={"btnOk"}
                        onClick={() => this.close()}
                        autoFocus={true}
                    >
                        OK
                    </button>,

                    <button
                        key={"btncancel"}
                        onClick={() => this.close(true)}
                    >
                        Cancel
                    </button>
                ]
                }
                content={this.getContent(defaultValue)}
            />
        );
    }
}

export default Prompt;