import Modal from "./modal.js";

class Prompt extends React.Component {
    el=React.createElement;
    constructor(props) {
        super(props);
        this.state = { show:false,
            title:"",
            width:300,           
            reason:''
         }
    }
    close=()=>{
        this.setState({show:false});        
        if(this.props.onClose)
        this.props.onClose(this.state.reason);
    }
    static getDerivedStateFromProps(props, current_state) {         
        return {...current_state,...props};
      }
    render() { 
        const {el}=this;
        const {title,width,reason}=this.state;
        //console.log(width);
        return ( el(
            Modal,{
                title:title||"Alert",
                show:this.state.show,
                width:width||500,
                onClose:()=>this.close(),
                footer:[
                    el('button',{key:"btncancel",onClick:()=>this.close()},"Cancel"),
                    el('button',{key:"btnOk",onClick:()=>this.close()},"Ok"),
                ],
                content:el('textarea',{
                    key:"input",
                    onChange:(event)=>this.setState({reason:event.target.value}),
                    style:{width:"97%",minHeight:30}
                })
        }
        ) );
    }
}
 
export default Prompt;