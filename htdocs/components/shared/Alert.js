import Modal from "../shared/Modal/modal";
import React from 'react';

class Alert extends React.Component {
    el = React.createElement;
    timeInterval;
    constructor(props) {
        super(props);
        this.state={
            autoCloseTimer:3, //in seconds,
            autoClose:false,
            show:false
        }
    }
    componentDidUpdate(prevProps, prevState) {
        if(!this.props.show)
            this.clearTimer();
        if(!prevProps.show&&this.props.show)
        {
            this.setState({show:this.props.show,autoClose:this.props.autoClose,autoCloseTimer:3 },
                ()=>this.startTimer());
        }
    }   
    componentDidMount(){
    }
    startTimer=()=>{        
        if (this.state.autoClose) {     
            this.timeInterval = setInterval(() => {
              let { autoCloseTimer } = this.state;             
              if (autoCloseTimer > 1) {
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

        return (el('div',{style:{zIndex:101, position: "absolute"}},
        el(
            Modal, {
                title: title || "Alert",
                show: this.props.show,
                width: width || 300,
                onClose: () => this.close(),
                footer: el('button', {key: "btnOk", onClick: () => this.close(),autoFocus:true}, `OK ${this.props.autoClose ? this.state.autoCloseTimer : ""}`),
                content
            }
        )));
    }
}

export default Alert;