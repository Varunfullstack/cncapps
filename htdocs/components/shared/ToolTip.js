class ToolTip extends React.Component {
  el = React.createElement;
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
      const {el}=this;
      const {title,content,width}=this.props;
    return el('div',{style:{width:width}},el(
      "div",
      { className: "tooltip" },
      content,
      el(
        "div",
        { className: "tooltiptext tooltip-bottom" },
        title ? title : "",
      ),
    ),);
  }
}

export default ToolTip;
