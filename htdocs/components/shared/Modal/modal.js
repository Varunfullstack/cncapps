import React from 'react';
import './modal.css';

export class Modal extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state={
            startDrag:false
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
    getDrag=()=>{
        const {show,draggable} = this.props;        
        if( show&&(draggable||draggable===undefined))
        {            
            let $this=this;
            setTimeout(()=>{
                $(".modal-content").draggable({                    
                    cancel: ".undraggable",
                    start:function(){
                                 },
                    stop:function(){
                        $this.setState({startDrag:false})
    
                    },  
                }) ;
            },500)
        }
    } 
    handleMouseMove=()=>{
        if(!this.state.startDrag)
         this.setState({startDrag:true},()=>this.getDrag());
    }
    hanldeMouseOut=()=>{
        if(this.state.startDrag)
        this.setState({startDrag:false});
    }
    handleContainerMouseMove=()=>{
        if(this.state.startDrag)
        this.setState({startDrag:false});
    }
    render() {
        const {el, handleClose} = this;
        const {show, width, title, content, footer,draggable} = this.props;
        const {startDrag}=this.state;        
        let maxWidth = "70%";
        const className = `modal ${this.props.className}`;        
      
        if (width) maxWidth = width;
        if (show) {
            return el("div", {key: "myModal", className,id:'modal'}, [
                el(
                    "div",
                    {
                        
                        key: "modalContent",
                        className: !startDrag?"modal-content undraggable":" modal-content",
                        style: {maxWidth},

                    },
                    [
                        el("div", {key: "modalHeader", className: "modal-header",onMouseMove:this.handleMouseMove}, [
                        el("span", { key: "spanClose", className: "close fa fa-times",onClick:handleClose,style:{color:"#FFFFFF"} }),
                            el("label", {key: "header", className: "modal-title"}, title),
                        ]),
                        el("div", {key: "modalbody", className: "modal-body",onMouseMove:this.handleContainerMouseMove}, [
                            content ? content : null,
                        ]),
                        footer
                            ? el("div", {key: "modalFooter", className: "modal-footer"}, [
                                footer,
                            ])
                            : null,
                    ]
                ),
            ]);
        } else return null;
    }
}

export default Modal;
