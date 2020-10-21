export default class CKEditor extends React.Component {
  el = React.createElement;
  constructor(props) {
    super(props);
    this.elementName = "editor_" + this.props.id;
    this.componentDidMount = this.componentDidMount.bind(this);
    this.state = { value: this.props.value ,reinit:false,height:this.props.height};
  }
componentDidUpdate=(prevProps, prevState)=> {
  
  // if(prevState.prevValue!=prevState.value)
  // {
  // this.setState({prevValue:prevState.value});
  // setTimeout(()=>this. initEditor(),200);
  // }
}
  render() {
    //console.log(this.state.height);
     if(this.state.reinit)
    {
      setTimeout(()=>this. initEditor(),200);
    }
    return this.el("textarea", {
      id: this.elementName,
      name: this.elementName,    
      defaultValue: this.props.value,  
     });
  }
  initEditor = ( ) => {    

      if(CKEDITOR.instances[this.elementName])
      {
        //console.log('destroyed',this.elementName,CKEDITOR.instances);
        CKEDITOR.instances[this.elementName].destroy(true);   
      }
      //console.log(this.props.height,this.state.height);      
      //inline replace
      if(this.props.inline)
      CKEDITOR.inline(this.elementName, {
        customConfig: "../../ckeditor_config_auto.js?v=10",
      });
      else 
      CKEDITOR.replace(this.elementName, {
        customConfig: "../../ckeditor_config_auto.js?v=10",
      });
     
      if (CKEDITOR.instances[this.elementName])
      {
        CKEDITOR.instances[this.elementName].config.width  = this.props.width||'auto';
        CKEDITOR.instances[this.elementName].config.height = this.props.height||220;
        //CKEDITOR.instances[this.elementName].config.allowedContent = '*{*}';
        //CKEDITOR.instances[this.elementName].config.extraAllowedContent = '*{*}';

        CKEDITOR.instances[this.elementName].on(
          "change",
          function () {
            let data = CKEDITOR.instances[this.elementName].getData();
            this.props.onChange(data);
          }.bind(this)
        );    
      }
  };
  componentDidMount() {    
    this.initEditor()
    CKEditor.getDerivedStateFromProps = CKEditor.getDerivedStateFromProps.bind(
      this
    );        
  }

  static getDerivedStateFromProps(props, current_state) {
   

    if (current_state && current_state.value !== props.value) {
      //console.log("====================>",current_state.value , props.value);
      this.initEditor();      
      return {
        value: props.value        ,
        reinit:true,
        height:props.height
      };
    }
     
    return {  
      reinit:false
    };;
  }
}
