export default class CKEditor extends React.Component {
    el=React.createElement;
  constructor(props) {
    super(props);
    this.elementName = "editor_" + this.props.id;
    this.componentDidMount = this.componentDidMount.bind(this);
  }

  render() {
    return this.el('textarea',{name:this.elementName,defaultValue:this.props.value});     
  }

  componentDidMount() {  
    CKEDITOR.replace(this.elementName,  {
        customConfig: '../../ckeditor_config.js'
    });
    CKEDITOR.instances[this.elementName].on(
      "change",
      function () {
        let data = CKEDITOR.instances[this.elementName].getData();
        this.props.onChange(data);
      }.bind(this)
    );
  }
}
