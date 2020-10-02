class ToolTip extends React.Component {
  el = React.createElement;
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
      const {el}=this;
      const {title,content}=this.props;
    return el('div',null,el(
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
