/**
 * paramters
 * onChange > function
 * inline bool default =false
 */
export default class CKEditor extends React.Component {
  el = React.createElement;
  constructor(props) {
    super(props);
    this.elementName = "editor_" + this.props.id;
    this.componentDidMount = this.componentDidMount.bind(this);
    this.state = {
      value: this.props.value,
      reinit: false,
      height: this.props.height,
    };
  }
  componentDidUpdate = (prevProps, prevState) => {
    // if(prevState.prevValue!=prevState.value)
    // {
    // this.setState({prevValue:prevState.value});
    // setTimeout(()=>this. initEditor(),200);
    // }
  };
  render() {
    //console.log(this.state.height);
    if (this.state.reinit) {
      setTimeout(() => this.initEditor(), 200);
    }
    return this.el(
      "div",
      {
        style: {
          display: "inline-table",
          height: this.props.height,
          width: this.props.width || "100%",
        },
      },
      this.el("textarea", {
        id: this.elementName,
        name: this.elementName,
        defaultValue: this.props.value,
      })
    );
  }
  initEditor = () => {
    if (CKEDITOR.instances[this.elementName]) {
      //console.log('destroyed',this.elementName,CKEDITOR.instances);
      CKEDITOR.instances[this.elementName].destroy(true);
    }
    //console.log(this.props.height,this.state.height);
    //inline replace
    // if (this.props.inline)
    //   CKEDITOR.inline(this.elementName, {
    //     customConfig: "../../ckeditor_config_auto.js?v=10",
    //   });
    if (this.props.inline)
      CKEDITOR.inline(this.elementName,this.getCKEditorConfig());
    else
      CKEDITOR.replace(this.elementName, this.getCKEditorConfig());
      
      // CKEDITOR.replace(this.elementName, {
      //   customConfig: "../../ckeditor_config_auto.js?v=10",
      // });
    if (CKEDITOR.instances[this.elementName]) {
      
      // CKEDITOR.instances[this.elementName].config.width =
      //   this.props.width || "auto";
      // CKEDITOR.instances[this.elementName].config.height =
      //   this.props.height || 220;
      // CKEDITOR.instances[this.elementName].config.resize_minHeight =
      //   this.props.height || 220;
      if (this.props.disableClipboard)
        CKEDITOR.instances[this.elementName].on("paste", function (evt) {
          evt.cancel();
        });
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
  getCKEditorConfig = () => {
    const config = {
      contentsCss: "/screen.css",
      toolbarStartupExpanded: false,
      disableNativeSpellChecker: false,
      toolbar: "CNCToolbar",
      toolbar_CNCToolbar: [
        [
          "Source",
          "-",
          "-",
          "Bold",
          "Italic",
          "Underline",
          "Strike",
          "TextColor",
          "BGColor",
        ],
        ["NumberedList", "BulletedList"],
        ["Table"],
        ["Format", "Font", "FontSize"],
        ["Anchor", "Link"],
        ["Undo", "Redo"],
      ],
      extraPlugins: "font,wordcount,scayt",
      fontSize_sizes:
        "8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt",
      wordcount: {
        showParagraphs: false,
        showCharCount: true,
        minCharCount: !this.props.minCharCount?-1 : this.props.minCharCount

      },
      //CKEDITOR.config.width = '1000';
    width:this.props.width || "auto",
    height : this.props.height || 220,
    //resize_minWidth : '760',
    resize_minHeight : this.props.height || 220,
    disableNativeSpellChecker : false,
    removePlugins : 'liststyle,tabletools,language,tableselection',
    scayt_customerId : 'LHXUCjpl0y2gdBb',
    scayt_autoStartup : true,
    grayt_autoStartup : true,
    scayt_sLang :"en_GB",
    disableNativeSpellChecker : true,
    };
    return config;
  }
  componentDidMount() {
    this.initEditor();
    CKEditor.getDerivedStateFromProps = CKEditor.getDerivedStateFromProps.bind(
      this
    );
  }

  static getDerivedStateFromProps(props, current_state) {
    if (current_state && current_state.value !== props.value) {
      //console.log("====================>",current_state.value , props.value);
      this.initEditor();
      return {
        value: props.value,
        reinit: true,
        height: props.height,
      };
    }

    return {
      reinit: false,
    };
  }
}
