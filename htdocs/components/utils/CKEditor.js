export default class CKEditor extends React.Component {
  el = React.createElement;
  constructor(props) {
    super(props);
    this.elementName = "editor_" + this.props.id;
    this.componentDidMount = this.componentDidMount.bind(this);
    this.state = { value: this.props.value ,reinit:false};
  }
componentDidUpdate=(prevProps, prevState)=> {
  
  // if(prevState.prevValue!=prevState.value)
  // {
  // this.setState({prevValue:prevState.value});
  // setTimeout(()=>this. initEditor(),200);
  // }
}
  render() {
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
  initEditor = () => {    

      if(CKEDITOR.instances[this.elementName])
      {
        console.log('destroyed',this.elementName,CKEDITOR.instances);
        CKEDITOR.instances[this.elementName].destroy(true);   
      }
      CKEDITOR.config.width = 'auto';
      CKEDITOR.replace(this.elementName, {
        customConfig: "../../ckeditor_config_auto.js",
      });
     
      if (CKEDITOR.instances[this.elementName])
        CKEDITOR.instances[this.elementName].on(
          "change",
          function () {
            let data = CKEDITOR.instances[this.elementName].getData();
            this.props.onChange(data);
          }.bind(this)
        );    
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
        reinit:true
      };
    }
     
    return {  
      reinit:false
    };;
  }
}
