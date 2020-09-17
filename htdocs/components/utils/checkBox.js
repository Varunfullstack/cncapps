/*
properties: label, name, checked, reversed
events onChange 
*/
class CheckBox extends React.Component {
  el = React.createElement;  
  render() {
    const { label, name, checked, onChange ,reversed} = this.props;
    const items = [
      this.el("input", {
        type: "checkbox",
        key: name,
        defaultChecked: checked,
      }),
      this.el("label", { key: name + "_label" }, label),
    ];
    return this.el("div", {className:'check-box',onClick:onChange }, 
      reversed?[items[1],items[0]]:[items[0],items[1]]
    );
  }
}
export default CheckBox;
