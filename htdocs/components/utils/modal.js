export class Modal extends React.Component {
  el = React.createElement;
  
  constructor(props)
  {
    super(props);    
  }
  handleClose=()=>{
      const {onClose}=this.props;
      if(onClose)
      onClose();
  }
  render() {
    const { el,handleClose } = this;
    const { show, width, title, content, footer } = this.props;
    let maxWidth = "70%";
    if (width) maxWidth = width;
    if (show) {
      return el("div", { key: "myModal", className: "modal" }, [
        el(
          "div",
          {
            key: "modalContent",
            className: "modal-content",
            style: { maxWidth },
            
          },
          [
            el("div", { key: "modalHeader", className: "modal-header" }, [
              el("span", { key: "spanClose", className: "close fa fa-times",onClick:handleClose,style:{color:"#FFFFFF"} }),
              el("label", { key: "header",className:"modal-title" }, title),
            ]),
            el("div", { key: "modalbody", className: "modal-body" }, [
              content ?  content: null,
            ]),
            footer
              ? el("div", { key: "modalFooter", className: "modal-footer" }, [
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
