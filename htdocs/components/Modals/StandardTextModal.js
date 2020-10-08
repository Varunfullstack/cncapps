import CKEditor from "../utils/CKEditor.js?v=1";
import Modal from "../utils/modal.js";
/**
 * options : show,options,value,title,okTitle
 * events: onChange
 */
class StandardTextModal  extends React.Component{
    el = React.createElement;
    constructor(props) {
        super(props);
        this.state = {
            _showModal:this.props.show||false,
            templateOptions:this.props.options||[],                        
            templateValue:this.props.value,       
            templateDefault:this.props.value,
            templateTitle:this.props.title,   
            templateOptionId:-1,
            key:this.props.key,
            okTitle:this.props.okTitle||'send',
         }
    }
    handleTemplateValueChange=(templateValue)=>{
        this.setState({templateValue});
        // if(this.props.onChange)
        //     this.props.onChange(templateValue);
    }
    handleTemplateOk=()=>{
        this.setState({_showModal:false})
        if(this.props.onChange)
            this.props.onChange(this.state.templateValue);
    }
    handleTemplateChanged=(event)=>{
        console.log(event.target.value);
        const id=event.target.value;
        const {templateOptions}=this.state;
        let templateDefault='';
        let templateOptionId=null;
        let templateValue='';
        if(id>=0)
        {
            const op=templateOptions.filter(s=>s.id==id)[0];            
            templateDefault=op.template;
            templateValue=op.template;
            templateOptionId=op.id;
        }
        else 
        {
            templateDefault='';
        }
        this.setState({templateDefault,templateOptionId,templateValue});
    }
    getTemplateModal=()=>{
        const {templateOptions,_showModal,templateTitle,key,okTitle,templateDefault }=this.state;
        const {el}=this;
        return el(Modal,{width:900,key,onClose:()=>this.setState({_showModal:false}),
            title:templateTitle,show:_showModal,
            content:el('div',{key:'conatiner'},
            templateOptions.length>0?el('select',{onChange:this.handleTemplateChanged},el('option',{key:'empty',value:-1},"-- Pick an option --"),templateOptions.map(s=>el('option',{key:s.id,value:s.id},s.name))):null,
            _showModal?el(CKEditor,{key:'salesRequestEditor',id:'salesRequest',value:templateDefault
                ,onChange:this.handleTemplateValueChange})
            :null),
            footer:el('div',{key:"footer"},
            el('button',{onClick:()=>this.setState({_showModal:false})},"Cancel"),
            el('button',{onClick:this.handleTemplateOk},okTitle))
            }
        )
    }
    render() { 
        return ( this.getTemplateModal() );
    }
}
 
export default StandardTextModal;