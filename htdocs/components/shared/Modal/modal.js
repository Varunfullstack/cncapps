import React from 'react';
import './modal.css';
//show, width, title, content, footer, draggable, children
export class Modal extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            startDrag: false
        }
    }

    componentDidMount() {
        //this.getDrag();
    }

    handleClose = () => {
        const {onClose} = this.props;
        if (onClose)
            onClose();
    }
    getDrag = () => {
        const {show, draggable} = this.props;
        if (show && (draggable || draggable === undefined)) {
            let $this = this;
            setTimeout(() => {
                $(".modal-content").draggable({
                    cancel: ".undraggable",
                    start: function () {
                    },
                    stop: function () {
                        $this.setState({startDrag: false})

                    },
                });
            }, 500)
        }
    }
    handleMouseMove = () => {
        if (!this.state.startDrag)
            this.setState({startDrag: true}, () => this.getDrag());
    }
    hanldeMouseOut = () => {
        if (this.state.startDrag)
            this.setState({startDrag: false});
    }
    handleContainerMouseMove = () => {
        if (this.state.startDrag)
            this.setState({startDrag: false});
    }

    render() {
        const {el, handleClose} = this;
        let {show, width, title, content, footer, draggable, children} = this.props;
        if (children && !content) {
            content = children;
        }
        const {startDrag} = this.state;
        let maxWidth = "70%";
        const className = `modal ${this.props.className}`;

        if (width) maxWidth = width;
        if (show) {
            return (
                <div key="myModal" className={className} id="modal">
                    <div key="modalContent" className={!startDrag ? "modal-content undraggable" : " modal-content"}
                         style={{maxWidth}}>
                        <div key="modalHeader" className="modal-header" onMouseMove={this.handleMouseMove}>
                            <span
                                key="spanClose"
                                className="close fa fa-times"
                                onClick={handleClose}
                                style={{color: "#FFFFFF"}}>
                            </span>
                            <label key="header" className="modal-title">{title}</label>

                        </div>
                        <div
                            key="modalbody"
                            className="modal-body"
                            onMouseMove={this.handleContainerMouseMove}
                        >
                            {content ? content : null}

                        </div>
                        {
                            footer
                                ? <div key="modalFooter"
                                       className="modal-footer"
                                >{footer}
                                </div>
                                : null
                        }
                    </div>

                </div>
            )
        } else return null;
    }
}

export default Modal;
