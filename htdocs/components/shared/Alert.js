import Modal from "../shared/Modal/modal";
import React from 'react';

class Alert extends React.Component {
    el = React.createElement;
    timeInterval;
    constructor(props) {
        super(props);
        this.state={
            autoCloseTimer:3 //in seconds
        }
    }
    componentDidUpdate(prevProps, prevState) {
        if(!this.props.show)
        this.clearTimer();
    }
    componentDidMount(){
        if (this.props.autoClose) {
          this.timeInterval = setInterval(() => {
            let { autoCloseTimer } = this.state;
            if (autoCloseTimer > 0) {
              autoCloseTimer--;
              this.setState({ autoCloseTimer });
            } else if (this.props.onAutoClose) 
            {                
                this.props.onAutoClose();
            }
          }, 1000);
        }
    }
    componentWillUnmount() {
     this.clearTimer();
    }
    clearTimer=()=>{
        if(this.timeInterval)   
        clearInterval(this.timeInterval);
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
                footer: el('button', {key: "btnOk", onClick: () => this.close(),autoFocus:true}, "OK "+this.state.autoCloseTimer),
                content
            }
        ));
    }
}

export default Alert;